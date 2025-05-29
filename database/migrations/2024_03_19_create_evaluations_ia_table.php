<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluations_ia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained()->onDelete('cascade');
            $table->decimal('score_ia', 5, 2);
            $table->string('statut_ia');
            $table->decimal('montant_prediction', 15, 2);
            $table->integer('duree_prediction');
            $table->decimal('ratio_endettement', 5, 2);
            $table->decimal('ratio_garanties', 5, 2);
            $table->decimal('ratio_rentabilite', 5, 2);
            $table->json('features_utilisees');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations_ia');
    }
}; 