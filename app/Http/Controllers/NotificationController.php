<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\User;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = auth()->user();
        $notifications = $user->notifications()->limit(10)->get();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark a notification as read.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);

        if ($notification->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead()
    {
        $user = auth()->user();
        $user->unreadNotifications()->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Create a notification for a user.
     *
     * @param  int  $userId
     * @param  string  $type
     * @param  string  $title
     * @param  string  $message
     * @param  string|null  $link
     * @param  array|null  $data
     * @return \App\Models\Notification
     */
    public static function createNotification($userId, $type, $title, $message, $link = null, $data = null)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'data' => $data,
        ]);
    }

    /**
     * Create notification for all admin users.
     *
     * @param  string  $type
     * @param  string  $title
     * @param  string  $message
     * @param  string|null  $link
     * @param  array|null  $data
     * @return void
     */
    public static function createForAdmins($type, $title, $message, $link = null, $data = null)
    {
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            self::createNotification($admin->id, $type, $title, $message, $link, $data);
        }
    }
}
