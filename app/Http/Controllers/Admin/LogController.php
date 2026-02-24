<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', AuditLog::class);

        $perPage = (int) $request->integer('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $baseQuery = AuditLog::query()
            ->with('user')
            ->when($request->filled('action'), fn ($query) => $query->where('action', 'like', '%'.$request->string('action')->toString().'%'));

        $logs = (clone $baseQuery)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'unique_actions' => (clone $baseQuery)->distinct('action')->count('action'),
            'active_users' => (clone $baseQuery)->whereNotNull('user_id')->distinct('user_id')->count('user_id'),
        ];

        return view('admin.logs.index', compact('logs', 'stats', 'perPage'));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', AuditLog::class);

        $logs = AuditLog::query()
            ->with('user')
            ->when($request->filled('action'), fn ($query) => $query->where('action', 'like', '%'.$request->string('action').'%'))
            ->latest()
            ->limit(5000)
            ->get();

        return response()->streamDownload(function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'User', 'Action', 'Context', 'IP']);
            foreach ($logs as $log) {
                fputcsv($handle, [
                    (string) $log->created_at,
                    $log->user?->name ?? '-',
                    $log->action,
                    json_encode($log->context),
                    $log->ip_address,
                ]);
            }
            fclose($handle);
        }, 'nmis-audit-logs-'.now()->format('Ymd-His').'.csv');
    }
}
