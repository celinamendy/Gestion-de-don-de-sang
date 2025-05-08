
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
    Schema::create('participations', function (Blueprint $table) {
        $table->id();
        $table->foreignId('donateur_id')->constrained('donateurs')->onDelete('cascade');
        $table->foreignId('campagne_id')->constrained('campagnes')->onDelete('cascade');
        $table->enum('statut', ['en attente', 'acceptée', 'refusée']);
        $table->integer('quantite')->default(1); // nombre de poches ou ml
        $table->date('date_participation')->nullable();
        $table->string('lieu_participation')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participations');
    }
};
