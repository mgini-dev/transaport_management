<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
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
        $take = min((int) $request->integer('take', 20), 100);

        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->skip($skip)
            ->take($take)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'read_at' => $item->read_at,
                    'created_at' => $item->created_at,
                    'data' => $item->data,
                ];
            });

        return response()->json(['data' => $notifications]);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'All notifications marked as read.');
    }

    public function markAsRead(Request $request, string $notificationId): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($notificationId);

        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read.']);
    }
}
