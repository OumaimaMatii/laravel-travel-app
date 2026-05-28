<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // GET /notifications - Liste les notifications de l'utilisateur connecté
    public function index(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié'
            ], 401);
        }
        
        $perPage = $request->get('per_page', 20);
        
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => $notifications,
            'unread_count' => $user->notifications()->unread()->count()
        ]);
    }

    // GET /notifications/unread-count - Compte les notifications non lues
    public function unreadCount(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'count' => 0
            ], 401);
        }
        
        $count = $user->notifications()->unread()->count();
        
        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }

    // POST /notifications/{id}/read - Marque une notification comme lue
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié'
            ], 401);
        }
        
        $notification = Notification::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();
        
        $notification->markAsRead();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification marquée comme lue'
        ]);
    }

    // POST /notifications/read-all - Marque toutes les notifications comme lues
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié'
            ], 401);
        }
        
        $count = $user->notifications()->unread()->count();
        
        $user->notifications()->unread()->update([
            'lue' => true,
            'lue_le' => now(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => "$count notification(s) marquée(s) comme lue(s)"
        ]);
    }

    // DELETE /notifications/{id} - Supprime une notification
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié'
            ], 401);
        }
        
        $notification = Notification::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();
        
        $notification->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification supprimée'
        ]);
    }

    // DELETE /notifications - Supprime toutes les notifications de l'utilisateur
    public function destroyAll(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié'
            ], 401);
        }
        
        $count = $user->notifications()->count();
        
        $user->notifications()->delete();
        
        return response()->json([
            'success' => true,
            'message' => "$count notification(s) supprimée(s)"
        ]);
    }
}