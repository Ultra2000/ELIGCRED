<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationIA extends Model
{
    protected $table = 'evaluations_ia';

    protected $fillable = [
        'dossier_id',
        'score_ia',
        'statut_ia',
        'montant_prediction',
        'duree_prediction',
        'ratio_endettement',
        'ratio_garanties',
        'ratio_rentabilite',
        'features_utilisees'
    ];

    protected $casts = [
        'features_utilisees' => 'array',
        'score_ia' => 'decimal:2',
        'montant_prediction' => 'decimal:2',
        'ratio_endettement' => 'decimal:2',
        'ratio_garanties' => 'decimal:2',
        'ratio_rentabilite' => 'decimal:2'
    ];

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }
} 