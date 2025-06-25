<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Trait\DonationNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\Notifiable;

class NotificationController extends Controller
{

     use DonationNotification;
    //   public function getUserNotifications()
    // {
    //     $user = Auth::user();
        
    //     // Utilisez votre structure personnalisée au lieu du système Laravel
    //     $notifications = Notification::where('user_id', $user->id)
    //         ->orderBy('created_at', 'desc')
    //         ->get();
            
    //     return response()->json([
    //         'success' => true,
    //         'data' => $notifications
    //     ]);
    // }
    public function getUserNotifications()
    {
        // Récupérer l'utilisateur connecté
        
        $user = Auth::user();

        // Récupérer les notifications associées à cet utilisateur
        $notifications = $user->notifications()->orderBy('created_at', 'desc')->get();

        // Retourner les notifications sous forme de JSON
        return response()->json([
            'message' => 'Notifications récupérées avec succès',
            'données' => $notifications,
            'status' => 200
        ]);
    }
    // Méthode pour marquer comme lue
    public function markAsRead($id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();
            
        if ($notification) {
            $notification->update(['statut' => 'lue']);
            return response()->json(['success' => true, 'message' => 'Notification marquée comme lue']);
        }
        
        return response()->json(['success' => false, 'message' => 'Notification non trouvée'], 404);
    }
    
    // Méthode pour compter les non-lues
    public function getUnreadCount()
    {
        $count = Notification::where('user_id', Auth::id())
            ->where('statut', 'non-lue')
            ->count();
            
        return response()->json(['unread_count' => $count]);
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        
        $updated = $user->notifications()
                       ->where('statut', 'non_lue')
                       ->update(['statut' => 'lue']);

        return response()->json([
            'message' => 'Toutes les notifications ont été marquées comme lues',
            'notifications_updated' => $updated,
            'status' => 200
        ]);
    }

    /**
     * Supprimer une notification
     */
    public function deleteNotification($id)
    {
        $notification = Notification::find($id);

        if ($notification && $notification->user_id == Auth::id()) {
            $notification->delete();

            return response()->json([
                'message' => 'Notification supprimée avec succès',
                'status' => 200
            ]);
        }

        return response()->json([
            'message' => 'Notification non trouvée ou accès refusé',
            'status' => 404
        ], 404);
    }
}


