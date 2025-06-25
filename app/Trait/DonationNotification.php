<?php

namespace App\Trait;

use App\Models\Notification;
use App\Models\User;
use App\Models\StructureTransfusionSanguin;

trait DonationNotification
{
    public function sendNotification(User $user, string $message, string $type): void
    {
        \Log::info('Création notification', [
            'user_id' => $user->id,
            'message' => $message,
            'type' => $type,
        ]);

        $user->notifications()->create([
            'message' => $message,
            'type' => $type,
            'statut' => 'non-lue',
            'created_at' => now(),
        ]);
    }

    /**
     * SUPPRIMÉ : méthodes qui font doublon avec le controller
     * Les méthodes notifyDonateurInscription et notifyOrganisateurInscription
     * sont supprimées car elles sont redéfinies dans le controller
     * et causent des conflits
     */

    /**
     * Méthode générique pour envoyer les notifications d'inscription
     * Peut être utilisée comme fallback si nécessaire
     */
    public function sendInscriptionNotifications($donateur, $campagne): void
    {
        if ($donateur->user) {
            // Cette méthode peut appeler les méthodes du controller si nécessaire
            $this->notifyDonateurFromTrait($donateur->user, $campagne);
        } else {
            \Log::error('Donateur sans user lié', ['donateur_id' => $donateur->id]);
        }

        $this->notifyOrganisateurFromTrait($donateur, $campagne);
    }

    /**
     * Version simplifiée pour le trait (évite les conflits)
     */
    private function notifyDonateurFromTrait(User $userDonateur, $campagne): void
    {
        $message = "Votre inscription à la campagne '{$campagne->theme}' a été confirmée avec succès. " .
                   "Date: {$campagne->date_debut} - Lieu: {$campagne->lieu}";

        \Log::info('Notification pour donateur (depuis trait)', [
            'user_id' => $userDonateur->id,
            'campagne_id' => $campagne->id
        ]);

        $this->sendNotification($userDonateur, $message, 'inscription');
    }

    /**
     * Version simplifiée pour le trait (évite les conflits)
     */
    private function notifyOrganisateurFromTrait($donateur, $campagne): void
    {
        try {
            // Notifier l'organisateur
            if (!empty($campagne->organisateur_id)) {
                $organisateur = User::find($campagne->organisateur_id);

                if ($organisateur) {
                    $message = "Nouvelle inscription à votre campagne '{$campagne->theme}' !" .
                               " Donateur : {$donateur->user->nom} - Email : {$donateur->user->email}";

                    \Log::info('Notification pour organisateur (depuis trait)', [
                        'organisateur_id' => $organisateur->id,
                        'donateur_id' => $donateur->id,
                        'campagne_id' => $campagne->id
                    ]);

                    $this->sendNotification($organisateur, $message, 'nouvelle_inscription');
                }
            }

            // Notifier la structure - VERSION CORRIGÉE
            if (!empty($campagne->structure_transfusion_sanguin_id)) {
                // Récupérer la structure
                $structure = StructureTransfusionSanguin::find($campagne->structure_transfusion_sanguin_id);
                
                if ($structure) {
                    // Essayer de trouver l'utilisateur de la structure
                    // Adaptez cette requête selon votre structure de données
                    $structureUser = User::where('email', $structure->email)->first() ??
                                   User::whereHas('roles', function ($query) {
                                       $query->where('name', 'Structure_transfusion_sanguin');
                                   })->where('structure_id', $structure->id)->first();

                    if ($structureUser) {
                        $message = "Nouvelle inscription à la campagne '{$campagne->theme}' de votre structure ! " .
                                   "Donateur : {$donateur->user->nom}";

                        \Log::info('Notification pour structure (depuis trait)', [
                            'structure_user_id' => $structureUser->id,
                            'donateur_id' => $donateur->id,
                            'campagne_id' => $campagne->id
                        ]);

                        $this->sendNotification($structureUser, $message, 'nouvelle_inscription');
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Erreur notification organisateur (depuis trait)', [
                'donateur_id' => $donateur->id,
                'campagne_id' => $campagne->id,
                'erreur' => $e->getMessage()
            ]);
        }
    }
}