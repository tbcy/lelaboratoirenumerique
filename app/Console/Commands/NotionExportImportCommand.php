<?php

namespace App\Console\Commands;

use App\Models\Note;
use App\Models\NoteScope;
use App\Models\Stakeholder;
use App\Services\Notion\NotionExportParser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NotionExportImportCommand extends Command
{
    protected $signature = 'notion:import-export
                            {path : Path to the Notion export folder}
                            {--dry-run : Show what would be imported without creating notes}
                            {--skip-stakeholders : Do not create stakeholders}
                            {--skip-scopes : Do not create scopes}
                            {--parent-name=Meeting notes - [NEXUS] : Name for the parent note}';

    protected $description = 'Import meeting notes from a Notion export (CSV + Markdown)';

    private NotionExportParser $parser;
    private bool $dryRun = false;
    private bool $skipStakeholders = false;
    private bool $skipScopes = false;

    private int $importedCount = 0;
    private int $skippedCount = 0;
    private int $errorCount = 0;

    public function handle(): int
    {
        $path = $this->argument('path');
        $this->dryRun = $this->option('dry-run');
        $this->skipStakeholders = $this->option('skip-stakeholders');
        $this->skipScopes = $this->option('skip-scopes');
        $parentName = $this->option('parent-name');

        $this->info('Starting Notion export import...');

        if ($this->dryRun) {
            $this->warn('DRY RUN MODE - No data will be created');
        }

        // Validate path
        if (!is_dir($path)) {
            $this->error("Directory not found: {$path}");
            return Command::FAILURE;
        }

        $this->parser = new NotionExportParser();

        // Find CSV file
        $csvFile = $this->findCSVFile($path);

        if (!$csvFile) {
            $this->error("No CSV file found in: {$path}");
            return Command::FAILURE;
        }

        $this->info("Found CSV: " . basename($csvFile));

        // Find Markdown directory
        $mdDirectory = $this->findMarkdownDirectory($path);

        if (!$mdDirectory) {
            $this->error("No Markdown directory found in: {$path}");
            return Command::FAILURE;
        }

        $this->info("Found Markdown directory: " . basename($mdDirectory));

        try {
            // Parse CSV
            $meetings = $this->parser->parseCSV($csvFile);
            $this->info("Found " . count($meetings) . " meetings in CSV");

            // Create parent note
            $parentNote = $this->createParentNote($parentName);

            // Process each meeting
            $this->output->progressStart(count($meetings));

            foreach ($meetings as $meeting) {
                $this->importMeeting($meeting, $mdDirectory, $parentNote?->id);
                $this->output->progressAdvance();
            }

            $this->output->progressFinish();

            // Summary
            $this->newLine();
            $this->info("Import completed!");
            $this->info("  Imported: {$this->importedCount}");
            $this->info("  Skipped: {$this->skippedCount}");

            if ($this->errorCount > 0) {
                $this->warn("  Errors: {$this->errorCount}");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Import failed: " . $e->getMessage());
            Log::error('Notion export import failed', [
                'path' => $path,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Find the CSV file in the export directory.
     */
    private function findCSVFile(string $path): ?string
    {
        $files = glob($path . '/*.csv');

        // Prefer the non-_all.csv file
        foreach ($files as $file) {
            if (!str_contains($file, '_all.csv')) {
                return $file;
            }
        }

        return $files[0] ?? null;
    }

    /**
     * Find the Markdown directory in the export.
     */
    private function findMarkdownDirectory(string $path): ?string
    {
        // Use scandir instead of glob to handle special characters like []
        $items = scandir($path);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $fullPath = $path . '/' . $item;

            if (!is_dir($fullPath)) {
                continue;
            }

            // Check if directory contains .md files
            $subItems = scandir($fullPath);

            foreach ($subItems as $subItem) {
                if (pathinfo($subItem, PATHINFO_EXTENSION) === 'md') {
                    return $fullPath;
                }
            }
        }

        return null;
    }

    /**
     * Create the parent note for all imported meetings.
     */
    private function createParentNote(string $name): ?Note
    {
        if ($this->dryRun) {
            $this->line("  Would create parent note: {$name}");
            return null;
        }

        $note = Note::create([
            'name' => $name,
            'parent_id' => null,
            'datetime' => now(),
            'short_summary' => 'Imported from Notion export',
        ]);

        $this->line("  Created parent note: {$name} (ID: {$note->id})");

        return $note;
    }

    /**
     * Import a single meeting.
     */
    private function importMeeting(array $meeting, string $mdDirectory, ?int $parentNoteId): void
    {
        $name = $meeting['name'];

        if (empty($name)) {
            $this->skippedCount++;
            return;
        }

        try {
            // Find corresponding Markdown file
            $mdFile = $this->parser->findMarkdownFile($mdDirectory, $name);
            $mdData = [];

            if ($mdFile) {
                $mdData = $this->parser->parseMarkdown($mdFile);
            }

            if ($this->dryRun) {
                $this->line("  Would import: {$name}");
                $this->line("    - Date: " . ($meeting['date']?->format('Y-m-d') ?? 'N/A'));
                $this->line("    - Summary: " . Str::limit($meeting['meeting_summary'], 50));
                $this->line("    - Résumé: " . strlen($mdData['resume'] ?? '') . " chars");
                $this->line("    - Notes: " . strlen($mdData['notes'] ?? '') . " chars");
                $this->line("    - Transcription: " . strlen($mdData['transcription'] ?? '') . " chars");
                $this->line("    - Participants: " . count($meeting['participants']));
                $this->line("    - Scope: " . ($meeting['scope'] ?: 'N/A'));
                $this->importedCount++;
                return;
            }

            // Create the note
            $note = Note::create([
                'name' => $name,
                'parent_id' => $parentNoteId,
                'datetime' => $meeting['date'],
                'short_summary' => $meeting['meeting_summary'] ?: null,
                'long_summary' => $mdData['resume'] ?? null,
                'notes' => $mdData['notes'] ?? null,
                'transcription' => $mdData['transcription'] ?? null,
            ]);

            // Attach stakeholders
            if (!$this->skipStakeholders && !empty($meeting['participants'])) {
                $this->attachStakeholders($note, $meeting['participants']);
            }

            // Attach scope
            if (!$this->skipScopes && !empty($meeting['scope'])) {
                $this->attachScope($note, $meeting['scope']);
            }

            $this->importedCount++;
        } catch (\Exception $e) {
            $this->errorCount++;
            $this->error("  Error importing '{$name}': " . $e->getMessage());
            Log::error('Failed to import meeting', [
                'name' => $name,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Attach stakeholders to a note.
     */
    private function attachStakeholders(Note $note, array $participantNames): void
    {
        foreach ($participantNames as $name) {
            $name = trim($name);

            if (empty($name) || $name === '...') {
                continue;
            }

            $stakeholder = Stakeholder::firstOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );

            $note->stakeholders()->syncWithoutDetaching([$stakeholder->id]);
        }
    }

    /**
     * Attach a scope to a note.
     */
    private function attachScope(Note $note, string $scopeName): void
    {
        $scopeName = trim($scopeName);

        if (empty($scopeName)) {
            return;
        }

        $scope = NoteScope::firstOrCreate(
            ['slug' => Str::slug($scopeName)],
            [
                'name' => $scopeName,
                'color' => $this->generateScopeColor($scopeName),
            ]
        );

        $note->scopes()->syncWithoutDetaching([$scope->id]);
    }

    /**
     * Generate a consistent color for a scope based on its name.
     */
    private function generateScopeColor(string $name): string
    {
        $colors = [
            '#6366f1', // Indigo
            '#8b5cf6', // Violet
            '#ec4899', // Pink
            '#f43f5e', // Rose
            '#f97316', // Orange
            '#eab308', // Yellow
            '#22c55e', // Green
            '#14b8a6', // Teal
            '#06b6d4', // Cyan
            '#3b82f6', // Blue
        ];

        $hash = crc32($name);
        $index = abs($hash) % count($colors);

        return $colors[$index];
    }
}
