<?php

namespace App\Services\Mcp\Resources;

use App\Models\Invoice;
use App\Models\Project;
use App\Models\SocialPost;
use App\Models\Task;

class DashboardResource
{
    /**
     * List available resource URIs for MCP
     */
    public function listResources(): array
    {
        return [
            [
                'uri' => 'helper://dashboard',
                'name' => 'Dashboard Statistics',
                'description' => 'Get dashboard statistics and recent activity',
                'mimeType' => 'application/json',
            ],
        ];
    }

    /**
     * Read resource by path for MCP
     */
    public function read(string $path, array $params = []): array
    {
        return $this->get();
    }

    /**
     * Alias for MCP tool calls
     */
    public function getDashboard(): array
    {
        return $this->get();
    }

    /**
     * Get dashboard statistics and recent items
     */
    public function get(): array
    {
        return [
            'summary' => $this->getSummaryStats(),
            'recent_invoices' => $this->getRecentInvoices(),
            'recent_tasks' => $this->getRecentTasks(),
        ];
    }

    /**
     * Get summary statistics
     */
    private function getSummaryStats(): array
    {
        // Unpaid invoices - count and total amount
        $unpaidInvoices = Invoice::whereNotIn('status', ['paid', 'cancelled'])
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_ttc - amount_paid), 0) as total')
            ->first();

        // Overdue invoices - past due date and not paid
        $overdueInvoices = Invoice::whereNotIn('status', ['paid', 'cancelled'])
            ->where('due_date', '<', now())
            ->count();

        // Pending tasks - not done
        $pendingTasks = Task::where('status', '!=', 'done')->count();

        // Overdue tasks - not done and past due date
        $overdueTasks = Task::where('status', '!=', 'done')
            ->where('due_date', '<', now())
            ->count();

        // Active projects
        $activeProjects = Project::where('status', 'active')->count();

        // Pending social posts - draft status
        $pendingPosts = SocialPost::where('status', 'draft')->count();

        return [
            'unpaid_invoices' => [
                'count' => $unpaidInvoices->count ?? 0,
                'total' => (float) ($unpaidInvoices->total ?? 0),
            ],
            'overdue_invoices' => $overdueInvoices,
            'pending_tasks' => $pendingTasks,
            'overdue_tasks' => $overdueTasks,
            'active_projects' => $activeProjects,
            'pending_posts' => $pendingPosts,
        ];
    }

    /**
     * Get recent invoices with client information
     */
    private function getRecentInvoices(): array
    {
        $invoices = Invoice::with('client')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return $invoices->map(function ($invoice) {
            return [
                'id' => $invoice->id,
                'number' => $invoice->number,
                'client_id' => $invoice->client_id,
                'client_name' => $invoice->client?->display_name,
                'subject' => $invoice->subject,
                'total_ttc' => (float) $invoice->total_ttc,
                'amount_paid' => (float) $invoice->amount_paid,
                'status' => $invoice->status,
                'due_date' => $invoice->due_date,
                'created_at' => $invoice->created_at,
            ];
        })->toArray();
    }

    /**
     * Get recent tasks with project information
     */
    private function getRecentTasks(): array
    {
        $tasks = Task::with('project')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return $tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status,
                'priority' => $task->priority,
                'project_id' => $task->project_id,
                'project_name' => $task->project?->name,
                'due_date' => $task->due_date,
                'created_at' => $task->created_at,
            ];
        })->toArray();
    }
}
