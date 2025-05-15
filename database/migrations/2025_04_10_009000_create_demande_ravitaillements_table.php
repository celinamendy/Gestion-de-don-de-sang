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
    Schema::create('demande_ravitaillements', function (Blueprint $table) {
        $table->id();
        $table->date('date_demande');
        $table->integer('quantite');
        $table->enum('statut', ['en attente', 'approuvée', 'rejetée','urgence'])->default('urgence');

        // Foreign keys pour le demandeur et destinataire
        $table->foreignId('sts_demandeur_id')->constrained('structure_transfusion_sanguins')->onDelete('cascade');
        $table->foreignId('sts_destinataire_id')->nullable()->constrained('structure_transfusion_sanguins')->onDelete('set null');

        // Groupe sanguin si nécessaire (optionnel)
        $table->foreignId('groupe_sanguin_id')->nullable()->constrained('groupe_sanguins')->onDelete('set null');

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demande_ravitaillements', function (Blueprint $table) {
            //
        });
    }
};
