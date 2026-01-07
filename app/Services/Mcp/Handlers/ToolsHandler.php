<?php

namespace App\Services\Mcp\Handlers;

use App\Services\Mcp\ErrorHandlingService;
use App\Services\Mcp\Resources\CatalogCategoryResource;
use App\Services\Mcp\Resources\CatalogItemResource;
use App\Services\Mcp\Resources\CategoryResource;
use App\Services\Mcp\Resources\ClientResource;
use App\Services\Mcp\Resources\DashboardResource;
use App\Services\Mcp\Resources\InvoiceResource;
use App\Services\Mcp\Resources\MediaResource;
use App\Services\Mcp\Resources\PostResource;
use App\Services\Mcp\Resources\ProjectResource;
use App\Services\Mcp\Resources\QuoteResource;
use App\Services\Mcp\Resources\SocialConnectionResource;
use App\Services\Mcp\Resources\SocialPostResource;
use App\Services\Mcp\Resources\NoteResource;
use App\Services\Mcp\Resources\NoteScopeResource;
use App\Services\Mcp\Resources\StakeholderResource;
use App\Services\Mcp\Resources\TagResource;
use App\Services\Mcp\Resources\TaskResource;
use App\Services\Mcp\Resources\TimeEntryResource;
use App\Services\Mcp\Tools\CatalogCategoryTools;
use App\Services\Mcp\Tools\CatalogItemTools;
use App\Services\Mcp\Tools\CategoryTools;
use App\Services\Mcp\Tools\ClientTools;
use App\Services\Mcp\Tools\ImageGenerationTools;
use App\Services\Mcp\Tools\InvoiceTools;
use App\Services\Mcp\Tools\MediaTools;
use App\Services\Mcp\Tools\PostTools;
use App\Services\Mcp\Tools\ProjectTools;
use App\Services\Mcp\Tools\QuoteTools;
use App\Services\Mcp\Tools\SocialConnectionTools;
use App\Services\Mcp\Tools\SocialPostTools;
use App\Services\Mcp\Tools\NoteTools;
use App\Services\Mcp\Tools\NoteScopeTools;
use App\Services\Mcp\Tools\StakeholderTools;
use App\Services\Mcp\Tools\TagTools;
use App\Services\Mcp\Tools\TaskTools;
use App\Services\Mcp\Tools\TimeEntryTools;
use App\Services\Mcp\ValidationService;

/**
 * Handles MCP tools/list and tools/call requests
 */
class ToolsHandler
{
    private array $toolExecutors;
    private array $toolDefinitions = [];

