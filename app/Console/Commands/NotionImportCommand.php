<?php

namespace App\Console\Commands;

use App\Models\Note;
use App\Services\Notion\NotionClient;
use App\Services\Notion\NotionBlockConverter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotionImportCommand extends Command
{
    protected $signature = 'notion:import
                            {page_id : The Notion page ID to import (root page)}
                            {--parent-note= : Optional parent Note ID to attach imported notes to}
                            {--dry-run : Show what would be imported without creating notes}';

    protected $description = 'Import pages from Notion into the Notes system';

    private NotionClient $client;
    private NotionBlockConverter $converter;
    private int $importedCount = 0;
    private int $errorCount = 0;
    private bool $dryRun = false;

    public function handle(): int
    {
        $pageId = $this->argument('page_id');
        $parentNoteId = $this->option('parent-note');
        $this->dryRun = $this->option('dry-run');

        $this->info('Starting Notion import...');

        if ($this->dryRun) {
            $this->warn('DRY RUN MODE - No notes will be created');
        }

        try {
            $this->client = new NotionClient();
            $this->converter = new NotionBlockConverter($this->client);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());
            return Command::FAILURE;
        }

        // Validate parent note if provided
        $parentNote = null;
        if ($parentNoteId) {
            $parentNote = Note::find($parentNoteId);
            if (!$parentNote) {
                $this->error("Parent note with ID {$parentNoteId} not found.");
                return Command::FAILURE;
            }
            $this->info("Importing under parent note: {$parentNote->name}");
        }

        try {
            // Detect if it's a page or database
            $type = $this->client->detectType($pageId);
            $this->info("Detected type: {$type}");

            if ($type === 'database') {
                $this->importDatabase($pageId, $parentNote?->id);
            } else {
                $this->importPage($pageId, $parentNote?->id);
            }

            $this->newLine();
            $this->info("Import completed!");
            $this->info("Pages imported: {$this->importedCount}");

            if ($this->errorCount > 0) {
                $this->warn("Errors encountered: {$this->errorCount}");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Import failed: {$e->getMessage()}");
            Log::error('Notion import failed', [
                'page_id' => $pageId,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Import a Notion database and all its entries.
     */
    private function importDatabase(string $databaseId, ?int $parentNoteId = null): void
    {
        try {
            // Get database metadata
            $database = $this->client->getDatabase($databaseId);
            $title = $this->client->extractPageTitle($database);

            if (empty($title) || $title === 'Untitled') {
                // Try to get title from the title property directly
                foreach ($database['title'] ?? [] as $titlePart) {
                    $title .= $titlePart['plain_text'] ?? '';
                }
            }

            $this->info("Importing database: {$title}");

            // Create parent note for the database
            $databaseNote = null;
            if (!$this->dryRun) {
                $databaseNote = Note::create([
                    'name' => $title ?: 'Imported Database',
                    'parent_id' => $parentNoteId,
                    'datetime' => now(),
                    'notes' => '<p><em>Imported from Notion database</em></p>',
                ]);
                $this->importedCount++;
                $this->info("  -> Created database note ID: {$databaseNote->id}");
            } else {
                $this->info("  -> Would create database root note");
            }

            // Get all entries from the database
            $entries = $this->client->getAllDatabaseEntries($databaseId);
            $this->info("Found " . count($entries) . " entries in database");

            // Import each entry as a child page
            foreach ($entries as $entry) {
                $entryId = $entry['id'];
                $this->importPage($entryId, $databaseNote?->id, 1);
            }

        } catch (\Exception $e) {
            $this->errorCount++;
            $this->error("Error importing database {$databaseId}: {$e->getMessage()}");
            Log::error('Failed to import Notion database', [
                'database_id' => $databaseId,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Import a Notion page and its children recursively.
     */
    private function importPage(string $pageId, ?int $parentNoteId = null, int $depth = 0): ?Note
    {
        $indent = str_repeat('  ', $depth);

        try {
            // Fetch page metadata
            $page = $this->client->getPage($pageId);
            $title = $this->client->extractPageTitle($page);
            $createdTime = $page['created_time'] ?? null;

            $this->line("{$indent}Importing: {$title}");

            // Fetch all blocks from the page
            $blocks = $this->client->getAllBlockChildren($pageId);

            // Separate child pages from content blocks
            $childPages = [];
            $contentBlocks = [];

            foreach ($blocks as $block) {
                if (($block['type'] ?? '') === 'child_page') {
                    $childPages[] = $block;
                } else {
                    $contentBlocks[] = $block;
                }
            }

            // Convert content blocks to HTML
            $htmlContent = $this->converter->convert($contentBlocks);

            // Create the note
            $note = null;

            if (!$this->dryRun) {
                $note = Note::create([
                    'name' => $title,
                    'parent_id' => $parentNoteId,
                    'datetime' => $createdTime ? new \DateTime($createdTime) : now(),
                    'notes' => $htmlContent ?: null,
                ]);

                $this->importedCount++;
                $this->line("{$indent}  -> Created Note ID: {$note->id}");
            } else {
                $this->importedCount++;
                $this->line("{$indent}  -> Would create note with " . strlen($htmlContent) . " chars of content");
                $this->line("{$indent}  -> Found " . count($childPages) . " child page(s)");
            }

            // Recursively import child pages
            foreach ($childPages as $childBlock) {
                $childPageId = $childBlock['id'];
                $this->importPage($childPageId, $note?->id, $depth + 1);
            }

            return $note;
        } catch (\Exception $e) {
            $this->errorCount++;
            $this->error("{$indent}Error importing page {$pageId}: {$e->getMessage()}");
            Log::error('Failed to import Notion page', [
                'page_id' => $pageId,
                'parent_note_id' => $parentNoteId,
                'exception' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
