<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Dossier;
use App\Http\Controllers\CreditEvaluationController;

// Routes pour l'évaluation des dossiers de crédit
Route::prefix('credit-evaluation')->group(function () {
    Route::post('/evaluate-excel', [CreditEvaluationController::class, 'evaluateFromExcel']);
    Route::post('/evaluate-data', [CreditEvaluationController::class, 'evaluateFromData']);
});

Route::get('/dossiers', function () {
    return Dossier::all();
}); 