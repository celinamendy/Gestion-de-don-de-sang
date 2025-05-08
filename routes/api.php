<?php
// routes/api.php (attention à la casse du nom de fichier)
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DonateurController;
use App\Http\Controllers\Api\ParticipationController;
use App\Http\Controllers\Api\StructureTransfusionController;
use App\Http\Controllers\CampagneController;
use App\Http\Controllers\CampagneStructureController;
// use App\Http\Controllers\NotificationController;
use App\Http\Controllers\API\BanqueDeSangController;
use App\Http\Controllers\DemandeRavitaillementController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\OrganisateurController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\API\DashboardDonateurController;
use App\Http\Controllers\Api\DashboardOrganisateurController;


// Routes pour l'enregistrement et la connexion (publiques)
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::middleware('auth:api')->get('/user-info', [UserController::class, 'getUserInfo']);


// Routes protégées par authentification
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/refresh-token', [AuthController::class, 'refresh'])->name('refresh'); // Corrigé: refresh au lieu de refreshToken
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
});
// route pour Dashboard Organisateur 
Route::middleware(['auth:api'])->prefix('dashboard-organisateur')->group(function () {
    Route::get('/statistiques', [DashboardOrganisateurController::class, 'statistiquesGenerales']);
    Route::get('/campagnes/actives', [DashboardOrganisateurController::class, 'campagnesActives']);
    Route::get('/campagnes/avenir', [DashboardOrganisateurController::class, 'campagnesAvenir']);
    Route::get('/campagnes/passees', [DashboardOrganisateurController::class, 'campagnesPassees']);
    // Route::get('/demandes/urgentes', [DashboardOrganisateurController::class, 'demandesUrgentes']);
    Route::get('/campagnes-par-mois', [DashboardOrganisateurController::class, 'campagnesParMois']);
    Route::get('/donneurs-par-groupe', [DashboardOrganisateurController::class, 'donneursParGroupe']);
    Route::get('/dashboard-organisateur/demandes-urgentes', [DashboardOrganisateurController::class, 'demandesUrgentes']);

});
// Route pour les opérations CRUD sur les banques de sang
Route::middleware(['auth:api'])->group(function () {
    Route::get('/banques', [BanqueDeSangController::class, 'index']);
    Route::post('/banques', [BanqueDeSangController::class, 'store']);
    Route::get('/banques/{id}', [BanqueDeSangController::class, 'show']);
    Route::put('/banques/{id}', [BanqueDeSangController::class, 'update']);
    Route::delete('/banques/{id}', [BanqueDeSangController::class, 'destroy']);
});






// Routes pour les opérations CRUD sur les donateurs

// // Récupérer le donateur connecté (nécessite authentification avec token)
// Route::middleware('auth:api')->get('/donateur', [DonateurController::class, 'getAuthenticatedDonateur']);
// // Récupérer un donateur par l'ID de l'utilisateur
// Route::get('/donateurs/utilisateur/{userId}', [DonateurController::class, 'getDonateurByUserId']);
// Route::apiResource('donateurs', DonateurController::class);
// Route::get('/donateurs/profil', [DonateurController::class, 'profil']);
// Route::get('/donateurs/dashboard', [DonateurDashboardController::class, 'dashboardDonateur']);


// Récupérer le donateur connecté (nécessite authentification avec token)
Route::middleware('auth:api')->group(function () {
    
Route::get('/donateur', [DonateurController::class, 'getAuthenticatedDonateur']);
// Liste de tous les donateurs
Route::get('/donateurs', [DonateurController::class, 'index']);
    
// Création d’un donateur avec utilisateur associé
Route::post('/donateurs', [DonateurController::class, 'store']);

// Afficher un donateur par ID
Route::get('/donateurs/{id}', [DonateurController::class, 'show']);

// Afficher le profil du donateur connecté
Route::get('/donateur/profil', [DonateurController::class, 'profil']);

// Récupérer un donateur par ID utilisateur
Route::get('/donateur/user/{userId}', [DonateurController::class, 'getDonateurByUserId']);

// Mise à jour d’un donateur
Route::put('/donateurs/{id}', [DonateurController::class, 'update']);

// Suppression d’un donateur
Route::delete('/donateurs/{donateur}', [DonateurController::class, 'destroy']);

// Dashboard donateur (étendu)
Route::get('/donateur/dashboard', [DonateurController::class, 'dashboardDonateur']);
});
Route::middleware('auth:api')->group(function () {
    // Mini dashboard
    Route::get('/dashboard/donateur', [DashboardDonateurController::class, 'dashboardDonateur']);
    Route::get('/dashboard', [DashboardDonateurController::class, 'index']);
    Route::get('/dashboard/user', [DashboardDonateurController::class, 'dashboardDonateur']);
    Route::get('/campagnes/avenir', [DashboardDonateurController::class, 'campagnesAVenir']);
    Route::get('/dashboard/historique', [DashboardDonateurController::class, 'historiqueDons']);
    Route::get('/dashboard/verifier', [DashboardDonateurController::class, 'verifierEligibilite']);
    Route::post('/dashboard/tester', [DashboardDonateurController::class, 'lancerTestEligibilite']);
});

