<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Dossier extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_dossier',
        'nom_client',
        'prenom_client',
        'montant_sollicite',
        'duree_sollicitee',
        'periodicite_sollicitee',
        'fichier_excel_path',
        'score_ia',
        'montant_propose',
        'duree_proposee',
        'periodicite_proposee',
        'statut',
        // Informations générales
        'guichet',
        'code_adherent',
        'date_prise_info',
        'renouvellement',
        'activite_financer',
        // Informations de l'emprunteur
        'sexe',
        'date_naissance',
        'lieu_naissance',
        'adresse',
        'residence_depuis',
        'metier',
        'activites',
        'numero_ifu',
        'situation_matrimoniale',
        'personnes_charge',
        'reference',
        // Informations sur l'entreprise
        'adresse_entreprise',
        'niveau_concurrence',
        // État des produits et charges
        'salaire_net',
        'total_revenus',
        'total_depenses',
        'actif_net',
        // Historique des crédits
        'total_credits',
        'nombre_credits_anterieurs',
        'montant_moyen_credits_anterieurs',
        'echeancier_respecte',
        // Évaluation du besoin
        'cout_total_projet',
        'besoin_financement',
        // Garanties
        'total_garanties',
        // Bilan de l'entreprise
        'encaisse',
        'total_actif',
        'total_passif',
        // Compte d'exploitation
        'vente',
        'surplus_net',
        // Avis et décisions
        'avis_ac',
        'montant_approuve_ac',
        'avis_ctc1',
        'montant_approuve_ctc1',
        'avis_ctc2',
        'montant_approuve_ctc2',
        // Commentaires
        'commentaires_ac',
        'commentaires_ctc1',
        'commentaires_ctc2',
        'statut_ia',
        'montant_predit',
        'duree_predite'
    ];

    protected $casts = [
        'montant_sollicite' => 'decimal:2',
        'score_ia' => 'decimal:2',
        'montant_propose' => 'decimal:2',
        'montant_accorde' => 'decimal:2',
        'date_dernier_avis' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($dossier) {
            // Supprimer le fichier Excel associé
            if ($dossier->fichier_excel_path) {
                Storage::disk('public')->delete($dossier->fichier_excel_path);
            }
        });
    }

    public function avis()
    {
        return $this->hasMany(Avis::class);
    }

    public function evaluationIA()
    {
        return $this->hasOne(EvaluationIA::class);
    }

    public function getNombreAvisAttribute()
    {
        return $this->avis()->count();
    }

    public function getAvisFavorablesAttribute()
    {
        return $this->avis()->where('avis', 'FAVORABLE')->count();
    }

    public function getAvisDefavorablesAttribute()
    {
        return $this->avis()->where('avis', 'NON_FAVORABLE')->count();
    }

    /**
     * Met à jour le statut du dossier selon les avis donnés.
     * Appelée après chaque nouvel avis.
     */
    public function updateStatutSelonAvis()
    {
        $avisTotal = $this->avis()->count();
        
        // Si on n'a pas encore 5 avis, on ne fait rien
        if ($avisTotal < 5) {
            return;
        }

        // Récupérer la date du dernier avis
        $dernierAvis = $this->avis()->latest()->first();
        $this->date_dernier_avis = $dernierAvis->created_at;

        // Compter les avis favorables et défavorables
        $favorables = $this->avis()->where('avis', 'FAVORABLE')->count();
        $defavorables = $this->avis()->where('avis', 'NON_FAVORABLE')->count();

        // Mettre à jour le statut selon les règles
        if ($favorables === 5) {
            // Tous les avis sont favorables
            $this->statut = 'PROVISOIREMENT_VALIDER';
        } elseif ($defavorables === 5) {
            // Tous les avis sont défavorables
            $this->statut = 'REJETER';
        } elseif ($defavorables >= 1) {
            // Au moins un avis défavorable
            $this->statut = 'AJOURNER';
        }

        $this->save();
    }

    /**
     * Applique les règles de délai pour le président du comité.
     * À appeler via une tâche planifiée.
     */
    public function appliquerDelaisDecision()
    {
        if ($this->statut === 'PROVISOIREMENT_VALIDER' && $this->date_dernier_avis) {
            if (now()->diffInDays($this->date_dernier_avis) >= 7) {
                $this->statut = 'VALIDER';
                $this->save();
            }
        }
        if ($this->statut === 'AJOURNER' && $this->date_dernier_avis) {
            if (now()->diffInDays($this->date_dernier_avis) >= 14) {
                $this->statut = 'REJETER';
                $this->save();
            }
        }
    }
}
