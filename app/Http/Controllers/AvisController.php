<?php

namespace App\Http\Controllers;

use App\Models\Avis;
use Illuminate\Http\Request;

class AvisController extends Controller
{
    public function destroy(Avis $avis)
    {
        // Vérifier que l'utilisateur est bien l'auteur de l'avis
        if (auth()->id() !== $avis->user_id) {
            abort(403);
        }

        // Récupérer le dossier associé à l'avis
        $dossier = $avis->dossier;

        // Supprimer l'avis
        $avis->delete();

        // Mettre à jour le statut du dossier à SOUMIS
        $dossier->update(['statut' => 'SOUMIS']);

        return redirect()->back()->with('success', 'Avis supprimé avec succès. Le dossier est revenu au statut "Soumis".');
    }
} 