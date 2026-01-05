<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\CatalogCategory;
use App\Models\CatalogItem;
use App\Models\Client;
use App\Models\Company;
use App\Models\GeneratedImage;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Project;
use App\Models\Quote;
use App\Models\QuoteLine;
use App\Models\SocialConnection;
use App\Models\SocialPost;
use App\Models\Task;
use App\Models\TimeEntry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;

class ImportHelperData extends Command
{
    protected $signature = 'helper:import {--dry-run : Show what would be imported without actually importing}';

    protected $description = 'Import data from Helper SQLite backup into Laboratoire Numérique';

    private PDO $helperDb;
    private bool $dryRun;
    private array $stats = [];

    public function handle(): int
    {
        $backupPath = base_path('helper_backup.sqlite');

        if (!file_exists($backupPath)) {
            $this->error("Helper backup not found at: {$backupPath}");
            $this->info("Please run: scp o2switch_perso:~/production/helper/database/database.sqlite helper_backup.sqlite");
            return 1;
        }

        $this->dryRun = $this->option('dry-run');

        if ($this->dryRun) {
            $this->warn('DRY RUN MODE - No data will be imported');
        }

        $this->helperDb = new PDO("sqlite:{$backupPath}");
        $this->helperDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->info('Starting Helper data import...');
        $this->newLine();

        // Import in order of dependencies
        $this->importTable('companies', Company::class, $this->getCompanyMapping());
        $this->importTable('catalog_categories', CatalogCategory::class, $this->getCatalogCategoryMapping());
        $this->importTable('catalog_items', CatalogItem::class, $this->getCatalogItemMapping());
        $this->importTable('clients', Client::class, $this->getClientMapping());
        $this->importTable('projects', Project::class, $this->getProjectMapping());
        $this->importTable('quotes', Quote::class, $this->getQuoteMapping());
        $this->importTable('quote_lines', QuoteLine::class, $this->getQuoteLineMapping());
        $this->importTable('invoices', Invoice::class, $this->getInvoiceMapping());
        $this->importTable('invoice_lines', InvoiceLine::class, $this->getInvoiceLineMapping());
        $this->importTable('tasks', Task::class, $this->getTaskMapping());
        $this->importTable('time_entries', TimeEntry::class, $this->getTimeEntryMapping());
        $this->importTable('social_connections', SocialConnection::class, $this->getSocialConnectionMapping());
        $this->importTable('social_posts', SocialPost::class, $this->getSocialPostMapping());
        $this->importTable('generated_images', GeneratedImage::class, $this->getGeneratedImageMapping());
        $this->importTable('activity_logs', ActivityLog::class, $this->getActivityLogMapping());
        $this->importTable('audit_logs', AuditLog::class, $this->getAuditLogMapping());

        $this->newLine();
        $this->info('Import Summary:');
        $this->table(['Table', 'Records'], collect($this->stats)->map(fn($count, $table) => [$table, $count])->toArray());

        if ($this->dryRun) {
            $this->warn('DRY RUN completed. Run without --dry-run to actually import.');
        } else {
            $this->info('Import completed successfully!');
        }

        return 0;
    }

    private function importTable(string $table, string $model, array $mapping): void
    {
        $stmt = $this->helperDb->query("SELECT * FROM {$table}");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $count = count($rows);
        $this->stats[$table] = $count;

        if ($count === 0) {
            $this->line("  <fg=yellow>⏭</> {$table}: 0 records (skipped)");
            return;
        }

        $this->line("  <fg=blue>→</> {$table}: {$count} records");

        if ($this->dryRun) {
            return;
        }

        // Disable foreign key checks during import
        DB::statement('PRAGMA foreign_keys = OFF');

        // Fields that are stored as JSON strings in Helper but have array casts in Laravel
        $jsonFields = [
            'images', 'connection_ids', 'credentials', 'metadata', 'old_values', 'new_values',
        ];

        foreach ($rows as $row) {
            $data = [];
            foreach ($mapping as $helperColumn => $laboColumn) {
                if (array_key_exists($helperColumn, $row)) {
                    $value = $row[$helperColumn];

                    // Decode JSON fields to prevent double-encoding
                    if (in_array($laboColumn, $jsonFields) && is_string($value) && $value !== '') {
                        $decoded = json_decode($value, true);
                        $value = $decoded !== null ? $decoded : $value;
                    }

                    $data[$laboColumn] = $value;
                }
            }

            // Preserve the original ID
            if (isset($row['id'])) {
                $data['id'] = $row['id'];
            }

            try {
                // Disable model events and timestamps during import
                $model::withoutEvents(function () use ($model, $data) {
                    $model::withoutTimestamps(function () use ($model, $data) {
                        $model::create($data);
                    });
                });
            } catch (\Exception $e) {
                $this->warn("    Error importing {$table} ID {$row['id']}: " . $e->getMessage());
            }
        }

        // Re-enable foreign key checks
        DB::statement('PRAGMA foreign_keys = ON');
    }

