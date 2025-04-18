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
        Schema::create('donateurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('adresse');
            $table->date('date_naissance');
            $table->enum('sexe', ['M', 'F']);
            $table->foreignId('groupe_sanguin_id')->constrained('groupe_sanguins');
            $table->string('poids');
            $table->enum('antecedent_medicament', ['Aucun', 'Maladie chronique', 'hépathite', 'anémier', 'autre'])->default('Aucun');
            $table->date('date_dernier_don')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donateurs');
    }
};
