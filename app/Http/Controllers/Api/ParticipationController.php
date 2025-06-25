<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Trait\DonationNotification;
use App\Models\Campagne;
use Carbon\Carbon;
use App\Models\Donateur;
use App\Models\Participation;
use App\Models\StructureTransfusionSanguin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ParticipationController extends Controller
{
    use DonationNotification;

    /**
     * Inscription à une campagne avec notifications automatiques
     */
    public function inscriptionCampagne(Request $request, $campagneId)
    {
        try {
            DB::beginTransaction();

            $donateur = Auth::user()->donateur;
            
            if (!$donateur) {
                return response()->json([
                    'status' => false,
                    'message' => 'Aucun donateur lié à cet utilisateur.'
                ], 404);
            }

            // Vérifier que le donateur a un utilisateur associé
            if (!$donateur->user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Aucun utilisateur associé à ce donateur.'
                ], 404);
            }
            
            $campagne = Campagne::find($campagneId);
            
            if (!$campagne) {
                return response()->json([
                    'status' => false,
                    'message' => "Campagne non trouvée."
                ], 404);
            }

            // Vérification de l'éligibilité
            $eligibilite = $this->verifierEligibilite($donateur->id);
            
            if (!$eligibilite['est_eligible']) {
                return response()->json([
                    'status' => false,
                    'message' => 'Vous ne pouvez pas vous inscrire à cette campagne car vous n\'êtes pas éligible.',
                    'problemes' => $eligibilite['problemes']
                ], 403);
            }

            // Vérification de l'inscription existante
            $already = Participation::where('donateur_id', $donateur->id)
                ->where('campagne_id', $campagneId)
                ->exists();

            if ($already) {
                return response()->json([
                    'status' => false,
                    'message' => 'Vous êtes déjà inscrit à cette campagne.'
                ], 409);
            }

            // Vérification de la date de fin de la campagne
            $date_fin = Carbon::parse($campagne->date_fin . ' ' . $campagne->Heure_fin);
            $dateActuelle = Carbon::now();

            if ($dateActuelle->greaterThan($date_fin)) {
                return response()->json([
                    'status' => false,
                    'message' => 'La campagne est déjà terminée.'
                ], 400);
            }

            // Création de la participation
            $participation = Participation::create([
                'donateur_id' => $donateur->id,
                'campagne_id' => $campagne->id,
                'statut' => 'en attente',
            ]);

            // CORRECTION : Envoi des notifications automatiques
            try {
                $this->envoyerNotificationsInscription($donateur, $campagne);
            } catch (\Exception $notificationError) {
                // Log l'erreur mais ne pas faire échouer l'inscription
                Log::warning('Erreur envoi notifications inscription', [
                    'donateur_id' => $donateur->id,
                    'campagne_id' => $campagne->id,
                    'erreur' => $notificationError->getMessage()
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Inscription réussie à la campagne.",
                'data' => $participation,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erreur inscription campagne', [
                'campagne_id' => $campagneId,
                'user_id' => Auth::id(),
                'erreur' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
                    
            return response()->json([
                'status' => false,
                'message' => 'Erreur lors de l\'inscription : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * CORRECTION : Envoie les notifications d'inscription
     */
    private function envoyerNotificationsInscription($donateur, $campagne)
    {
        // Vérifier que le donateur a un utilisateur associé
        if (!$donateur->user) {
            throw new \Exception('Aucun utilisateur associé à ce donateur pour les notifications.');
        }

        // 1. Notifier le donateur de son inscription
        $this->notifierDonateurInscription($donateur, $campagne);

        // 2. Notifier l'organisateur/structure selon le type de campagne
        $this->notifierOrganisateurInscription($donateur, $campagne);
    }

    /**
     * CORRECTION : Notification du donateur - FIX de la variable $donateur
     */
    private function notifierDonateurInscription($donateur, $campagne)
    {
        try {
            $userDonateur = $donateur->user;
            
            if (!$userDonateur) {
                throw new \Exception('Aucun utilisateur associé à ce donateur.');
            }

            $message = "Félicitations ! Votre inscription à la campagne '{$campagne->theme}' a été confirmée avec succès. " .
                      "📅 Date: " . Carbon::parse($campagne->date_debut)->format('d/m/Y') . 
                      " 📍 Lieu: {$campagne->lieu}. " .
                      "Merci pour votre engagement solidaire ! ❤️";

            Log::info('Debug user lié au donateur', [
                'donateur_id' => $donateur->id,
                'user_existe' => $donateur->relationLoaded('user') ? 'chargé' : 'non chargé',
                'user_id' => $donateur->user?->id
            ]);

            $this->sendNotification($userDonateur, $message, 'inscription');
        } catch (\Exception $e) {
            Log::error('Erreur notification donateur inscription', [
                'donateur_id' => $donateur->id,
                'campagne_id' => $campagne->id,
                'erreur' => $e->getMessage()
            ]);
            throw $e;
        }
    }
 
    //function pour se desinscrire a une campagnes apres inscription 
public function desinscrire(Request $request)
{
    $donateurId = $request->input('donateur_id'); // snake_case
    $campagneId = $request->input('campagne_id');

    $participation = Participation::where('donateur_id', $donateurId)
                                  ->where('campagne_id', $campagneId)
                                  ->first();

    if (!$participation) {
        return response()->json([
            'status' => false,
            'message' => 'Inscription non trouvée.'
        ], 404);
    }

    $participation->delete();

    return response()->json([
        'status' => true,
        'message' => 'Désinscription réussie.'
    ]);
}

    /**
     * CORRECTION : Notification des organisateurs/structures - FIX de la requête SQL
     */
    private function notifierOrganisateurInscription($donateur, $campagne)
    {
        try {
            $userDonateur = $donateur->user;
            
            if (!$userDonateur) {
                throw new \Exception('Aucun utilisateur associé à ce donateur.');
            }

            // Notifier l'organisateur si c'est une campagne d'organisateur
            if ($campagne->organisateur_id) {
                $organisateur = User::find($campagne->organisateur_id);
                
                if ($organisateur) {
                    $message = "🎉 Nouvelle inscription à votre campagne '{$campagne->theme}' ! " .
                              "👤 Donateur: {$userDonateur->nom} {$userDonateur->prenom} " .
                              "📧 Email: {$userDonateur->email}";
                    
                    $this->sendNotification($organisateur, $message, 'nouvelle_inscription');
                }
            }

            // CORRECTION : Fix de la requête pour la structure
            if ($campagne->structure_transfusion_sanguin_id) {
                // Option 1: Si les users ont un champ structure_transfusion_sanguin_id
                // $structure = User::where('structure_transfusion_sanguin_id', $campagne->structure_transfusion_sanguin_id)->first();
                
                // Option 2: Si il faut passer par une relation avec la table structures
                $structureTransfusion = StructureTransfusionSanguin::find($campagne->structure_transfusion_sanguin_id);
                
                if ($structureTransfusion) {
                    // Supposons que vous avez une relation user() dans le modèle StructureTransfusionSanguin
                    // Ajustez selon votre structure de données
                    $structureUser = $structureTransfusion->user ?? 
                                   User::where('email', $structureTransfusion->email)->first() ??
                                   User::whereHas('roles', function($query) {
                                       $query->where('name', 'Structure_transfusion_sanguin');
                                   })->where('structure_id', $campagne->structure_transfusion_sanguin_id)->first();
                    
                    if ($structureUser) {
                        $message = "🏥 Nouvelle inscription à la campagne '{$campagne->theme}' de votre structure ! " .
                                  "👤 Donateur: {$userDonateur->nom} {$userDonateur->prenom} " .
                                  "📧 Contact: {$userDonateur->email}";
                        
                        $this->sendNotification($structureUser, $message, 'nouvelle_inscription');
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Erreur notification organisateur inscription', [
                'donateur_id' => $donateur->id,
                'campagne_id' => $campagne->id,
                'erreur' => $e->getMessage()
            ]);
            // Ne pas relancer l'exception pour ne pas faire échouer l'inscription
        }
    }

    /**
     * Vérifier l'éligibilité du donateur à une campagne.
     */
    public function verifierEligibilite($donateurId)
    {
        $donateur = Donateur::findOrFail($donateurId);
        $problemes = [];

        // Chemin complet vers le fichier JSON
        $cheminFichierEligibilite = storage_path('app/eligibilite.json');

        // Charger les règles d'éligibilité depuis le fichier JSON
        try {
            if (!file_exists($cheminFichierEligibilite)) {
                throw new \Exception('Le fichier de règles d\'éligibilité n\'existe pas.');
            }

            $contenuJson = file_get_contents($cheminFichierEligibilite);
            $regles = json_decode($contenuJson, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Erreur de parsing du fichier JSON : ' . json_last_error_msg());
            }

            if (empty($regles)) {
                throw new \Exception('Les règles d\'éligibilité sont vides.');
            }
        } catch (\Exception $e) {
            Log::error('Erreur de chargement des règles d\'éligibilité : ' . $e->getMessage());

            return [
                'est_eligible' => false,
                'problemes' => ['Erreur système dans la vérification d\'éligibilité.']
            ];
        }

        // Normaliser le sexe
        $sexe = strtolower($donateur->sexe);
        if ($sexe === 'h' || $sexe === 'm') {
            $sexe = 'homme';
        } elseif ($sexe === 'f') {
            $sexe = 'femme';
        }

        // Vérification du sexe
        if (!isset($regles[$sexe])) {
            return [
                'est_eligible' => false,
                'problemes' => ["Sexe non reconnu dans les règles d'éligibilité. Vous devez être du sexe 'femme' ou 'homme'."]
            ];
        }

        $regleSexe = $regles[$sexe];

        // Vérification de l'âge
        if (!$donateur->date_naissance) {
            $problemes[] = "Date de naissance manquante. Veuillez compléter votre profil.";
        } else {
            $age = Carbon::parse($donateur->date_naissance)->age;
            if ($age < $regleSexe['age']['min'] || $age > $regleSexe['age']['max']) {
                $problemes[] = "Votre âge doit être compris entre {$regleSexe['age']['min']} et {$regleSexe['age']['max']} ans pour être éligible à cette campagne.";
            }
        }

        // Vérification du poids
        if ($donateur->poids < $regleSexe['poids_min']) {
            $problemes[] = "Le poids doit être supérieur ou égal à {$regleSexe['poids_min']} kg pour que vous soyez reconnu(e) comme éligible à cette campagne.";
        }

        // Vérification de l'intervalle depuis le dernier don
        if ($donateur->date_dernier_don) {
            $dernierDon = Carbon::parse($donateur->date_dernier_don);
            $maintenant = Carbon::now();

            $jours = $dernierDon->diffInDays($maintenant);
            if ($jours < $regleSexe['intervalle_don_jours']) {
                $problemes[] = "Il faut attendre au moins {$regleSexe['intervalle_don_jours']} jours entre deux dons pour pouvoir faire un don.";
            }
        }

        return [
            'est_eligible' => empty($problemes),
            'problemes' => $problemes,
        ];
    }

    /**
     * Récupérer toutes les campagnes auxquelles le donateur connecté est inscrit.
     */
    public function historiquecampagnes()
    {
        $donateur = Auth::user()->donateur;

        if (!$donateur) {
            return response()->json([
                'status' => false,
                'message' => 'Aucun donateur lié à cet utilisateur.'
            ], 404);
        }

        $campagnes = Participation::with('campagne')
            ->where('donateur_id', $donateur->id)
            ->get()
            ->pluck('campagne')
            ->unique('id')
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Campagnes récupérées avec succès',
            'data' => $campagnes,
        ], 200);
    }
  

    /**
     * Mettre à jour les informations du donateur
     */
    public function mettreAJourInformations(Request $request, $id)
    {
        $donateur = Donateur::findOrFail($id);

        $request->validate([
            'poids' => 'required|numeric|min:30|max:200',
            'sexe' => 'required|in:M,F',
            'date_dernier_don' => 'nullable|date',
        ]);

        $donateur->update([
            'poids' => $request->poids,
            'sexe' => $request->sexe,
            'date_dernier_don' => $request->date_dernier_don,
        ]);

        return response()->json([
            'message' => 'Informations mises à jour avec succès.',
            'donateur' => $donateur
        ]);
    }

    /**
     * Récupérer les donateurs inscrits à une campagne spécifique
     */
    public function donateursDeMaCampagne($campagneId)
    {
        $user = Auth::user();

        if (!$user->hasAnyRole(['Organisateur', 'Structure_transfusion_sanguin'])) {
            return response()->json([
                'message' => 'Vous n\'êtes pas autorisé à consulter la liste des donateurs.'
            ], 403);
        }

        $campagne = Campagne::with([
            'participations.donateur.user',
            'participations.donateur.groupeSanguin'
        ])->find($campagneId);

        if (!$campagne || 
            ($user->hasRole('Organisateur') && $campagne->organisateur_id !== $user->id) ||
            ($user->hasRole('Structure_transfusion_sanguin') && $campagne->structure_transfusion_sanguin_id !== $user->id)) {
            return response()->json([
                'status' => false,
                'message' => 'Campagne non trouvée ou non autorisée.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Campagne avec participations et donateurs.',
            'data' => $campagne
        ]);
    }

    /**
     * Récupérer le nom d'un participant
     */
    public function getNomparticipant($id)
    {
        $donateur = Donateur::findOrFail($id);

        if (!$donateur->user) {
            return response()->json([
                'status' => false,
                'message' => 'Aucun utilisateur associé à ce donateur.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Nom du participant récupéré avec succès.',
            'data' => [
                'nom' => $donateur->user->nom,
            ]
        ]);
    }

    /**
     * Mettre à jour la validation d'une participation avec notification
     */
    public function updateParticipationValidation(Request $request, $participationId)
    {
        try {
            DB::beginTransaction();

            // Validation des données d'entrée
            $validatedData = $request->validate([
                'participation_validee' => 'required|boolean',
                'statut' => 'required|string|in:En attente,Validé,Refusé',
            ]);

            // Récupérer la participation avec ses relations
            $participation = Participation::with(['donateur.user', 'campagne'])->find($participationId);

            if (!$participation) {
                return response()->json([
                    'status' => false,
                    'message' => 'Participation non trouvée'
                ], 404);
            }

            // Vérification des rôles
            $user = auth()->user();
            if (!in_array($user->roles[0] ?? '', ['Organisateur', 'Structure_transfusion_sanguin'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Accès non autorisé'
                ], 403);
            }

            // Données à mettre à jour
            $updateData = [
                'statut' => $validatedData['statut'],
                'participation_validee' => $validatedData['participation_validee'],
            ];

            // Si la participation est validée, ajouter la date
            if ($validatedData['statut'] === 'Validé') {
                $updateData['date_participation'] = now();
            }

            // Sauvegarder l'ancien statut pour comparaison
            $ancienStatut = $participation->statut;

            // Mise à jour de la participation
            $participation->update($updateData);

            // Notification du changement de statut
            if ($ancienStatut !== $validatedData['statut']) {
                try {
                    $this->notifierChangementStatut($participation, $validatedData['statut']);
                } catch (\Exception $notificationError) {
                    Log::warning('Erreur envoi notification changement statut', [
                        'participation_id' => $participationId,
                        'erreur' => $notificationError->getMessage()
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Participation mise à jour avec succès.',
                'data' => $participation
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erreur mise à jour participation', [
                'participation_id' => $participationId,
                'erreur' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => false,
                'message' => 'Erreur serveur : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Notifier le changement de statut de participation
     */
    private function notifierChangementStatut($participation, $nouveauStatut)
    {
        $donateur = $participation->donateur->user;
        $campagne = $participation->campagne;

        switch ($nouveauStatut) {
            case 'Validé':
                $message = "✅ Excellente nouvelle ! Votre participation à la campagne '{$campagne->nom}' a été validée. " .
                          "Rendez-vous le " . Carbon::parse($campagne->date_debut)->format('d/m/Y') . 
                          " à {$campagne->lieu}. Merci pour votre générosité ! 🩸❤️";
                $this->sendNotification($donateur, $message, 'validation');
                break;

            case 'Refusé':
                $message = "❌ Nous regrettons de vous informer que votre participation à la campagne '{$campagne->nom}' a été refusée. " .
                          "N'hésitez pas à vous inscrire à d'autres campagnes. Merci pour votre compréhension.";
                $this->sendNotification($donateur, $message, 'refus');
                break;

            case 'En attente':
                $message = "⏳ Votre participation à la campagne '{$campagne->theme}' est en cours d'examen. " .
                          "Nous vous tiendrons informé(e) dès que possible.";
                $this->sendNotification($donateur, $message, 'attente');
                break;
        }
    }
}