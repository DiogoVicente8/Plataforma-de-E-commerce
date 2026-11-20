<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'unread_count' => $request->user()->unreadNotifications()->count(),
            'notifications' => $request->user()->notifications()->latest()->limit(20)->get()->map(fn ($notification) => [
                'id' => $notification->id,
                'title' => $notification->data['title'] ?? 'Notificação',
                'body' => $notification->data['body'] ?? '',
                'url' => $notification->data['url'] ?? '/',
                'kind' => $notification->data['kind'] ?? 'order',
                'read' => $notification->read_at !== null,
                'created_at' => $notification->created_at->diffForHumans(),
                'read_url' => route('account.notifications.read', $notification->id, absolute: false),
            ]),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function read(Request $request, string $notification): JsonResponse
    {
        $request->user()->notifications()->findOrFail($notification)->markAsRead();

        return $this->index($request);
    }

    public function readAll(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return $this->index($request);
    }
}