    public function __construct(
        private ValidationService $validator,
        private ErrorHandlingService $errorHandler
    ) {
        $this->toolExecutors = [
            // Dashboard Tools
            'get_dashboard' => [DashboardResource::class, 'getDashboard'],

            // Client Tools
            'list_clients' => [ClientResource::class, 'list'],
            'get_client' => [ClientResource::class, 'get'],
            'create_client' => [ClientTools::class, 'create'],
            'update_client' => [ClientTools::class, 'update'],
            'delete_client' => [ClientTools::class, 'delete'],
            'archive_client' => [ClientTools::class, 'archive'],

            // Invoice Tools
            'list_invoices' => [InvoiceResource::class, 'list'],
            'get_invoice' => [InvoiceResource::class, 'get'],
            'create_invoice' => [InvoiceTools::class, 'create'],
            'update_invoice' => [InvoiceTools::class, 'update'],
            'delete_invoice' => [InvoiceTools::class, 'delete'],
            'mark_invoice_paid' => [InvoiceTools::class, 'markPaid'],
            'send_invoice' => [InvoiceTools::class, 'send'],

            // Quote Tools
            'list_quotes' => [QuoteResource::class, 'list'],
            'get_quote' => [QuoteResource::class, 'get'],
            'create_quote' => [QuoteTools::class, 'create'],
            'update_quote' => [QuoteTools::class, 'update'],
            'delete_quote' => [QuoteTools::class, 'delete'],
            'convert_quote_to_invoice' => [QuoteTools::class, 'convertToInvoice'],
            'send_quote' => [QuoteTools::class, 'send'],

            // Task Tools
            'list_tasks' => [TaskResource::class, 'list'],
            'get_task' => [TaskResource::class, 'get'],
            'create_task' => [TaskTools::class, 'create'],
            'update_task' => [TaskTools::class, 'update'],
            'delete_task' => [TaskTools::class, 'delete'],
            'update_task_status' => [TaskTools::class, 'updateStatus'],
            'assign_task' => [TaskTools::class, 'assign'],

            // Time Entry Tools
            'log_time' => [TimeEntryTools::class, 'create'],
            'get_time_entries' => [TimeEntryResource::class, 'list'],

            // Project Tools
            'list_projects' => [ProjectResource::class, 'list'],
            'get_project' => [ProjectResource::class, 'get'],
            'create_project' => [ProjectTools::class, 'create'],
            'update_project' => [ProjectTools::class, 'update'],
            'delete_project' => [ProjectTools::class, 'delete'],
            'archive_project' => [ProjectTools::class, 'archive'],

            // Social Post Tools
            'list_social_posts' => [SocialPostResource::class, 'list'],
            'get_social_post' => [SocialPostResource::class, 'get'],
            'create_social_post' => [SocialPostTools::class, 'create'],
            'approve_social_post' => [SocialPostTools::class, 'approve'],
            'publish_social_post' => [SocialPostTools::class, 'publish'],
            'delete_social_post' => [SocialPostTools::class, 'delete'],

            // Social Connection Tools
            'list_social_connections' => [SocialConnectionResource::class, 'list'],
            'get_social_connection' => [SocialConnectionResource::class, 'get'],
            'create_social_connection' => [SocialConnectionTools::class, 'create'],
            'update_social_connection' => [SocialConnectionTools::class, 'update'],
            'delete_social_connection' => [SocialConnectionTools::class, 'delete'],

            // Catalog Category Tools
            'list_catalog_categories' => [CatalogCategoryResource::class, 'list'],
            'get_catalog_category' => [CatalogCategoryResource::class, 'get'],
            'create_catalog_category' => [CatalogCategoryTools::class, 'create'],
            'update_catalog_category' => [CatalogCategoryTools::class, 'update'],
            'delete_catalog_category' => [CatalogCategoryTools::class, 'delete'],

            // Catalog Item Tools
            'list_catalog_items' => [CatalogItemResource::class, 'list'],
            'get_catalog_item' => [CatalogItemResource::class, 'get'],
            'create_catalog_item' => [CatalogItemTools::class, 'create'],
            'update_catalog_item' => [CatalogItemTools::class, 'update'],
            'delete_catalog_item' => [CatalogItemTools::class, 'delete'],

            // Media Tools
            'list_media' => [MediaResource::class, 'list'],
            'get_media' => [MediaResource::class, 'get'],
            'upload_media_from_url' => [MediaTools::class, 'uploadFromUrl'],
            'delete_media' => [MediaTools::class, 'delete'],

            // Image Generation Tools
            'generate_image' => [ImageGenerationTools::class, 'generate'],
            'generate_image_for_post' => [ImageGenerationTools::class, 'generateForPost'],
            'list_generated_images' => [ImageGenerationTools::class, 'list'],
            'delete_generated_image' => [ImageGenerationTools::class, 'delete'],

            // Blog Post Tools
            'list_posts' => [PostResource::class, 'list'],
            'get_post' => [PostResource::class, 'get'],
            'create_post' => [PostTools::class, 'create'],
            'update_post' => [PostTools::class, 'update'],
            'delete_post' => [PostTools::class, 'delete'],
            'publish_post' => [PostTools::class, 'publish'],
            'unpublish_post' => [PostTools::class, 'unpublish'],

            // Blog Category Tools
            'list_categories' => [CategoryResource::class, 'list'],
            'get_category' => [CategoryResource::class, 'get'],
            'create_category' => [CategoryTools::class, 'create'],
            'update_category' => [CategoryTools::class, 'update'],
            'delete_category' => [CategoryTools::class, 'delete'],

            // Blog Tag Tools
            'list_tags' => [TagResource::class, 'list'],
            'get_tag' => [TagResource::class, 'get'],
            'create_tag' => [TagTools::class, 'create'],
            'update_tag' => [TagTools::class, 'update'],
            'delete_tag' => [TagTools::class, 'delete'],

            // Stakeholder Tools
            'list_stakeholders' => [StakeholderResource::class, 'list'],
            'get_stakeholder' => [StakeholderResource::class, 'get'],
            'create_stakeholder' => [StakeholderTools::class, 'create'],
            'update_stakeholder' => [StakeholderTools::class, 'update'],
            'delete_stakeholder' => [StakeholderTools::class, 'delete'],

            // Note Scope Tools
            'list_note_scopes' => [NoteScopeResource::class, 'list'],
            'get_note_scope' => [NoteScopeResource::class, 'get'],
            'create_note_scope' => [NoteScopeTools::class, 'create'],
            'update_note_scope' => [NoteScopeTools::class, 'update'],
            'delete_note_scope' => [NoteScopeTools::class, 'delete'],

            // Note Tools
            'list_notes' => [NoteResource::class, 'list'],
            'get_note' => [NoteResource::class, 'get'],
            'get_note_children' => [NoteResource::class, 'getChildren'],
            'get_note_tree' => [NoteResource::class, 'getTree'],
            'search_notes' => [NoteResource::class, 'search'],
            'create_note' => [NoteTools::class, 'create'],
            'update_note' => [NoteTools::class, 'update'],
            'delete_note' => [NoteTools::class, 'delete'],
            'set_note_parent' => [NoteTools::class, 'setParent'],
        ];

        $this->buildToolDefinitions();
    }

    /**
     * List all available tools
     */
    public function list(array $params = []): array
    {
        return [
            'tools' => $this->toolDefinitions,
        ];
    }

    /**
     * Call a specific tool
     */
    public function call(array $params): array
    {
        // Validate required parameters
        $this->errorHandler->validateRequiredParams($params, ['name'], 'tools/call');

        $toolName = $params['name'];
        $arguments = $params['arguments'] ?? [];

        // Convert parameter aliases before validation
        $arguments = $this->normalizeAliases($toolName, $arguments);

        // Automatic validation using ValidationService
        $validation = $this->validator->validateToolArgs($toolName, $arguments);

        if (!$validation['valid']) {
            return [
                'content' => [
                    [
                        'type' => 'text',
                        'text' => json_encode([
                            'success' => false,
                            'error_code' => 'VALIDATION_ERROR',
                            'message' => 'Validation failed',
                            'errors' => $validation['errors'],
                        ], JSON_PRETTY_PRINT),
                    ],
                ],
            ];
        }

        // Use validated data
        $arguments = $validation['data'];

        if (!isset($this->toolExecutors[$toolName])) {
            throw new \InvalidArgumentException("Unknown tool: {$toolName}");
        }

        [$class, $method] = $this->toolExecutors[$toolName];
        $instance = app($class);

        if (!method_exists($instance, $method)) {
            throw new \InvalidArgumentException("Tool method not found: {$class}::{$method}");
        }

        // Use reflection to inspect method signature and pass parameters correctly
        $reflection = new \ReflectionMethod($instance, $method);
        $parameters = $reflection->getParameters();

        // Determine how to pass arguments based on method signature
        $methodArgs = $this->prepareMethodArguments($parameters, $arguments);

        // Wrap tool execution in error handling
        $result = $this->errorHandler->wrapToolOperation(
            fn () => $instance->$method(...$methodArgs),
            $toolName
        );

        return [
            'content' => [
                [
                    'type' => 'text',
                    'text' => json_encode($result, JSON_PRETTY_PRINT),
                ],
            ],
        ];
    }