    // Column mappings (Helper column => Labo Num column)

    private function getCompanyMapping(): array
    {
        return [
            'id' => 'id',
            'name' => 'name',
            'legal_form' => 'legal_form',
            'siret' => 'siret',
            'vat_number' => 'vat_number',
            'address' => 'address',
            'address_2' => 'address_2',
            'postal_code' => 'postal_code',
            'city' => 'city',
            'country' => 'country',
            'phone' => 'phone',
            'email' => 'email',
            'website' => 'website',
            'logo' => 'logo',
            'iban' => 'iban',
            'bic' => 'bic',
            'bank_name' => 'bank_name',
            'legal_mentions' => 'legal_mentions',
            'quote_prefix' => 'quote_prefix',
            'quote_counter' => 'quote_counter',
            'invoice_prefix' => 'invoice_prefix',
            'invoice_counter' => 'invoice_counter',
            'default_payment_delay' => 'default_payment_delay',
            'default_vat_rate' => 'default_vat_rate',
            'openai_api_key' => 'openai_api_key',
            'image_generation_prompt' => 'image_generation_prompt',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
            'deleted_at' => 'deleted_at',
        ];
    }

    private function getCatalogCategoryMapping(): array
    {
        return [
            'id' => 'id',
            'name' => 'name',
            'color' => 'color',
            'sort_order' => 'sort_order',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
            'deleted_at' => 'deleted_at',
        ];
    }

    private function getCatalogItemMapping(): array
    {
        return [
            'id' => 'id',
            'catalog_category_id' => 'catalog_category_id',
            'name' => 'name',
            'description' => 'description',
            'unit_price' => 'unit_price',
            'unit' => 'unit',
            'vat_rate' => 'vat_rate',
            'is_active' => 'is_active',
            'sku' => 'sku',
            'minimum_quantity' => 'minimum_quantity',
            'default_quantity' => 'default_quantity',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
            'deleted_at' => 'deleted_at',
        ];
    }

    private function getClientMapping(): array
    {
        return [
            'id' => 'id',
            'type' => 'type',
            'company_name' => 'company_name',
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'email' => 'email',
            'phone' => 'phone',
            'mobile' => 'mobile',
            'address' => 'address',
            'address_2' => 'address_2',
            'postal_code' => 'postal_code',
            'city' => 'city',
            'country' => 'country',
            'vat_number' => 'vat_number',
            'siret' => 'siret',
            'notes' => 'notes',
            'status' => 'status',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
            'deleted_at' => 'deleted_at',
        ];
    }

    private function getProjectMapping(): array
    {
        return [
            'id' => 'id',
            'client_id' => 'client_id',
            'name' => 'name',
            'description' => 'description',
            'status' => 'status',
            'start_date' => 'start_date',
            'end_date' => 'end_date',
            'budget' => 'budget',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
            'deleted_at' => 'deleted_at',
        ];
    }

    private function getQuoteMapping(): array
    {
        return [
            'id' => 'id',
            'client_id' => 'client_id',
            'number' => 'number',
            'subject' => 'subject',
            'status' => 'status',
            'issue_date' => 'issue_date',
            'valid_until' => 'valid_until',
            'total_ht' => 'total_ht',
            'total_vat' => 'total_vat',
            'total_ttc' => 'total_ttc',
            'notes' => 'notes',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
            'deleted_at' => 'deleted_at',
        ];
    }

