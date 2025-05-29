<?php

namespace App\Filament\Resources\DossierResource\Pages;

use App\Filament\Resources\DossierResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDossier extends EditRecord
{
    protected static string $resource = DossierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(function () {
                    // Si le dossier est validé, seul l'admin peut le supprimer
                    if ($this->record->statut === 'VALIDER') {
                        return auth()->user()->hasRole('admin');
                    }
                    return true;
                }),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->visible(function () {
                    // Si le dossier est validé, seul l'admin peut le sauvegarder
                    if ($this->record->statut === 'VALIDER') {
                        return auth()->user()->hasRole('admin');
                    }
                    return true;
                }),
            $this->getCancelFormAction(),
        ];
    }
}
