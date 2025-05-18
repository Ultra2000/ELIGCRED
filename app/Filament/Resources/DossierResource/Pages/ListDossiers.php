<?php

namespace App\Filament\Resources\DossierResource\Pages;

use App\Filament\Resources\DossierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDossiers extends ListRecords
{
    protected static string $resource = DossierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Créer un dossier'),
            Actions\Action::make('charger')
                ->label('Charger un dossier')
                ->url(fn () => route('filament.admin.resources.dossiers.charger'))
                ->icon('heroicon-o-arrow-up-tray'),
        ];
    }
}