    /**
     * Prepare method arguments based on method signature
     *
     * @param array $parameters ReflectionParameter[]
     * @param array $arguments MCP tool arguments
     * @return array Arguments to pass to the method
     */
    private function prepareMethodArguments(array $parameters, array $arguments): array
    {
        // If method has no parameters, return empty array
        if (empty($parameters)) {
            return [];
        }

        // If method expects a single array parameter (like create(array $args))
        // pass the entire arguments array
        if (count($parameters) === 1) {
            $param = $parameters[0];
            $paramType = $param->getType();

            // If parameter is typed as 'array' or has no type, pass arguments as-is
            if (!$paramType || $paramType->getName() === 'array') {
                return [$arguments];
            }
        }

        // Otherwise, extract individual parameters from arguments array
        $methodArgs = [];
        foreach ($parameters as $param) {
            $paramName = $param->getName();

            // Extract value from arguments or use default if available
            if (array_key_exists($paramName, $arguments)) {
                $methodArgs[] = $arguments[$paramName];
            } elseif ($param->isDefaultValueAvailable()) {
                $methodArgs[] = $param->getDefaultValue();
            } elseif ($param->allowsNull()) {
                $methodArgs[] = null;
            } else {
                // Required parameter missing
                throw new \InvalidArgumentException(
                    "Missing required parameter: {$paramName}"
                );
            }
        }

        return $methodArgs;
    }

