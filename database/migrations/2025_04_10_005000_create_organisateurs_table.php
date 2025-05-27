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
        Schema::create('organisateurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nom_responsable');
            $table->string('adresse');
            $table->enum('type_organisation', ['Organisation non gouvernementale (ONG)','ONG médicale','Association humanitaire','Croix-Rouge / Croissant-Rouge',
            'Fondation médicale','Organisme international','Hôpital public','Hôpital privé',
            'Clinique','Centre de santé','Poste de santé','Pharmacie','Laboratoire',
            'Ministère de la Santé','Agence gouvernementale','Direction régionale de la santé',
            'Inspection médicale','Université / Faculté de médecine','Institut de recherche médicale',
            'Entreprise privée (RSE)','Collectivité territoriale','Organisation communautaire',
            'Centre médico-social','Service de santé scolaire','Service de santé militaire','Autre']);
            $table->foreignId('structure_transfusion_sanguin_id')->constrained('structure_transfusion_sanguins');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisateurs');
    }
};
