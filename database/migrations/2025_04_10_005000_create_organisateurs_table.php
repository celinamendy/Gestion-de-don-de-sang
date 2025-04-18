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
            $table->enum('type_organisation', ['ONG', 'hôpital', 'Pharmacie','ministère','Centre de santé' ,'Laboratoire','autre']);
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