    /**
     * Build tool definitions for tools/list response
     */
    private function buildToolDefinitions(): void
    {
        // Dashboard
        $this->toolDefinitions[] = [
            'name' => 'get_dashboard',
            'description' => 'Get dashboard statistics including unpaid invoices, overdue tasks, etc.',
            'inputSchema' => [
                'type' => 'object',
                'properties' => (object) [],
            ],
        ];

        // Clients
        $this->toolDefinitions[] = [
            'name' => 'list_clients',
            'description' => 'List all clients with optional filters (status, type, search)',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'status' => ['type' => 'string', 'description' => 'Filter by status: prospect, active, inactive'],
                    'type' => ['type' => 'string', 'description' => 'Filter by type: company, individual'],
                    'search' => ['type' => 'string', 'description' => 'Search in name, email'],
                ],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'get_client',
            'description' => 'Get a single client with related data (quotes, invoices, projects)',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Client ID'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'create_client',
            'description' => 'Create a new client',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'type' => ['type' => 'string', 'description' => 'company or individual'],
                    'company_name' => ['type' => 'string'],
                    'first_name' => ['type' => 'string'],
                    'last_name' => ['type' => 'string'],
                    'email' => ['type' => 'string'],
                    'phone' => ['type' => 'string'],
                    'mobile' => ['type' => 'string'],
                    'address' => ['type' => 'string'],
                    'postal_code' => ['type' => 'string'],
                    'city' => ['type' => 'string'],
                    'country' => ['type' => 'string'],
                    'status' => ['type' => 'string', 'description' => 'Status: prospect, active, inactive'],
                ],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'update_client',
            'description' => 'Update an existing client',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Client ID'],
                    'type' => ['type' => 'string', 'description' => 'company or individual'],
                    'company_name' => ['type' => 'string'],
                    'first_name' => ['type' => 'string'],
                    'last_name' => ['type' => 'string'],
                    'email' => ['type' => 'string'],
                    'phone' => ['type' => 'string'],
                    'mobile' => ['type' => 'string'],
                    'address' => ['type' => 'string'],
                    'postal_code' => ['type' => 'string'],
                    'city' => ['type' => 'string'],
                    'country' => ['type' => 'string'],
                    'status' => ['type' => 'string', 'description' => 'Status: prospect, active, inactive'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'delete_client',
            'description' => 'Delete a client',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Client ID'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'archive_client',
            'description' => 'Archive a client (set status to inactive)',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Client ID'],
                ],
                'required' => ['id'],
            ],
        ];

        // Invoices
        $this->toolDefinitions[] = [
            'name' => 'list_invoices',
            'description' => 'List all invoices with optional filters',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'status' => ['type' => 'string', 'description' => 'Filter by status: draft, sent, paid, overdue, cancelled'],
                    'client_id' => ['type' => 'integer', 'description' => 'Filter by client ID'],
                    'unpaid' => ['type' => 'boolean', 'description' => 'Filter unpaid invoices only'],
                ],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'get_invoice',
            'description' => 'Get a single invoice with lines',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Invoice ID'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'create_invoice',
            'description' => 'Create a new invoice',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'client_id' => ['type' => 'integer', 'description' => 'Client ID (required)'],
                    'number' => ['type' => 'string', 'description' => 'Invoice number (optional, auto-generated if not provided, must be unique). Alias: invoice_number'],
                    'subject' => ['type' => 'string', 'description' => 'Invoice subject/title (required)'],
                    'project_id' => ['type' => 'integer', 'description' => 'Project ID (optional, must belong to client)'],
                    'quote_id' => ['type' => 'integer', 'description' => 'Quote ID (optional, must belong to client)'],
                    'status' => ['type' => 'string', 'description' => 'Status: draft, sent, paid, overdue, cancelled (default: draft)'],
                    'issue_date' => ['type' => 'string', 'description' => 'Issue date (YYYY-MM-DD, required)'],
                    'due_date' => ['type' => 'string', 'description' => 'Due date (YYYY-MM-DD, required)'],
                    'lines' => ['type' => 'array', 'description' => 'Invoice lines with description, quantity, unit_price, vat_rate'],
                ],
                'required' => ['client_id', 'subject', 'issue_date', 'due_date'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'update_invoice',
            'description' => 'Update an existing invoice (invoice number cannot be changed after creation)',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Invoice ID (required)'],
                    'subject' => ['type' => 'string', 'description' => 'Invoice subject/title'],
                    'project_id' => ['type' => 'integer', 'description' => 'Project ID (must belong to invoice client)'],
                    'quote_id' => ['type' => 'integer', 'description' => 'Quote ID (must belong to invoice client)'],
                    'status' => ['type' => 'string', 'description' => 'Status: draft, sent, paid, overdue, cancelled'],
                    'issue_date' => ['type' => 'string', 'description' => 'Issue date (YYYY-MM-DD)'],
                    'due_date' => ['type' => 'string', 'description' => 'Due date (YYYY-MM-DD)'],
                    'lines' => ['type' => 'array', 'description' => 'Invoice lines'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'delete_invoice',
            'description' => 'Delete an invoice',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Invoice ID'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'mark_invoice_paid',
            'description' => 'Mark an invoice as paid',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Invoice ID'],
                    'payment_date' => ['type' => 'string', 'description' => 'Payment date (YYYY-MM-DD)'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'send_invoice',
            'description' => 'Send an invoice by email',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Invoice ID'],
                ],
                'required' => ['id'],
            ],
        ];

        // Quotes
        $this->toolDefinitions[] = [
            'name' => 'list_quotes',
            'description' => 'List all quotes with optional filters',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'status' => ['type' => 'string', 'description' => 'Filter by status: draft, sent, accepted, rejected'],
                    'client_id' => ['type' => 'integer', 'description' => 'Filter by client ID'],
                ],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'get_quote',
            'description' => 'Get a single quote with lines',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Quote ID'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'create_quote',
            'description' => 'Create a new quote',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'client_id' => ['type' => 'integer', 'description' => 'Client ID (required)'],
                    'number' => ['type' => 'string', 'description' => 'Quote number (optional, auto-generated if not provided, must be unique). Alias: quote_number'],
                    'subject' => ['type' => 'string', 'description' => 'Quote subject/title (required)'],
                    'issue_date' => ['type' => 'string', 'description' => 'Issue date (YYYY-MM-DD, required)'],
                    'valid_until' => ['type' => 'string', 'description' => 'Valid until date (YYYY-MM-DD, required)'],
                    'status' => ['type' => 'string', 'description' => 'Status: draft, sent, accepted, rejected, expired (default: draft)'],
                    'lines' => ['type' => 'array', 'description' => 'Quote lines with description, quantity, unit_price, vat_rate'],
                ],
                'required' => ['client_id', 'subject', 'issue_date', 'valid_until'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'update_quote',
            'description' => 'Update an existing quote (quote number cannot be changed after creation)',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Quote ID (required)'],
                    'subject' => ['type' => 'string', 'description' => 'Quote subject/title'],
                    'issue_date' => ['type' => 'string', 'description' => 'Issue date (YYYY-MM-DD)'],
                    'valid_until' => ['type' => 'string', 'description' => 'Valid until date (YYYY-MM-DD)'],
                    'status' => ['type' => 'string', 'description' => 'Status: draft, sent, accepted, rejected, expired'],
                    'lines' => ['type' => 'array', 'description' => 'Quote lines'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'delete_quote',
            'description' => 'Delete a quote',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Quote ID'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'convert_quote_to_invoice',
            'description' => 'Convert an accepted quote to an invoice',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Quote ID'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'send_quote',
            'description' => 'Send a quote by email',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Quote ID'],
                ],
                'required' => ['id'],
            ],
        ];

        // Tasks
        $this->toolDefinitions[] = [
            'name' => 'list_tasks',
            'description' => 'List all tasks with optional filters',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'status' => ['type' => 'string', 'description' => 'Filter by status: todo, in_progress, done'],
                    'priority' => ['type' => 'string', 'description' => 'Filter by priority: low, medium, high'],
                    'project_id' => ['type' => 'integer', 'description' => 'Filter by project ID'],
                    'overdue' => ['type' => 'boolean', 'description' => 'Filter overdue tasks only'],
                ],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'get_task',
            'description' => 'Get a single task with subtasks',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Task ID'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'create_task',
            'description' => 'Create a new task',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'title' => ['type' => 'string', 'description' => 'Task title'],
                    'description' => ['type' => 'string', 'description' => 'Task description'],
                    'status' => ['type' => 'string', 'description' => 'Status: todo, in_progress, done'],
                    'priority' => ['type' => 'string', 'description' => 'Priority: low, medium, high'],
                    'due_date' => ['type' => 'string', 'description' => 'Due date (YYYY-MM-DD)'],
                    'project_id' => ['type' => 'integer', 'description' => 'Project ID'],
                    'client_id' => ['type' => 'integer', 'description' => 'Client ID'],
                    'parent_id' => ['type' => 'integer', 'description' => 'Parent task ID for subtasks'],
                ],
                'required' => ['title'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'update_task',
            'description' => 'Update an existing task',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Task ID'],
                    'title' => ['type' => 'string', 'description' => 'Task title'],
                    'description' => ['type' => 'string', 'description' => 'Task description'],
                    'status' => ['type' => 'string', 'description' => 'Status: todo, in_progress, done'],
                    'priority' => ['type' => 'string', 'description' => 'Priority: low, medium, high'],
                    'due_date' => ['type' => 'string', 'description' => 'Due date (YYYY-MM-DD)'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'delete_task',
            'description' => 'Delete a task',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Task ID'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'update_task_status',
            'description' => 'Update task status (for Kanban)',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Task ID'],
                    'status' => ['type' => 'string', 'description' => 'New status: todo, in_progress, done'],
                ],
                'required' => ['id', 'status'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'assign_task',
            'description' => 'Assign a task to a project',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Task ID'],
                    'project_id' => ['type' => 'integer', 'description' => 'Project ID'],
                ],
                'required' => ['id', 'project_id'],
            ],
        ];

        // Time Entries
        $this->toolDefinitions[] = [
            'name' => 'log_time',
            'description' => 'Log time spent on a task',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'task_id' => ['type' => 'integer', 'description' => 'Task ID'],
                    'duration' => ['type' => 'integer', 'description' => 'Duration in minutes'],
                    'description' => ['type' => 'string', 'description' => 'Description of work done'],
                    'date' => ['type' => 'string', 'description' => 'Date (YYYY-MM-DD)'],
                ],
                'required' => ['task_id', 'duration'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'get_time_entries',
            'description' => 'Get time entries for a task',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'task_id' => ['type' => 'integer', 'description' => 'Task ID'],
                ],
                'required' => ['task_id'],
            ],
        ];

        // Projects
        $this->toolDefinitions[] = [
            'name' => 'list_projects',
            'description' => 'List all projects with optional filters',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'status' => ['type' => 'string', 'description' => 'Filter by status: active, completed, archived, cancelled'],
                    'client_id' => ['type' => 'integer', 'description' => 'Filter by client ID'],
                ],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'get_project',
            'description' => 'Get a single project with tasks, quotes, invoices',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Project ID'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'create_project',
            'description' => 'Create a new project',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string', 'description' => 'Project name'],
                    'description' => ['type' => 'string', 'description' => 'Project description'],
                    'client_id' => ['type' => 'integer', 'description' => 'Client ID'],
                    'status' => ['type' => 'string', 'description' => 'Status: active, completed, archived, cancelled'],
                    'start_date' => ['type' => 'string', 'description' => 'Start date (YYYY-MM-DD)'],
                    'end_date' => ['type' => 'string', 'description' => 'End date (YYYY-MM-DD)'],
                ],
                'required' => ['name'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'update_project',
            'description' => 'Update an existing project',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Project ID'],
                    'name' => ['type' => 'string', 'description' => 'Project name'],
                    'description' => ['type' => 'string', 'description' => 'Project description'],
                    'status' => ['type' => 'string', 'description' => 'Status: active, completed, archived, cancelled'],
                    'start_date' => ['type' => 'string', 'description' => 'Start date (YYYY-MM-DD)'],
                    'end_date' => ['type' => 'string', 'description' => 'End date (YYYY-MM-DD)'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'delete_project',
            'description' => 'Delete a project',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Project ID'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'archive_project',
            'description' => 'Archive a project',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Project ID'],
                ],
                'required' => ['id'],
            ],
        ];

        // Social Posts
        $this->toolDefinitions[] = [
            'name' => 'list_social_posts',
            'description' => 'List all social media posts',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'status' => ['type' => 'string', 'description' => 'Filter by status: draft, approved, scheduled, published, rejected'],
                ],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'get_social_post',
            'description' => 'Get a single social media post',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Post ID'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'create_social_post',
            'description' => 'Create a new social media post. Use connection_ids to specify which social accounts to post to. Use media_ids to attach images from the media library (uploaded via upload_media_from_url).',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'content' => ['type' => 'string', 'description' => 'Post content (max 5000 characters)'],
                    'connection_ids' => ['type' => 'array', 'items' => ['type' => 'integer'], 'description' => 'Array of social connection IDs to post to (use list_social_connections to get available IDs)'],
                    'status' => ['type' => 'string', 'enum' => ['draft', 'scheduled', 'approved'], 'description' => 'Post status: draft (default), scheduled, or approved'],
                    'media_ids' => ['type' => 'array', 'items' => ['type' => 'integer'], 'description' => 'Array of media IDs from the media library to attach as images'],
                    'scheduled_at' => ['type' => 'string', 'description' => 'Scheduled publication date (YYYY-MM-DD HH:MM:SS) - required if status is "scheduled"'],
                ],
                'required' => ['content', 'connection_ids'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'approve_social_post',
            'description' => 'Approve a social media post for publication',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Post ID'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'publish_social_post',
            'description' => 'Publish a social media post immediately',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Post ID'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'delete_social_post',
            'description' => 'Delete a social media post',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Post ID'],
                ],
                'required' => ['id'],
            ],
        ];

        // Social Connections
        $this->toolDefinitions[] = [
            'name' => 'list_social_connections',
            'description' => 'List all social media connections',
            'inputSchema' => [
                'type' => 'object',
                'properties' => (object) [],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'get_social_connection',
            'description' => 'Get a single social media connection',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Connection ID'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'create_social_connection',
            'description' => 'Create a new social media connection',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'platform' => ['type' => 'string', 'description' => 'Platform: twitter, linkedin, instagram, facebook'],
                    'name' => ['type' => 'string', 'description' => 'Connection name'],
                ],
                'required' => ['platform', 'name'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'update_social_connection',
            'description' => 'Update a social media connection',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Connection ID'],
                    'name' => ['type' => 'string', 'description' => 'Connection name'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'delete_social_connection',
            'description' => 'Delete a social media connection',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Connection ID'],
                ],
                'required' => ['id'],
            ],
        ];

        // Catalog Categories
        $this->toolDefinitions[] = [
            'name' => 'list_catalog_categories',
            'description' => 'List all catalog categories',
            'inputSchema' => [
                'type' => 'object',
                'properties' => (object) [],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'get_catalog_category',
            'description' => 'Get a single catalog category with items',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Category ID'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'create_catalog_category',
            'description' => 'Create a new catalog category',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string', 'description' => 'Category name'],
                    'color' => ['type' => 'string', 'description' => 'Category color (hex format, e.g. #FF5733)'],
                ],
                'required' => ['name'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'update_catalog_category',
            'description' => 'Update a catalog category',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Category ID'],
                    'name' => ['type' => 'string', 'description' => 'Category name'],
                    'color' => ['type' => 'string', 'description' => 'Category color (hex format, e.g. #FF5733)'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'delete_catalog_category',
            'description' => 'Delete a catalog category',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Category ID'],
                ],
                'required' => ['id'],
            ],
        ];

        // Catalog Items
        $this->toolDefinitions[] = [
            'name' => 'list_catalog_items',
            'description' => 'List all catalog items',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'category_id' => ['type' => 'integer', 'description' => 'Filter by category ID'],
                ],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'get_catalog_item',
            'description' => 'Get a single catalog item',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Item ID'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'create_catalog_item',
            'description' => 'Create a new catalog item',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'catalog_category_id' => ['type' => 'integer', 'description' => 'Category ID'],
                    'name' => ['type' => 'string', 'description' => 'Item name'],
                    'description' => ['type' => 'string', 'description' => 'Item description'],
                    'unit_price' => ['type' => 'number', 'description' => 'Unit price'],
                    'unit' => ['type' => 'string', 'description' => 'Unit type', 'enum' => ['hour', 'day', 'unit', 'fixed', 'line']],
                    'vat_rate' => ['type' => 'number', 'description' => 'VAT rate (0, 5.5, 10, 20)'],
                    'default_quantity' => ['type' => 'number', 'description' => 'Default quantity for quotes/invoices (default: 1)'],
                    'is_active' => ['type' => 'boolean', 'description' => 'Active status (default: true)'],
                    'sku' => ['type' => 'string', 'description' => 'SKU code (optional, unique)'],
                    'minimum_quantity' => ['type' => 'number', 'description' => 'Minimum quantity'],
                ],
                'required' => ['catalog_category_id', 'name', 'unit_price', 'unit', 'vat_rate'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'update_catalog_item',
            'description' => 'Update a catalog item',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Item ID'],
                    'catalog_category_id' => ['type' => 'integer', 'description' => 'Category ID'],
                    'name' => ['type' => 'string', 'description' => 'Item name'],
                    'description' => ['type' => 'string', 'description' => 'Item description'],
                    'unit_price' => ['type' => 'number', 'description' => 'Unit price'],
                    'unit' => ['type' => 'string', 'description' => 'Unit type', 'enum' => ['hour', 'day', 'unit', 'fixed', 'line']],
                    'vat_rate' => ['type' => 'number', 'description' => 'VAT rate (0, 5.5, 10, 20)'],
                    'default_quantity' => ['type' => 'number', 'description' => 'Default quantity for quotes/invoices'],
                    'is_active' => ['type' => 'boolean', 'description' => 'Active status'],
                    'sku' => ['type' => 'string', 'description' => 'SKU code (unique)'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'delete_catalog_item',
            'description' => 'Delete a catalog item',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Item ID'],
                ],
                'required' => ['id'],
            ],
        ];

        // Media
        $this->toolDefinitions[] = [
            'name' => 'list_media',
            'description' => 'List all media files in the library',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'collection' => ['type' => 'string', 'description' => 'Filter by collection name'],
                    'mime_type' => ['type' => 'string', 'description' => 'Filter by MIME type prefix (e.g., "image")'],
                    'search' => ['type' => 'string', 'description' => 'Search by name'],
                ],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'get_media',
            'description' => 'Get a single media file details',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Media ID'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'upload_media_from_url',
            'description' => 'Upload media from a URL to the library. Use this to stage images before attaching to social posts.',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'url' => ['type' => 'string', 'description' => 'URL of the image to download'],
                    'name' => ['type' => 'string', 'description' => 'Optional name for the media file'],
                    'collection' => ['type' => 'string', 'description' => 'Optional collection name (default: "default")'],
                ],
                'required' => ['url'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'delete_media',
            'description' => 'Delete a media file from the library',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Media ID'],
                ],
                'required' => ['id'],
            ],
        ];

        // Image Generation
        $this->toolDefinitions[] = [
            'name' => 'generate_image',
            'description' => 'Generate an image from text content using OpenAI GPT Image 1.5. The image will be saved in the generated images library and can be attached to social posts.',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'content' => ['type' => 'string', 'description' => 'Text content to generate an image from (10-5000 characters). The system prompt will add branding guidelines.'],
                    'social_post_id' => ['type' => 'integer', 'description' => 'Optional: Link the generated image to a social post'],
                ],
                'required' => ['content'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'generate_image_for_post',
            'description' => 'Generate an image based on a social post content and automatically attach it to that post.',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'social_post_id' => ['type' => 'integer', 'description' => 'Social post ID to generate an image for'],
                ],
                'required' => ['social_post_id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'list_generated_images',
            'description' => 'List generated images with optional filters',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'social_post_id' => ['type' => 'integer', 'description' => 'Filter by linked social post ID'],
                    'has_social_post' => ['type' => 'boolean', 'description' => 'Filter by whether image is linked to a post'],
                    'limit' => ['type' => 'integer', 'description' => 'Maximum number of results (default: 20, max: 100)'],
                ],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'delete_generated_image',
            'description' => 'Delete a generated image from the library',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Generated image ID'],
                ],
                'required' => ['id'],
            ],
        ];

        // Blog Posts
        $this->toolDefinitions[] = [
            'name' => 'list_posts',
            'description' => 'List blog posts with optional filters',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'status' => ['type' => 'string', 'description' => 'Filter by status: draft, published'],
                    'category_id' => ['type' => 'integer', 'description' => 'Filter by category ID'],
                    'is_featured' => ['type' => 'boolean', 'description' => 'Filter featured posts only'],
                    'search' => ['type' => 'string', 'description' => 'Search in title and content'],
                ],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'get_post',
            'description' => 'Get a single blog post with category, author, and tags',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Post ID'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'create_post',
            'description' => 'Create a new blog post',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'title' => ['type' => 'string', 'description' => 'Post title (required)'],
                    'content' => ['type' => 'string', 'description' => 'Post content in HTML (required)'],
                    'excerpt' => ['type' => 'string', 'description' => 'Short excerpt/summary'],
                    'slug' => ['type' => 'string', 'description' => 'URL slug (auto-generated if not provided)'],
                    'category_id' => ['type' => 'integer', 'description' => 'Category ID'],
                    'tag_ids' => ['type' => 'array', 'items' => ['type' => 'integer'], 'description' => 'Array of tag IDs'],
                    'status' => ['type' => 'string', 'description' => 'Status: draft, published (default: draft)'],
                    'is_featured' => ['type' => 'boolean', 'description' => 'Mark as featured post'],
                    'meta_title' => ['type' => 'string', 'description' => 'SEO meta title'],
                    'meta_description' => ['type' => 'string', 'description' => 'SEO meta description'],
                ],
                'required' => ['title', 'content'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'update_post',
            'description' => 'Update an existing blog post',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Post ID (required)'],
                    'title' => ['type' => 'string', 'description' => 'Post title'],
                    'content' => ['type' => 'string', 'description' => 'Post content in HTML'],
                    'excerpt' => ['type' => 'string', 'description' => 'Short excerpt/summary'],
                    'slug' => ['type' => 'string', 'description' => 'URL slug'],
                    'category_id' => ['type' => 'integer', 'description' => 'Category ID'],
                    'tag_ids' => ['type' => 'array', 'items' => ['type' => 'integer'], 'description' => 'Array of tag IDs'],
                    'status' => ['type' => 'string', 'description' => 'Status: draft, published'],
                    'is_featured' => ['type' => 'boolean', 'description' => 'Mark as featured post'],
                    'meta_title' => ['type' => 'string', 'description' => 'SEO meta title'],
                    'meta_description' => ['type' => 'string', 'description' => 'SEO meta description'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'delete_post',
            'description' => 'Delete a blog post (soft delete)',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Post ID'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'publish_post',
            'description' => 'Publish a draft blog post',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Post ID'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'unpublish_post',
            'description' => 'Unpublish a blog post (revert to draft)',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Post ID'],
                ],
                'required' => ['id'],
            ],
        ];

        // Blog Categories
        $this->toolDefinitions[] = [
            'name' => 'list_categories',
            'description' => 'List all blog categories',
            'inputSchema' => [
                'type' => 'object',
                'properties' => (object) [],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'get_category',
            'description' => 'Get a single blog category with post count',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Category ID'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'create_category',
            'description' => 'Create a new blog category',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string', 'description' => 'Category name (required)'],
                    'slug' => ['type' => 'string', 'description' => 'URL slug (auto-generated if not provided)'],
                    'description' => ['type' => 'string', 'description' => 'Category description'],
                    'color' => ['type' => 'string', 'description' => 'Category color (hex format, e.g. #FF5733)'],
                    'sort_order' => ['type' => 'integer', 'description' => 'Sort order for display'],
                ],
                'required' => ['name'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'update_category',
            'description' => 'Update a blog category',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Category ID (required)'],
                    'name' => ['type' => 'string', 'description' => 'Category name'],
                    'slug' => ['type' => 'string', 'description' => 'URL slug'],
                    'description' => ['type' => 'string', 'description' => 'Category description'],
                    'color' => ['type' => 'string', 'description' => 'Category color (hex format)'],
                    'sort_order' => ['type' => 'integer', 'description' => 'Sort order for display'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'delete_category',
            'description' => 'Delete a blog category (fails if posts are using it)',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Category ID'],
                ],
                'required' => ['id'],
            ],
        ];

        // Blog Tags
        $this->toolDefinitions[] = [
            'name' => 'list_tags',
            'description' => 'List all blog tags',
            'inputSchema' => [
                'type' => 'object',
                'properties' => (object) [],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'get_tag',
            'description' => 'Get a single blog tag with post count',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Tag ID'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'create_tag',
            'description' => 'Create a new blog tag',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string', 'description' => 'Tag name (required)'],
                    'slug' => ['type' => 'string', 'description' => 'URL slug (auto-generated if not provided)'],
                ],
                'required' => ['name'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'update_tag',
            'description' => 'Update a blog tag',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Tag ID (required)'],
                    'name' => ['type' => 'string', 'description' => 'Tag name'],
                    'slug' => ['type' => 'string', 'description' => 'URL slug'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'delete_tag',
            'description' => 'Delete a blog tag (automatically detached from posts)',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Tag ID'],
                ],
                'required' => ['id'],
            ],
        ];

        // Stakeholders
        $this->toolDefinitions[] = [
            'name' => 'list_stakeholders',
            'description' => 'List all stakeholders (participants) with optional filters',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'search' => ['type' => 'string', 'description' => 'Search in name, email, company'],
                    'is_active' => ['type' => 'boolean', 'description' => 'Filter by active status'],
                ],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'get_stakeholder',
            'description' => 'Get a single stakeholder with notes and tasks counts',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Stakeholder ID'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'create_stakeholder',
            'description' => 'Create a new stakeholder',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string', 'description' => 'Stakeholder name (required)'],
                    'email' => ['type' => 'string', 'description' => 'Email address'],
                    'phone' => ['type' => 'string', 'description' => 'Phone number'],
                    'company' => ['type' => 'string', 'description' => 'Company name'],
                    'role' => ['type' => 'string', 'description' => 'Role/position'],
                    'notes' => ['type' => 'string', 'description' => 'Additional notes'],
                ],
                'required' => ['name'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'update_stakeholder',
            'description' => 'Update an existing stakeholder',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Stakeholder ID (required)'],
                    'name' => ['type' => 'string', 'description' => 'Stakeholder name'],
                    'email' => ['type' => 'string', 'description' => 'Email address'],
                    'phone' => ['type' => 'string', 'description' => 'Phone number'],
                    'company' => ['type' => 'string', 'description' => 'Company name'],
                    'role' => ['type' => 'string', 'description' => 'Role/position'],
                    'notes' => ['type' => 'string', 'description' => 'Additional notes'],
                    'is_active' => ['type' => 'boolean', 'description' => 'Active status'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'delete_stakeholder',
            'description' => 'Delete a stakeholder (automatically detached from notes and tasks)',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Stakeholder ID'],
                ],
                'required' => ['id'],
            ],
        ];

        // Note Scopes
        $this->toolDefinitions[] = [
            'name' => 'list_note_scopes',
            'description' => 'List all note scopes (tags)',
            'inputSchema' => [
                'type' => 'object',
                'properties' => (object) [],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'get_note_scope',
            'description' => 'Get a single note scope with notes count',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Note Scope ID'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'create_note_scope',
            'description' => 'Create a new note scope',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string', 'description' => 'Scope name (required)'],
                    'slug' => ['type' => 'string', 'description' => 'URL slug (auto-generated if not provided)'],
                    'color' => ['type' => 'string', 'description' => 'Color (hex format, e.g. #FF5733)'],
                ],
                'required' => ['name'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'update_note_scope',
            'description' => 'Update a note scope',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Note Scope ID (required)'],
                    'name' => ['type' => 'string', 'description' => 'Scope name'],
                    'slug' => ['type' => 'string', 'description' => 'URL slug'],
                    'color' => ['type' => 'string', 'description' => 'Color (hex format)'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'delete_note_scope',
            'description' => 'Delete a note scope (fails if notes are using it)',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Note Scope ID'],
                ],
                'required' => ['id'],
            ],
        ];

        // Notes
        $this->toolDefinitions[] = [
            'name' => 'list_notes',
            'description' => 'List notes (hierarchical pages) with optional filters',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'parent_id' => ['type' => 'integer', 'description' => 'Filter by parent note ID (null for root notes)'],
                    'scope_id' => ['type' => 'integer', 'description' => 'Filter by scope ID'],
                    'stakeholder_id' => ['type' => 'integer', 'description' => 'Filter by stakeholder ID'],
                    'search' => ['type' => 'string', 'description' => 'Search in note name'],
                    'root_only' => ['type' => 'boolean', 'description' => 'Return only root-level notes'],
                ],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'get_note',
            'description' => 'Get a single note with full content, stakeholders, scopes, and children',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Note ID'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'get_note_children',
            'description' => 'Get direct children (subpages) of a note',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Parent Note ID'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'get_note_tree',
            'description' => 'Get hierarchical tree of notes',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'root_id' => ['type' => 'integer', 'description' => 'Root note ID (optional, null returns all root notes with their children)'],
                ],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'search_notes',
            'description' => 'Full-text search across all note content fields using FTS5 index. Searches in: name, short_summary, long_summary, notes, transcription. Features: (1) Results ranked by relevance (BM25 algorithm), (2) Phrase search with quotes: "exact phrase", (3) Prefix/partial matching: searching "meet" finds "meeting", "meetings". Returns up to 50 notes with relevance scores and context snippets showing where matches were found.',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'query' => ['type' => 'string', 'description' => 'Search query. Use quotes for exact phrase: "project review". Multiple words are OR-matched with prefix support.'],
                    'scope_id' => ['type' => 'integer', 'description' => 'Filter by scope ID'],
                    'stakeholder_id' => ['type' => 'integer', 'description' => 'Filter by stakeholder ID'],
                    'date_from' => ['type' => 'string', 'description' => 'Filter notes from this date (ISO 8601)'],
                    'date_to' => ['type' => 'string', 'description' => 'Filter notes until this date (ISO 8601)'],
                ],
                'required' => ['query'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'create_note',
            'description' => 'Create a new note (hierarchical page)',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string', 'description' => 'Note name (required)'],
                    'datetime' => ['type' => 'string', 'description' => 'Date/time (ISO 8601, defaults to now)'],
                    'short_summary' => ['type' => 'string', 'description' => 'Short summary (HTML)'],
                    'long_summary' => ['type' => 'string', 'description' => 'Long summary (HTML)'],
                    'notes' => ['type' => 'string', 'description' => 'Detailed notes (HTML)'],
                    'transcription' => ['type' => 'string', 'description' => 'Transcription text'],
                    'parent_id' => ['type' => 'integer', 'description' => 'Parent note ID for creating subpage'],
                    'stakeholder_ids' => ['type' => 'array', 'items' => ['type' => 'integer'], 'description' => 'Array of stakeholder IDs'],
                    'scope_ids' => ['type' => 'array', 'items' => ['type' => 'integer'], 'description' => 'Array of scope IDs'],
                ],
                'required' => ['name'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'update_note',
            'description' => 'Update an existing note',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Note ID (required)'],
                    'name' => ['type' => 'string', 'description' => 'Note name'],
                    'datetime' => ['type' => 'string', 'description' => 'Date/time (ISO 8601)'],
                    'short_summary' => ['type' => 'string', 'description' => 'Short summary (HTML)'],
                    'long_summary' => ['type' => 'string', 'description' => 'Long summary (HTML)'],
                    'notes' => ['type' => 'string', 'description' => 'Detailed notes (HTML)'],
                    'transcription' => ['type' => 'string', 'description' => 'Transcription text'],
                    'parent_id' => ['type' => 'integer', 'description' => 'Parent note ID'],
                    'stakeholder_ids' => ['type' => 'array', 'items' => ['type' => 'integer'], 'description' => 'Array of stakeholder IDs'],
                    'scope_ids' => ['type' => 'array', 'items' => ['type' => 'integer'], 'description' => 'Array of scope IDs'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'delete_note',
            'description' => 'Delete a note (soft delete)',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Note ID'],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'set_note_parent',
            'description' => 'Move a note to a new parent (change hierarchy)',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'description' => 'Note ID to move (required)'],
                    'parent_id' => ['type' => 'integer', 'description' => 'New parent note ID (null for root level)'],
                ],
                'required' => ['id'],
            ],
        ];
    }

    /**
     * Normalize parameter aliases before validation
     * This converts alternative parameter names to their canonical form
     */
    private function normalizeAliases(string $toolName, array $args): array
    {
        $aliases = [
            'create_invoice' => ['invoice_number' => 'number'],
            'create_quote' => ['quote_number' => 'number'],
        ];

        if (isset($aliases[$toolName])) {
            foreach ($aliases[$toolName] as $alias => $canonical) {
                if (isset($args[$alias]) && !isset($args[$canonical])) {
                    $args[$canonical] = $args[$alias];
                    unset($args[$alias]);
                }
            }
        }

        return $args;
    }
}
