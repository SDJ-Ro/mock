<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AuditLog;
use Core\Controller;

// The Audit Log (admin/audit): read-only cards of the events, newest first, one page at a time (page links).
// The filters are a normal GET form: the server finds the matching events in the whole log. Settings: config/audit.php; who may open it: config/permissions.php 'audit.view'.
class AuditController extends Controller
{
    // GET /{role}/audit?action=LOGIN_FAILED&role=teacher&q=…&page=2
    public function index(string $role): void
    {
        $user  = $this->requireRole($role);
        $log   = new AuditLog();
        $names = [];

        foreach ($log->actions() as $code)
        {
            $names[$code] = config("audit.names.{$code}") ?? ucwords(strtolower(str_replace('_', ' ', $code)));
        }

        // Only values from the lists are kept (anything else = "all")
        $filters = [
            'action' => array_key_exists($this->queryText('action'), $names) ? $this->queryText('action') : '',
            'role'   => array_key_exists($this->queryText('role'), config('audit.tones')) ? $this->queryText('role') : '',
            'q'      => $this->queryText('q'),
        ];

        // The page asked for; one past the last page shows the last
        $perPage = (int) config('audit.per_page');
        [$logs, $total] = $log->page($filters, max(1, (int) $this->queryText('page')), $perPage);
        $pages   = max(1, (int) ceil($total / $perPage));
        $page    = min($pages, max(1, (int) $this->queryText('page')));

        if ($logs === [] && $total > 0)
        {
            [$logs] = $log->page($filters, $page, $perPage);
        }

        $this->page("{$role}/audit", $user, [
            'logs'        => $logs,
            'total'       => $total,
            'page'        => $page,
            'pages'       => $pages,
            'filters'     => $filters,
            'actionNames' => $names,
        ]);
    }
}
