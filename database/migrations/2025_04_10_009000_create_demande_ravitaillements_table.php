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
        
            $table->unsignedBigInteger('structure_transfusion_sanguin_id');
            // $table->unsignedBigInteger('structure_transfusion_sang_destinataire_id');
        
            // Foreign key avec noms plus courts
            // $table->foreign('structure_transfusion_sang_id')
            //     ->references('id')->on('structure_transfusion_sang')
            //     ->onDelete('cascade');
        
            
        
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demande_ravitaillements');
    }
};
