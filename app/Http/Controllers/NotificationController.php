<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Fetch notifications from the authenticated user
     * 
     * @param $limit Notification limit
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $limit = $request->get('limit', 10);

        $query = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return response()->json($query->get());
    }

    /**
     * Mark the notification as read fro the logged user
     * 
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(Request $request, string $id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if(!$notification) {
            return response()->json([
                'message' => 'Notificação não encontrada.'
            ], 404);
        }

        $notification->is_read = true;
        $notification->save();

        return response()->json([
            'message' => 'Notificação marcada como visualizada'
        ]);
    }

    /**
     * Mark all notifications as read for the logged user
     * 
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead(Request $request)
    {
        Notification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'message' => 'Todas as notificações foram marcadas como lidas'
        ]);
    }
}
