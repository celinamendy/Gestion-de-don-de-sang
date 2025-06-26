<?php
// routes/api.php (attention à la casse du nom de fichier)
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DonateurController;
use App\Http\Controllers\Api\ParticipationController;
// use App\Http\Controllers\Api\StructureTransfusionController;
use App\Http\Controllers\API\StructureTransfusionSanguinController;
use App\Http\Controllers\CampagneController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\BanqueDeSangController;
use App\Http\Controllers\API\RegionController;
use App\Http\Controllers\Api\GroupeSanguinController;
use App\Http\Controllers\DemandeRavitaillementController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\OrganisateurController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\API\DashboardDonateurController;
use App\Http\Controllers\Api\DashboardOrganisateurController;
use App\Http\Controllers\Api\StructureDashboardController;
use App\Http\Controllers\Api\CampagneStructureController;

// Routes pour l'enregistrement et la connexion (publiques)
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::middleware('auth:api')->get('/user-info', [UserController::class, 'getUserInfo']);

// Route::apiResource('demandes', DemandeRavitaillementController::class);
Route::apiResource('structures', StructureTransfusionSanguinController::class);
Route::middleware('auth:api')->get('/structures-destinataires', [StructureTransfusionSanguinController::class, 'structuresDestinataires']);
    
   
// Routes protégées par authentification
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/refresh-token', [AuthController::class, 'refresh'])->name('refresh'); // Corrigé: refresh au lieu de refreshToken
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    // Route::get('/donateurs/{id}/profil', [DonateurController::class, 'showProfil']);
    Route::get('/donateurs/profil', [DonateurController::class, 'showProfil']);
    Route::get('/donateurs/profil-complet', [DonateurController::class, 'profilComplet']);
// Route::get('/notifications', [NotificationController::class, 'getUserNotifications']);
//     Route::post('/notificationsend', [NotificationController::class, 'sendNotification']);


});
// Route::delete('/desinscrire/{donateur_id}/{campagne_id}', [ParticipationController::class, 'desinscrire']);

// Routes pour les notifications
Route::middleware('auth:api')->group(function () {
      Route::post('/campagnes/{campagne_id}/inscrire', [ParticipationController::class, 'inscrire']);
    Route::get('/mes-inscriptions', [ParticipationController::class, 'mesInscriptions']);
    Route::delete('/campagnes/{campagneId}/desinscription', [ParticipationController::class, 'desinscrireCampagne']);
// Route::delete('/desinscrire/{donateur_id}/{campagne_id}', [ParticipationController::class, 'desinscrire']);
    // Route::delete('/campagnes/desinscrire', [ParticipationController::class, 'desinscrire']);
    Route::get('/inscriptions-organisateur', [ParticipationController::class, 'inscriptionsOrganisateur']);
    Route::middleware('auth:api')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'getUserNotifications']);
    Route::get('/notifications/unread', [NotificationController::class, 'getUnreadCount']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'deleteNotification']);
});

});
// route pour Dashboard Organisateur 
Route::middleware(['auth:api'])->prefix('dashboard-organisateur')->group(function () {
    Route::get('/statistiques', [DashboardOrganisateurController::class, 'statistiquesGenerales']);
    Route::get('/campagnes/actives', [DashboardOrganisateurController::class, 'campagnesActives']);
    Route::get('/campagnes/avenir', [DashboardOrganisateurController::class, 'campagnesAVenir']);
    Route::get('/campagnes/passees', [DashboardOrganisateurController::class, 'campagnesPassees']);
    // Route::get('/demandes/urgentes', [DashboardOrganisateurController::class, 'demandesUrgentes']);
    Route::get('/campagnes-par-mois', [DashboardOrganisateurController::class, 'campagnesParMois']);
    Route::get('/donneurs-par-groupe', [DashboardOrganisateurController::class, 'donneursParGroupe']);
    // Route::get('/dashboard-organisateur/demandes-urgentes', [DashboardOrganisateurController::class, 'demandesUrgentes']);
    Route::get('/taux-participation', [DashboardOrganisateurController::class, 'tauxParticipation']);
    Route::get('/organisateur/demandes', [DashboardOrganisateurController::class, 'demandesParOrganisateurConnecte']);
    Route::get('/campagnes-par-statut', [DashboardOrganisateurController::class, 'repartitionStatutsCampagnes']);
    Route::get('/dons-par-mois', [DashboardOrganisateurController::class, 'donsParMois']);
    Route::get('/dons-par-region', [DashboardOrganisateurController::class, 'donsParRegion']);

});


// Route pour les opérations CRUD sur les banques de sang

