<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->integer('nombre_credits_anterieurs')->nullable();
            $table->decimal('montant_moyen_credits_anterieurs', 15, 2)->nullable();
            $table->string('echeancier_respecte')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropColumn([
                'nombre_credits_anterieurs',
                'montant_moyen_credits_anterieurs',
                'echeancier_respecte'
            ]);
        });
    }
}; 