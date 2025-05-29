<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::delete('/avis/{avis}', [App\Http\Controllers\AvisController::class, 'destroy'])->name('avis.destroy');

Route::post('/dossiers/{dossier}/evaluate', [DossierController::class, 'evaluate'])->name('dossiers.evaluate');
