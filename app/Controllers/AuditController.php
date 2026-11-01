<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AuditLog;
use Core\Controller;

// The Audit Log (admin/audit): read-only cards of the newest events — every sign-in and every change.
// The filters are a normal GET form: the server finds the matching events in the whole log (not only the newest
// ones on the page). Settings: config/audit.php; who may open it: config/permissions.php 'audit.view'.
class AuditController extends Controller
{
    // GET /{role}/audit?action=LOGIN_FAILED&role=teacher&q=…
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

        $this->page("{$role}/audit", $user, [
            'logs'        => $log->recent($filters, config('audit.shown')),
            'filters'     => $filters,
            'actionNames' => $names,
        ]);
    }
}
