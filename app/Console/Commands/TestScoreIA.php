<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Dossier;
use App\Services\CreditEvaluationService;
use Illuminate\Support\Facades\Log;

class TestScoreIA extends Command
{
    protected $signature = 'test:score-ia {dossier_id?}';
    protected $description = 'Teste l\'évaluation IA sur un dossier';

    protected $evaluationService;

    public function __construct(CreditEvaluationService $evaluationService)
    {
        parent::__construct();
        $this->evaluationService = $evaluationService;
    }

    public function handle()
    {
        $dossierId = $this->argument('dossier_id');
        
        if ($dossierId) {
            $dossier = Dossier::find($dossierId);
            if (!$dossier) {
                $this->error("Dossier #$dossierId non trouvé");
                return;
            }
            $this->testDossier($dossier);
        } else {
            $dossiers = Dossier::all();
            foreach ($dossiers as $dossier) {
                $this->testDossier($dossier);
            }
        }
    }

    protected function testDossier(Dossier $dossier)
    {
        $this->info("\nTest du dossier #{$dossier->id} - {$dossier->numero_dossier}");
        try {
            // Appeler le service IA (qui prépare les features correctement)
            $result = $this->evaluationService->evaluateDossier($dossier);

            // Afficher les features réellement envoyées (loguées dans le service)
            $this->info("Features envoyées à l'IA (voir logs Laravel pour le détail complet)");

            // Afficher le résultat complet retourné par l'IA
            $this->info("\nRésultat brut retourné par l'IA :");
            foreach ($result as $key => $value) {
                $this->info("- {$key}: {$value}");
            }

            // Mettre à jour le dossier avec les résultats
            if (isset($result['score'])) $dossier->score_ia = $result['score'];
            if (isset($result['statut'])) $dossier->statut_ia = $result['statut'];
            if (isset($result['montant'])) $dossier->montant_predit = $result['montant'];
            if (isset($result['duree'])) $dossier->duree_predite = $result['duree'];
            $dossier->save();

            $this->info("Dossier mis à jour avec les résultats de l'évaluation IA");
        } catch (\Exception $e) {
            $this->error("Erreur lors de l'évaluation du dossier : " . $e->getMessage());
            Log::error("Erreur d'évaluation IA pour le dossier #{$dossier->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
} 