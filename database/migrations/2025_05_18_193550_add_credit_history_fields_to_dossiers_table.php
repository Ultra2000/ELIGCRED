<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->integer('nombre_credits_anterieurs')->default(0);
            $table->integer('nombre_credits_en_cours')->default(0);
            $table->decimal('montant_total_credits', 15, 2)->default(0);
            $table->decimal('montant_mensuel_credits', 15, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropColumn([
                'nombre_credits_anterieurs',
                'nombre_credits_en_cours',
                'montant_total_credits',
                'montant_mensuel_credits'
            ]);
        });
    }
}; 