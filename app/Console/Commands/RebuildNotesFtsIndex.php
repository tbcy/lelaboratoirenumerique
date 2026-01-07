<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RebuildNotesFtsIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notes:rebuild-fts
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild the FTS5 full-text search index for notes';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('This will rebuild the entire notes FTS index. Continue?')) {
            $this->info('Operation cancelled.');

            return Command::SUCCESS;
        }

        $this->info('Rebuilding notes FTS index...');

        try {
            // Check if FTS table exists
            $tableExists = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='notes_fts'");

            if (empty($tableExists)) {
                $this->error('FTS table "notes_fts" does not exist. Run migrations first.');

                return Command::FAILURE;
            }

            // Get count before rebuild
            $noteCount = DB::table('notes')->count();
            $this->info("Found {$noteCount} notes to index.");

            // Rebuild the FTS index
            $startTime = microtime(true);
            DB::statement("INSERT INTO notes_fts(notes_fts) VALUES('rebuild')");
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            // Verify index
            $ftsCount = DB::select('SELECT COUNT(*) as count FROM notes_fts')[0]->count ?? 0;

            $this->info("FTS index rebuilt successfully in {$duration}ms.");
            $this->info("Indexed {$ftsCount} notes.");

            if ($ftsCount !== $noteCount) {
                $this->warn("Warning: FTS count ({$ftsCount}) differs from notes count ({$noteCount}).");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to rebuild FTS index: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
