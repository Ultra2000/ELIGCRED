<?php

namespace App\Services;

use App\Models\Dossier;
use App\Models\EvaluationIA;
use Illuminate\Support\Facades\Log;

class CreditEvaluationService
{
    public function evaluateDossier(Dossier $dossier)
    {
        try {
            // Préparer les features
            $features = $this->prepareFeatures($dossier);
            
            // Log des features pour debug
            Log::info('Features préparées : ' . print_r($features, true));
            
            // Appeler le script Python pour la prédiction
            $result = $this->callPythonScript($features);
            
            if (!$result['success']) {
                throw new \Exception('Erreur Python: ' . ($result['error'] ?? 'Erreur inconnue'));
            }
            
            // Log du résultat pour debug
            Log::info('Résultat de la prédiction : ' . print_r($result, true));
            
            // Calculer les ratios
            $ratioEndettement = $dossier->total_revenus > 0 
                ? ($dossier->total_depenses / $dossier->total_revenus * 100)
                : 0;
                
            $ratioGaranties = $dossier->besoin_financement > 0 
                ? ($dossier->total_garanties / $dossier->besoin_financement * 100)
                : 0;
                
            $ratioRentabilite = $dossier->vente > 0 
                ? ($dossier->surplus_net / $dossier->vente * 100)
                : 0;
            
            // Créer ou mettre à jour l'enregistrement d'évaluation
            $evaluation = EvaluationIA::updateOrCreate(
                ['dossier_id' => $dossier->id],
                [
                    'score_ia' => $result['score'],
                    'statut_ia' => $result['prediction'] ? 'FAVORABLE' : 'NON_FAVORABLE',
                    'montant_prediction' => $result['montant'],
                    'duree_prediction' => $result['duree'],
                    'ratio_endettement' => $ratioEndettement,
                    'ratio_garanties' => $ratioGaranties,
                    'ratio_rentabilite' => $ratioRentabilite,
                    'features_utilisees' => $features
                ]
            );
            
            // Mettre à jour le dossier
            $dossier->update([
                'score_ia' => $result['score'],
                'statut_ia' => $result['prediction'] ? 'FAVORABLE' : 'NON_FAVORABLE',
                'montant_predit' => $result['montant'],
                'duree_predite' => $result['duree']
            ]);
            
            return [
                'score' => $result['score'],
                'statut' => $result['prediction'] ? 'FAVORABLE' : 'NON_FAVORABLE',
                'montant' => $result['montant'],
                'duree' => $result['duree']
            ];
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'évaluation du dossier : ' . $e->getMessage());
            throw new \Exception('Erreur lors de l\'évaluation du dossier');
        }
    }

    private function callPythonScript(array $features)
    {
        $scriptPath = base_path('app/Services/predict_dossier.py');
        
        // Vérifier que le script existe
        if (!file_exists($scriptPath)) {
            throw new \Exception('Script Python non trouvé');
        }
        
        // Préparer la commande
        $command = sprintf('python3 %s', escapeshellarg($scriptPath));
        
        // Créer un processus
        $descriptorspec = [
            0 => ['pipe', 'r'],  // stdin
            1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w']   // stderr
        ];
        
        $process = proc_open($command, $descriptorspec, $pipes);
        
        if (!is_resource($process)) {
            throw new \Exception('Impossible de lancer le script Python');
        }
        
        // Écrire les features en JSON dans stdin
        fwrite($pipes[0], json_encode($features));
        fclose($pipes[0]);
        
        // Lire la sortie
        $output = stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);
        
        // Fermer les pipes
        fclose($pipes[1]);
        fclose($pipes[2]);
        
        // Fermer le processus
        $return_value = proc_close($process);
        
        if ($return_value !== 0) {
            throw new \Exception('Erreur Python: ' . $error);
        }
        
        return json_decode($output, true);
    }

    private function prepareFeatures(Dossier $dossier)
    {
        // Calculer l'âge à partir de la date de naissance
        $age = 0;
        if ($dossier->date_naissance) {
            try {
                $dateNaissance = \Carbon\Carbon::parse($dossier->date_naissance);
                $age = $dateNaissance->age;
                if ($age < 0 || !is_numeric($age)) {
                    $age = 0;
                }
            } catch (\Exception $e) {
                $age = 0;
            }
        }

        // Convertir le sexe en binaire (0 = F, 1 = M)
        $sexe = 0;
        if (strtoupper(trim($dossier->sexe)) === 'M') {
            $sexe = 1;
        }

        // Helper pour forcer une valeur numérique
        $num = function($v) {
            return (is_numeric($v) && $v !== null) ? $v + 0 : 0;
        };

        // Calculer les ratios avec vérification de division par zéro
        $ratioEndettement = $num($dossier->total_revenus) > 0 
            ? ($num($dossier->total_depenses) / $num($dossier->total_revenus) * 100)
            : 0;
        $ratioGaranties = $num($dossier->besoin_financement) > 0 
            ? ($num($dossier->total_garanties) / $num($dossier->besoin_financement) * 100)
            : 0;
        $ratioRentabilite = $num($dossier->vente) > 0 
            ? ($num($dossier->surplus_net) / $num($dossier->vente) * 100)
            : 0;

        return [
            'age' => $age,
            'sexe' => $sexe,
            'personnes_charge' => $num($dossier->personnes_charge),
            'residence_depuis' => $num($dossier->residence_depuis),
            'salaire_net' => $num($dossier->salaire_net),
            'total_revenus' => $num($dossier->total_revenus),
            'total_depenses' => $num($dossier->total_depenses),
            'actif_net' => $num($dossier->actif_net),
            'total_credits' => $num($dossier->total_credits),
            'cout_total_projet' => $num($dossier->cout_total_projet),
            'besoin_financement' => $num($dossier->besoin_financement),
            'total_garanties' => $num($dossier->total_garanties),
            'encaisse' => $this->ensureNumeric($dossier->encaisse),
            'total_actif' => $this->ensureNumeric($dossier->total_actif),
            'total_passif' => $this->ensureNumeric($dossier->total_passif),
            'vente' => $num($dossier->vente),
            'surplus_net' => $num($dossier->surplus_net),
            'nombre_credits_anterieurs' => $num($dossier->nombre_credits_anterieurs),
            'montant_moyen_credits_anterieurs' => $num($dossier->montant_moyen_credits_anterieurs),
            'echeancier_respecte' => $num($dossier->echeancier_respecte),
            // Les ratios ne sont pas envoyés, ils sont recalculés côté Python si besoin
        ];
    }

    private function ensureNumeric($value)
    {
        if (is_numeric($value)) {
            return $value + 0;
        }
        return 0;
    }
} 