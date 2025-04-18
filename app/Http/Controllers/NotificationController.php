<!-- <?php
// namespace App\Http\Controllers;

// use App\Models\Notification;
// use Illuminate\Http\Request;
// use App\Trait\NotificationTrait;
// use App\Http\Controllers\Controller;
// use Illuminate\Support\Facades\Auth;

// class NotificationController extends Controller

// {
    // use NotificationTrait;
    // public function getUserNotification()
    // {
        // Récupérer l'utilisateur connecté
    //     $user = Auth::user();

    //     // Récupérer les notifications associées à cet utilisateur
    //     $notifications = $user->notifications()->orderBy('created_at', 'desc')->get();

    //     // Retourner les notifications sous forme de JSON
    //     return response()->json([
    //         'message' => 'Notifications récupérées avec succès',
    //         'données' => $notification,
    //         'status' => 200
    //     ]);
    // }
    // public function index()
    // {
    //     $userId = Auth::id();
    //     $notifications = Notification::where('user_id', $userId)->get();

    //     return response()->json([
    //         'status' => true,
    //         'data' => $notifications,
    //     ], 200);
    // }
//     public function index()
// {
//     return $this->getAllNotifications();
// }

    // public function getAllNotifications()
    // {
    //     // récuperer toutes les notifications
    //     $notifications = Notification:: all();

    //     // Retourner les notifications
    //     return response()->json([
    //         'status' => true,
    //         'data' => $notifications
    //     ], 200);
    // }

    // public function markAsRead($id)
    // {
    //     $notification = Notification::find($id);

    //    // if ($notification && $notification->user_id == auth()->id())
    //     if ($notification && $notification->user_id == Auth::id()) {

    //         $notification->statut = 'lue';
    //         $notification->save();

    //         return response()->json(['message' => 'Notification marquée comme lue'], 200);
    //     }

    //     return response()->json(['message' => 'Notification non trouvée ou accès refusé'], 404);
    // }
// } -->
