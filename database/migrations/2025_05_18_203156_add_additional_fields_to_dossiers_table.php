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
        Schema::table('dossiers', function (Blueprint $table) {
            // Informations générales
            $table->string('guichet')->nullable();
            $table->string('code_adherent')->nullable();
            $table->dateTime('date_prise_info')->nullable();
            $table->boolean('renouvellement')->default(false);
            $table->string('activite_financer')->nullable();

            // Informations de l'emprunteur
            $table->string('sexe')->nullable();
            $table->dateTime('date_naissance')->nullable();
            $table->string('lieu_naissance')->nullable();
            $table->string('adresse')->nullable();
            $table->integer('residence_depuis')->nullable();
            $table->string('metier')->nullable();
            $table->string('activites')->nullable();
            $table->string('numero_ifu')->nullable();
            $table->string('situation_matrimoniale')->nullable();
            $table->integer('personnes_charge')->nullable();
            $table->string('reference')->nullable();

            // Informations sur l'entreprise
            $table->string('adresse_entreprise')->nullable();
            $table->string('niveau_concurrence')->nullable();

            // État des produits et charges
            $table->decimal('salaire_net', 15, 2)->nullable();
            $table->decimal('total_revenus', 15, 2)->nullable();
            $table->decimal('total_depenses', 15, 2)->nullable();
            $table->decimal('actif_net', 15, 2)->nullable();

            // Historique des crédits
            $table->decimal('total_credits', 15, 2)->nullable();

            // Évaluation du besoin
            $table->decimal('cout_total_projet', 15, 2)->nullable();
            $table->decimal('besoin_financement', 15, 2)->nullable();

            // Garanties
            $table->decimal('total_garanties', 15, 2)->nullable();

            // Bilan de l'entreprise
            $table->decimal('encaisse', 15, 2)->nullable();
            $table->decimal('total_actif', 15, 2)->nullable();
            $table->decimal('total_passif', 15, 2)->nullable();

            // Compte d'exploitation
            $table->decimal('vente', 15, 2)->nullable();
            $table->decimal('surplus_net', 15, 2)->nullable();

            // Avis et décisions
            $table->string('avis_ac')->nullable();
            $table->decimal('montant_approuve_ac', 15, 2)->nullable();
            $table->string('avis_ctc1')->nullable();
            $table->decimal('montant_approuve_ctc1', 15, 2)->nullable();
            $table->string('avis_ctc2')->nullable();
            $table->decimal('montant_approuve_ctc2', 15, 2)->nullable();

            // Commentaires
            $table->text('commentaires_ac')->nullable();
            $table->text('commentaires_ctc1')->nullable();
            $table->text('commentaires_ctc2')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            // Informations générales
            $table->dropColumn([
                'guichet',
                'code_adherent',
                'date_prise_info',
                'renouvellement',
                'activite_financer',
                'sexe',
                'date_naissance',
                'lieu_naissance',
                'adresse',
                'residence_depuis',
                'metier',
                'activites',
                'numero_ifu',
                'situation_matrimoniale',
                'personnes_charge',
                'reference',
                'adresse_entreprise',
                'niveau_concurrence',
                'salaire_net',
                'total_revenus',
                'total_depenses',
                'actif_net',
                'total_credits',
                'cout_total_projet',
                'besoin_financement',
                'total_garanties',
                'encaisse',
                'total_actif',
                'total_passif',
                'vente',
                'surplus_net',
                'avis_ac',
                'montant_approuve_ac',
                'avis_ctc1',
                'montant_approuve_ctc1',
                'avis_ctc2',
                'montant_approuve_ctc2',
                'commentaires_ac',
                'commentaires_ctc1',
                'commentaires_ctc2',
            ]);
        });
    }
};
