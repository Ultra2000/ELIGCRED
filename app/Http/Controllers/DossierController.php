<?php

namespace App\Http\Controllers;

use App\Services\CreditEvaluationService;

class DossierController extends Controller
{
    protected $evaluationService;

    public function __construct(CreditEvaluationService $evaluationService)
    {
        $this->evaluationService = $evaluationService;
    }

    public function evaluate(Dossier $dossier)
    {
        try {
            $result = $this->evaluationService->evaluateDossier($dossier);
            
            return response()->json([
                'success' => true,
                'message' => 'Évaluation effectuée avec succès',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'évaluation : ' . $e->getMessage()
            ], 500);
        }
    }
} 