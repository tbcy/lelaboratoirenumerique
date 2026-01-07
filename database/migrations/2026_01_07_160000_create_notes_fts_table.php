<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Create FTS5 virtual table for full-text search on notes.
     * This provides 10-100x faster search compared to LIKE queries.
     */
    public function up(): void
    {
        // Create FTS5 virtual table with external content
        // content='notes' means it references the notes table
        // content_rowid='id' links FTS rowid to notes.id
        DB::statement("
            CREATE VIRTUAL TABLE IF NOT EXISTS notes_fts USING fts5(
                name,
                short_summary,
                long_summary,
                notes,
                transcription,
                content='notes',
                content_rowid='id'
            );
        ");

        // Trigger to sync FTS index on INSERT
        DB::statement("
            CREATE TRIGGER IF NOT EXISTS notes_fts_insert AFTER INSERT ON notes BEGIN
                INSERT INTO notes_fts(rowid, name, short_summary, long_summary, notes, transcription)
                VALUES (NEW.id, NEW.name, NEW.short_summary, NEW.long_summary, NEW.notes, NEW.transcription);
            END;
        ");

        // Trigger to sync FTS index on UPDATE
        // FTS5 requires delete then insert for updates
        DB::statement("
            CREATE TRIGGER IF NOT EXISTS notes_fts_update AFTER UPDATE ON notes BEGIN
                INSERT INTO notes_fts(notes_fts, rowid, name, short_summary, long_summary, notes, transcription)
                VALUES ('delete', OLD.id, OLD.name, OLD.short_summary, OLD.long_summary, OLD.notes, OLD.transcription);
                INSERT INTO notes_fts(rowid, name, short_summary, long_summary, notes, transcription)
                VALUES (NEW.id, NEW.name, NEW.short_summary, NEW.long_summary, NEW.notes, NEW.transcription);
            END;
        ");

        // Trigger to sync FTS index on DELETE
        DB::statement("
            CREATE TRIGGER IF NOT EXISTS notes_fts_delete AFTER DELETE ON notes BEGIN
                INSERT INTO notes_fts(notes_fts, rowid, name, short_summary, long_summary, notes, transcription)
                VALUES ('delete', OLD.id, OLD.name, OLD.short_summary, OLD.long_summary, OLD.notes, OLD.transcription);
            END;
        ");

        // Rebuild index from existing data
        DB::statement("INSERT INTO notes_fts(notes_fts) VALUES('rebuild');");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop triggers first
        DB::statement("DROP TRIGGER IF EXISTS notes_fts_insert;");
        DB::statement("DROP TRIGGER IF EXISTS notes_fts_update;");
        DB::statement("DROP TRIGGER IF EXISTS notes_fts_delete;");

        // Drop virtual table
        DB::statement("DROP TABLE IF EXISTS notes_fts;");
    }
};