Route::middleware(['auth:api'])->group(function () {

Route::apiResource('banques', BanqueDeSangController::class);

 });
 Route::middleware(['auth:api'])->group(function () {
    Route::apiResource('demandes', DemandeRavitaillementController::class);
Route::get('/demandes-recues', [DemandeRavitaillementController::class, 'demandesReçues']);
Route::get('/demandes-envoyees', [DemandeRavitaillementController::class, 'demandesEnvoyees']);
Route::get('/structures-destinataires', [DemandeRavitaillementController::class, 'structuresDestinataires']);
Route::patch('/demandes/{id}/approuver', [DemandeRavitaillementController::class, 'approuver']);
Route::patch('/demandes/{id}/rejeter', [DemandeRavitaillementController::class, 'rejeter']);
// Route::get('/stocks', [BanqueDeSangController::class, 'stocks']);
Route::get('/stocks', [BanqueDeSangController::class, 'stocks']);

// Route::get('/stocks', [DemandeRavitaillementController::class, 'index']);
     });

Route::middleware(['auth:api'])->prefix('structure')->group(function () {
    Route::get('dashboard/campagnes', [StructureDashboardController::class, 'campagnes']);
    Route::get('dashboard/statistiques', [StructureDashboardController::class, 'statistiquesGenerales']);
    Route::get('dashboard/campagnes/actives', [StructureDashboardController::class, 'campagnesActives']);
    Route::get('dashboard/campagnes/avenir', [StructureDashboardController::class, 'campagnesAVenir']);
    Route::get('dashboard/campagnes/passees', [StructureDashboardController::class, 'campagnesPassees']);
    Route::get('dashboard/campagnes/statuts', [StructureDashboardController::class, 'repartitionStatutsCampagnes']);
    Route::get('dashboard/campagnes/mois', [StructureDashboardController::class, 'campagnesParMois']);
    Route::get('dashboard/dons/mois', [StructureDashboardController::class, 'donsParMois']);
    Route::get('dashboard/dons/region', [StructureDashboardController::class, 'donsParRegion']);
    Route::get('dashboard/donneurs/groupesanguin', [StructureDashboardController::class, 'donneursParGroupe']);
    Route::get('dashboard/taux/participation', [StructureDashboardController::class, 'tauxParticipation']);
    Route::get('dashboard/demandes', [StructureDashboardController::class, 'demandesParStructure']);
    Route::get('/demandes-urgentes', [StructureDashboardController::class, 'demandesUrgentes']);

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
// Route pour les regions 
// Route::middleware('auth:api')->group(function(){

// });
Route::apiResource('regions', RegionController::class);
// Route pour les groupe sanguin 
Route::apiResource('groupe-sanguins',GroupeSanguinController::class);

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
    Route::get('/participations/donateur/{id}', [ParticipationController::class, 'getNomparticipant']);
    Route::put('/participations/{participationId}/validation', [ParticipationController::class, 'updateParticipationValidation']);
    // Route pour inscrire un donateur à une campagne
    Route::post('/campagnes/{campagneId}/inscription', [ParticipationController::class, 'inscriptionCampagne']);
    Route::put('/donateurs/{id}/informations-medicales', [ParticipationController::class, 'mettreAJourInformations']);
    Route::get('/donateurs/{id}/eligibilite', [ParticipationController::class, 'verifierEligibilite']);
});


    // Route::apiResource('banques', App\Http\Controllers\Banque_sangController::class);
    
    // Route::get('/demandes-urgentes/{statut}', [DemandeRavitaillementController::class, 'demandesUrgentesParStatut']);


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
    // Route::get('organisateurs/mes-campagnes', [OrganisateurController::class, 'mesCampagnes']);
    Route::post('/campagnes', [CampagneController::class, 'store']);
    // Route::get('/organisateurs/{id}/campagnes', [CampagneController::class, 'getCampagnes']);
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
    // Route::get('/structure/campagnes', [CampagneStructureController::class, 'index']);
    Route::post('/structure/campagnes', [CampagneStructureController::class, 'store']);
    // Route::get('/struture/{id}/campagnes', [CampagneController::class, 'getCampagnesByOrganisateurId']);
    Route::get('/structure/campagnes/{id}', [CampagneStructureController::class, 'show']);
    Route::put('/structure/campagnes/{id}', [CampagneStructureController::class, 'update']);
    Route::delete('/structure/campagnes/{id}', [CampagneStructureController::class, 'destroy']);
    // Route::apiResource('structure', CampagneStructureController::class);
    // Route::get('/structures/organisateur/{id}', [StructureController::class, 'getByOrganisateur']);
Route::get('/campagnes/structure/{id}', [CampagneController::class, 'getCampagnesByStructure']);
    Route::get('/mes-campagnesStructure', [CampagneStructureController::class, 'getMesCampagnesStructure']);



});


