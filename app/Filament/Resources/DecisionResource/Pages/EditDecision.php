<?php

namespace App\Filament\Resources\DecisionResource\Pages;

use App\Filament\Resources\DecisionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditDecision extends EditRecord
{
    protected static string $resource = DecisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        $dossier = $this->record;
        $dateDernierAvis = $dossier->date_dernier_avis;

        if (!$dateDernierAvis) {
            Notification::make()
                ->title('Erreur')
                ->body('La date du dernier avis est manquante')
                ->danger()
                ->send();
            $this->halt();
            return;
        }

        $delai = $dossier->statut === 'PROVISOIREMENT_VALIDER' ? 7 : 14;
        $joursRestants = $delai - now()->diffInDays($dateDernierAvis);

        if ($joursRestants <= 0) {
            if ($dossier->statut === 'PROVISOIREMENT_VALIDER') {
                $this->data['statut'] = 'VALIDER';
            } else {
                $this->data['statut'] = 'REJETER';
            }

            Notification::make()
                ->title('Attention')
                ->body('Le délai est dépassé. Le statut a été automatiquement mis à jour.')
                ->warning()
                ->send();
        }
    }

    protected function afterSave(): void
    {
        $dossier = $this->record;
        
        // Si le dossier est validé, on met à jour les montants et durées
        if ($dossier->statut === 'VALIDER') {
            $dossier->montant_accorde = $this->data['montant_accorde'];
            $dossier->duree_accordee = $this->data['duree_accordee'];
            $dossier->periodicite_accordee = $this->data['periodicite_accordee'];
            $dossier->observations = $this->data['observations'];
            $dossier->save();
        }
    }
} 