<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Dossier;

class AppliquerDelaisDossiers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:appliquer-delais-dossiers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dossiers = Dossier::whereIn('statut', ['PROVISOIREMENT_VALIDER', 'AJOURNER'])
            ->whereNotNull('date_dernier_avis')
            ->get();
        $count = 0;
        foreach ($dossiers as $dossier) {
            $ancienStatut = $dossier->statut;
            $dossier->appliquerDelaisDecision();
            if ($dossier->statut !== $ancienStatut) {
                $count++;
            }
        }
        $this->info("$count dossiers mis à jour selon les délais.");
    }
}
