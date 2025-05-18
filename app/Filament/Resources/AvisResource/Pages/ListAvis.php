<?php

namespace App\Filament\Resources\AvisResource\Pages;

use App\Filament\Resources\AvisResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAvis extends ListRecords
{
    protected static string $resource = AvisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
