<?php

namespace App\Filament\Resources\EvaluationIAResource\Pages;

use App\Filament\Resources\EvaluationIAResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEvaluationIA extends ViewRecord
{
    protected static string $resource = EvaluationIAResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
} 