    private function getQuoteLineMapping(): array
    {
        return [
            'id' => 'id',
            'quote_id' => 'quote_id',
            'catalog_item_id' => 'catalog_item_id',
            'description' => 'description',
            'quantity' => 'quantity',
            'unit_price' => 'unit_price',
            'vat_rate' => 'vat_rate',
            'total_ht' => 'total_ht',
            'total_vat' => 'total_vat',
            'total_ttc' => 'total_ttc',
            'sort_order' => 'sort_order',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        ];
    }

    private function getInvoiceMapping(): array
    {
        return [
            'id' => 'id',
            'client_id' => 'client_id',
            'quote_id' => 'quote_id',
            'project_id' => 'project_id',
            'number' => 'number',
            'subject' => 'subject',
            'status' => 'status',
            'issue_date' => 'issue_date',
            'due_date' => 'due_date',
            'paid_at' => 'paid_at',
            'total_ht' => 'total_ht',
            'total_vat' => 'total_vat',
            'total_ttc' => 'total_ttc',
            'notes' => 'notes',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
            'deleted_at' => 'deleted_at',
        ];
    }

    private function getInvoiceLineMapping(): array
    {
        return [
            'id' => 'id',
            'invoice_id' => 'invoice_id',
            'catalog_item_id' => 'catalog_item_id',
            'description' => 'description',
            'quantity' => 'quantity',
            'unit_price' => 'unit_price',
            'vat_rate' => 'vat_rate',
            'total_ht' => 'total_ht',
            'total_vat' => 'total_vat',
            'total_ttc' => 'total_ttc',
            'sort_order' => 'sort_order',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        ];
    }

    private function getTaskMapping(): array
    {
        return [
            'id' => 'id',
            'project_id' => 'project_id',
            'client_id' => 'client_id',
            'catalog_item_id' => 'catalog_item_id',
            'parent_id' => 'parent_id',
            'title' => 'title',
            'description' => 'description',
            'status' => 'status',
            'priority' => 'priority',
            'due_date' => 'due_date',
            'estimated_minutes' => 'estimated_minutes',
            'sort_order' => 'sort_order',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
            'deleted_at' => 'deleted_at',
        ];
    }

    private function getTimeEntryMapping(): array
    {
        return [
            'id' => 'id',
            'task_id' => 'task_id',
            'user_id' => 'user_id',
            'started_at' => 'started_at',
            'stopped_at' => 'stopped_at',
            'duration_seconds' => 'duration_seconds',
            'notes' => 'notes',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
            'deleted_at' => 'deleted_at',
        ];
    }

    private function getSocialConnectionMapping(): array
    {
        return [
            'id' => 'id',
            'name' => 'name',
            'platform' => 'platform',
            'credentials' => 'credentials',
            'is_active' => 'is_active',
            'last_used_at' => 'last_used_at',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
            'deleted_at' => 'deleted_at',
        ];
    }

    private function getSocialPostMapping(): array
    {
        return [
            'id' => 'id',
            'content' => 'content',
            'images' => 'images',
            'connection_ids' => 'connection_ids',
            'status' => 'status',
            'scheduled_at' => 'scheduled_at',
            'published_at' => 'published_at',
            'error_message' => 'error_message',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
            'deleted_at' => 'deleted_at',
        ];
    }

    private function getGeneratedImageMapping(): array
    {
        return [
            'id' => 'id',
            'social_post_id' => 'social_post_id',
            'prompt' => 'prompt',
            'revised_prompt' => 'revised_prompt',
            'image_url' => 'image_url',
            'image_path' => 'image_path',
            'model' => 'model',
            'size' => 'size',
            'quality' => 'quality',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        ];
    }

    private function getActivityLogMapping(): array
    {
        return [
            'id' => 'id',
            'user_id' => 'user_id',
            'loggable_type' => 'loggable_type',
            'loggable_id' => 'loggable_id',
            'action' => 'action',
            'description' => 'description',
            'metadata' => 'metadata',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        ];
    }

    private function getAuditLogMapping(): array
    {
        return [
            'id' => 'id',
            'entity_type' => 'entity_type',
            'entity_id' => 'entity_id',
            'action' => 'action',
            'old_values' => 'old_values',
            'new_values' => 'new_values',
            'metadata' => 'metadata',
            'created_at' => 'created_at',
        ];
    }
}
