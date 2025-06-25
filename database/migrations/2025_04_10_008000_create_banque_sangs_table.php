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
        Schema::create('banque_sangs', function (Blueprint $table) {
            $table->id();
            $table->integer('nombre_poche');
            $table->integer('stock_actuelle');
            $table->date('date_mise_a_jour');
            $table->string('date_expiration');
            $table->string('heure_expiration');
            $table->string('date_dernier_stock');
            $table->string('date_dernier_approvisionnement');
            $table->string('date_dernier_rapprochement');
            $table->enum('statut', ['urgence', 'intervention','disponible','indisponible'])->default('urgence');
            $table->foreignId('groupe_sanguin_id')->constrained('groupe_sanguins');
            $table->foreignId('structure_transfusion_sanguin_id')->constrained('structure_transfusion_sanguins');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banque_sangs');
    }
};
