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
        Schema::create('dossiers', function (Blueprint $table) {
            $table->id();
            $table->string('numero_dossier')->unique();
            $table->string('nom_client');
            $table->string('prenom_client');
            $table->decimal('montant_sollicite', 15, 2);
            $table->integer('duree_sollicitee');
            $table->string('periodicite_sollicitee');
            $table->decimal('score_ia', 5, 2);
            $table->decimal('montant_propose', 15, 2)->nullable();
            $table->integer('duree_proposee')->nullable();
            $table->string('periodicite_proposee')->nullable();
            $table->decimal('montant_accorde', 15, 2)->nullable();
            $table->integer('duree_accordee')->nullable();
            $table->string('periodicite_accordee')->nullable();
            $table->string('statut')->default('CHARGE');
            $table->string('fichier_excel_path');
            $table->string('fichier_pdf_path')->nullable();
            $table->text('observations')->nullable();
            $table->timestamp('date_dernier_avis')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dossiers');
    }
};
