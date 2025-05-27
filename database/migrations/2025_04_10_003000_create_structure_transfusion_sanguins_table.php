<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('structure_transfusion_sanguins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nom_responsable');
            $table->enum('type_entite', [
            'Hôpital','Hôpital public','Hôpital militaire', 'Hôpital privé', 'Clinique privée', 'Centre hospitalier universitaire (CHU)', 'Centre de santé communautaire',
            'Centre de santé intégré','Poste de santé','Dispensaire','Centre national de transfusion sanguine (CNTS)','Centre régional de transfusion sanguine (CRTS)',
            'Centre de transfusion sanguine mobile','Banque de sang hospitalière','Banque de sang indépendante',
            'Centre de collecte de sang','Centre de préparation de produits sanguins','Centre de conservation de sang','Centre de distribution de produits sanguins',
            'Laboratoire d’hématologie','Laboratoire de biologie médicale','Institut national de santé publique',
            'Institut de formation en santé','Université ou faculté de médecine','ONG médicale',
            'Croix-Rouge / Croissant-Rouge','Association de donneurs de sang bénévoles','Fondation médicale',
            'Organisme international','Ministère de la Santé','Agence nationale de sécurité transfusionnelle',
            'Direction régionale de la santé','Inspection médicale départementale','Pharmacie hospitalière','Maison de santé',
            'Centre médico-social','Clinique universitaire','Infirmerie scolaire ou industrielle',
            'Unité mobile de collecte','Autre']);
            $table->string('adresse');
            $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('structure_transfusion_sanguins');
    }
};
