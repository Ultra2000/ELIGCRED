<?php

namespace App\Http\Controllers;

use App\Services\CreditEvaluationService;
use App\Models\Dossier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CreditEvaluationController extends Controller
{
    protected $evaluationService;

    public function __construct(CreditEvaluationService $evaluationService)
    {
        $this->evaluationService = $evaluationService;
    }

    /**
     * Évalue un dossier à partir d'un fichier Excel
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function evaluateFromExcel(Request $request)
    {
        try {
            $request->validate([
                'excel_file' => 'required|file|mimes:xlsx,xls',
                'dossier_id' => 'required|exists:dossiers,id'
            ]);

            // Sauvegarder le fichier Excel
            $path = $request->file('excel_file')->store('dossiers/excel');
            $fullPath = Storage::path($path);

            // Récupérer le dossier
            $dossier = Dossier::findOrFail($request->dossier_id);

            // Évaluer le dossier
            $results = $this->evaluationService->evaluateFromExcel($fullPath);

            // Mettre à jour le dossier avec les résultats
            $this->evaluationService->updateDossierWithEvaluation($dossier, $results);

            // Supprimer le fichier temporaire
            Storage::delete($path);

            return response()->json([
                'success' => true,
                'message' => 'Dossier évalué avec succès',
                'results' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'évaluation du dossier: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'évaluation du dossier: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Évalue un dossier à partir des données de la base de données
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function evaluateFromData(Request $request)
    {
        try {
            $request->validate([
                'dossier_id' => 'required|exists:dossiers,id'
            ]);

            // Récupérer le dossier
            $dossier = Dossier::findOrFail($request->dossier_id);

            // Convertir le dossier en tableau
            $dossierData = $dossier->toArray();

            // Évaluer le dossier
            $results = $this->evaluationService->evaluateFromData($dossierData);

            // Mettre à jour le dossier avec les résultats
            $this->evaluationService->updateDossierWithEvaluation($dossier, $results);

            return response()->json([
                'success' => true,
                'message' => 'Dossier évalué avec succès',
                'results' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'évaluation du dossier: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'évaluation du dossier: ' . $e->getMessage()
            ], 500);
        }
    }
} 