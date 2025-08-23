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
}
