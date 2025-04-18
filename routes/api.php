<?php
// routes/api.php (attention à la casse du nom de fichier)
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DonateurController;
use App\Http\Controllers\Api\ParticipationController;
use App\Http\Controllers\Api\StructureTransfusionController;
use App\Http\Controllers\CampagneController;
use App\Http\Controllers\OrganisateurController;
use App\Http\Controllers\CampagneStructureController;
// use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Banque_sangController;
use App\Http\Controllers\DemandeRavitaillementController;

// Routes pour l'enregistrement et la connexion (publiques)
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');


// Routes protégées par authentification
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/refresh-token', [AuthController::class, 'refresh'])->name('refresh'); // Corrigé: refresh au lieu de refreshToken
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
});
// Routes pour les opérations CRUD sur les donateurs

// Récupérer le donateur connecté (nécessite authentification avec token)
Route::middleware('auth:api')->get('/donateur', [DonateurController::class, 'getAuthenticatedDonateur']);
// Récupérer un donateur par l'ID de l'utilisateur
Route::get('/donateurs/utilisateur/{userId}', [DonateurController::class, 'getDonateurByUserId']);
Route::apiResource('donateurs', DonateurController::class);


// Route pour créer une participation (nécessite authentification avec token)
Route::middleware(['auth:api'])->group(function () {
    Route::get('/participations/historiques', [ParticipationController::class, 'historiquecampagnes']);
    Route::get('/participations/campagne/{campagneId}/donateurs', [ParticipationController::class, 'donateursParCampagne']);
});


    Route::apiResource('banques', App\Http\Controllers\Banque_sangController::class);
    Route::apiResource('demandes', DemandeRavitaillementController::class);



// Routes pour l'organisateur et ces opérations crud sur les campagnes 
Route::middleware(['auth:api'])->group(function () {
    Route::post('/campagnes', [CampagneController::class, 'store']);
    Route::get('/organisateurs/{id}/campagnes', [CampagneController::class, 'getCampagnesByOrganisateurId']);
    Route::get('campagnes/{campagne}/participations', [CampagneController::class, 'participants']);
    Route::patch('participations/{id}/valider', [CampagneController::class, 'validerParticipation']);
    Route::apiResource('campagnes', CampagneController::class);
    Route::put('/organisateurs/campagnes/{id}', [CampagneController::class, 'update']);

});
Route::middleware(['auth:api'])->group(function () {
    Route::get('/structure/campagnes', [CampagneStructureController::class, 'index']);
    Route::post('/structure/campagnes', [CampagneStructureController::class, 'store']);
    Route::get('/struture/{id}/campagnes', [CampagneController::class, 'getCampagnesByOrganisateurId']);
    Route::get('/structure/campagnes/{id}', [CampagneStructureController::class, 'show']);
    Route::put('/structure/campagnes/{id}', [CampagneStructureController::class, 'update']);
    Route::delete('/structure/campagnes/{id}', [CampagneStructureController::class, 'destroy']);
    Route::apiResource('structure', CampagneStructureController::class);

});















// Route pour récupérer le donateur connecté
// Route::get('donateur/user', [DonateurController::class, 'getByUser'])->middleware('auth:api');
// Route::apiResource('/donateurs', DonateurController::class);

// //  Routes protégées avec JWT
// Route::middleware('auth:api')->group(function () {
//     Route::get('/profile', [AuthController::class, 'profile']);
//     Route::post('/logout', [AuthController::class, 'logout']);

    //  (Facultatif) Rafraîchir le token
    // Route::post('/refresh', [AuthController::class, 'refresh']);

    // //  Routes RESTful pour les modèles
    // Route::apiResource('/donateurs', DonateurController::class);
    // Route::apiResource('/organisateurs', OrganisateurController::class);
    // Route::apiResource('/structures-transfusion', StructureTransfusionController::class);


    // Route::get('donateurs/{id}/dons', [ParticipationController::class, 'historiqueDons']);
// Route::get('donateurs/{id}/campagnes', [ParticipationController::class, 'historiqueCampagnes']);
// Route::apiResource('participations', ParticipationController::class);

// Route::get('/donateurs/{userId}/dons', [ParticipationController::class, 'historiqueDons']);



// Routes pour les opérations crud sur les organisateurs
// Route::apiResource('organisateurs', OrganisateurController::class);

// Routes pour les opérations crud sur les structures de transfusion sanguine   

// Routes pour les notifications
    // Route::apiResource('notification', NotificationsController::class);
    // Route::get('/notifications', [NotificationController::class, 'getUserNotification']);
    // Route::post('/notificationsend', [NotificationController::class, 'sendNotification']);
