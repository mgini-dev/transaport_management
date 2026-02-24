<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function center(Request $request): View
    {
        return view('notifications.center', [
            'notifications' => $request->user()->notifications()->latest()->paginate(25),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $skip = (int) $request->integer('skip', 0);
        $take = min((int) $request->integer('take', 10), 100);
        $search = trim($request->string('search')->toString());

        $baseQuery = $request->user()
            ->notifications()
            ->latest();

        if ($search !== '') {
            $baseQuery->where(function ($query) use ($search) {
                $query->where('type', 'like', '%'.$search.'%')
                    ->orWhere('data', 'like', '%'.$search.'%');
            });
        }

        $total = (clone $baseQuery)->count();
        $unread = (clone $baseQuery)->whereNull('read_at')->count();
        $read = max($total - $unread, 0);

        $notifications = (clone $baseQuery)
            ->skip($skip)
            ->take($take)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'read_at' => $item->read_at?->toDateTimeString(),
                    'created_at' => $item->created_at?->toDateTimeString(),
                    'data' => $item->data,
                ];
            });

        return response()->json([
            'data' => $notifications,
            'meta' => [
                'total' => $total,
                'unread' => $unread,
                'read' => $read,
                'skip' => $skip,
                'take' => $take,
            ],
        ]);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'All notifications marked as read.');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $notifications = $request->user()->notifications()->latest()->limit(3000)->get();

        return response()->streamDownload(function () use ($notifications) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Title', 'Message', 'Type', 'Read']);
            foreach ($notifications as $item) {
                fputcsv($handle, [
                    (string) $item->created_at,
                    data_get($item->data, 'title'),
                    data_get($item->data, 'message'),
                    data_get($item->data, 'type'),
                    $item->read_at ? 'Yes' : 'No',
                ]);
            }
            fclose($handle);
        }, 'nmis-notifications-'.now()->format('Ymd-His').'.csv');
    }

    public function markAsRead(Request $request, string $notificationId): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($notificationId);

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read.',
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }
}
