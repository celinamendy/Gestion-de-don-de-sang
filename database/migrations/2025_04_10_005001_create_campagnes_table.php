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
        Schema::create('campagnes', function (Blueprint $table) {
            $table->id();
            $table->string('theme');
            $table->string('description');
            $table->string('lieu');
            $table->string('date_debut');
            $table->string('date_fin');
            $table->string('Heure_debut');
            $table->string('Heure_fin');
            $table->string('participant');
            $table->enum('statut', ['à venir', 'en attente', 'validée','en cours ', 'terminée', 'annulee']);
            $table->foreignId('organisateur_id')->constrained('organisateurs')->onDelete('cascade');
            $table->foreignId('structure_transfusion_sanguin_id')->constrained('structure_transfusion_sanguins')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campagnes');
    }
};