// Récupérer un donateur par l'ID de l'utilisateur
// Route::get('/donateurs/utilisateur/{userId}', [DonateurController::class, 'getDonateurByUserId']);

// // CRUD sur les donateurs
// Route::apiResource('donateurs', DonateurController::class);

// // Récupérer le profil du donateur connecté
// Route::get('/donateurs/profil', [DonateurController::class, 'profil']);

// // Dashboard du donateur - afficher des informations de base
// Route::get('/donateurs/dashboard', [DonateurDashboardController::class, 'dashboardDonateur']);
// Route::get('/donateur', [DonateurController::class, 'getAuthenticatedDonateur']);
// Route pour créer une participation (nécessite authentification avec token)
Route::middleware(['auth:api'])->group(function () {
    Route::get('/participations/historiques', [ParticipationController::class, 'historiquecampagnes']);
    Route::get('/participations/campagne/{campagneId}/donateurs', [ParticipationController::class, 'donateursParCampagne']);
    // Route pour inscrire un donateur à une campagne
    Route::post('/campagnes/{campagneId}/inscription', [ParticipationController::class, 'inscriptionCampagne']);

});


    // Route::apiResource('banques', App\Http\Controllers\Banque_sangController::class);
    Route::apiResource('demandes', DemandeRavitaillementController::class);


    Route::get('/campagnes', [CampagneController::class, 'getAllCampagnes']); // accès public
    Route::get('/campagnes/{id}', [CampagneController::class, 'show']);
//route organisateur 
Route::middleware('auth:api')->group(function () {
    Route::get('/organisateur', [OrganisateurController::class, 'getAuthenticatedOrganisateur']);
    Route::get('/organisateur/user', [OrganisateurController::class, 'getByUserId']);
    Route::get('/organisateur/{id}', [OrganisateurController::class, 'show']);
    Route::get('/organisateurs', [OrganisateurController::class, 'index']);
});

// Route::get('/organisateurs/user/{id}', [OrganisateurController::class, 'getByUserId']);

// Campagnes
Route::middleware('auth:api')->group(function () {
    // Route::get('/campagnes', [CampagneController::class, 'index']);
    Route::get('/mes-campagnes', [CampagneController::class, 'mesCampagnes']);

    Route::get('/campagnes/{id}', [CampagneController::class, 'show']);
    Route::get('/campagnes/{campagneId}/donateurs', [ParticipationController::class, 'donateursDeMaCampagne']);

    Route::get('/campagnes/actives', [CampagneController::class, 'campagnesActives']);
    Route::get('/campagnes/passees', [CampagneController::class, 'campagnesPassées']);
    Route::get('/campagnes/validees', [CampagneController::class, 'campagnesValidees']);
    Route::get('/campagnes/annulees', [CampagneController::class, 'campagnesAnnulees']);
    Route::get('/campagnes/structure/{id}', [CampagneController::class, 'getCampagnesByStructureId']);
    // Route::get('/mes-campagnes', [CampagneController::class, 'mesCampagnes']);
    Route::get('organisateurs/mes-campagnes', [OrganisateurController::class, 'mesCampagnes']);
    Route::post('/campagnes', [CampagneController::class, 'store']);
    Route::get('/organisateurs/{id}/campagnes', [CampagneController::class, 'getCampagnes']);
    Route::put('/campagnes/{id}', [CampagneController::class, 'update']);
    Route::delete('/campagnes/{id}', [CampagneController::class, 'destroy']);
    Route::post('/campagnes/{id}/valider', [CampagneController::class, 'valider']);
    Route::get('/campagnes/{id}/participants', [CampagneController::class, 'participants']);
    Route::put('/participations/{id}/valider', [CampagneController::class, 'validerParticipation']);
//route pour l'organisateur 







Route::get('/campagnes/{id}/participants', [DashboardController::class, 'participations']);
Route::get('/campagnes/{id}/demandes', [DashboardController::class, 'demandes']); // Récupérer les demandes liées à une campagne spécifique
});
Route::middleware(['auth:api'])->group(function () {
    Route::get('/structure/campagnes', [CampagneStructureController::class, 'index']);
    Route::post('/structure/campagnes', [CampagneStructureController::class, 'store']);
    Route::get('/struture/{id}/campagnes', [CampagneController::class, 'getCampagnesByOrganisateurId']);
    Route::get('/structure/campagnes/{id}', [CampagneStructureController::class, 'show']);
    Route::put('/structure/campagnes/{id}', [CampagneStructureController::class, 'update']);
    Route::delete('/structure/campagnes/{id}', [CampagneStructureController::class, 'destroy']);
    Route::apiResource('structure', CampagneStructureController::class);
    Route::get('/structures/organisateur/{id}', [StructureController::class, 'getByOrganisateur']);

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
