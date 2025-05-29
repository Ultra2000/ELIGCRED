<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DecisionResource\Pages;
use App\Models\Dossier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DecisionResource extends Resource
{
    protected static ?string $model = Dossier::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationGroup = 'Gestion des dossiers';

    protected static ?string $navigationLabel = 'Décisions';

    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasRole('pcc');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Ne montrer que les dossiers avec 5 avis et statut PROVISOIREMENT_VALIDER ou AJOURNER
        $query->whereHas('avis', function ($query) {
            $query->selectRaw('dossier_id, COUNT(*) as avis_count')
                ->groupBy('dossier_id')
                ->having('avis_count', 5);
        })
        ->whereIn('statut', ['PROVISOIREMENT_VALIDER', 'AJOURNER']);

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations du dossier')
                    ->schema([
                        Forms\Components\TextInput::make('numero_dossier')
                            ->disabled()
                            ->label('Numéro du dossier'),
                        Forms\Components\TextInput::make('nom_client')
                            ->disabled()
                            ->label('Nom du client'),
                        Forms\Components\TextInput::make('prenom_client')
                            ->disabled()
                            ->label('Prénom du client'),
                        Forms\Components\TextInput::make('montant_sollicite')
                            ->disabled()
                            ->numeric()
                            ->prefix('XOF')
                            ->label('Montant sollicité'),
                        Forms\Components\TextInput::make('duree_sollicitee')
                            ->disabled()
                            ->label('Durée sollicitée (mois)'),
                        Forms\Components\TextInput::make('periodicite_sollicitee')
                            ->disabled()
                            ->label('Périodicité sollicitée'),
                    ])->columns(2),

                Forms\Components\Section::make('Décision')
                    ->schema([
                        Forms\Components\Select::make('statut')
                            ->required()
                            ->options([
                                'VALIDER' => 'Valider',
                                'REJETER' => 'Rejeter',
                            ])
                            ->label('Décision'),
                        Forms\Components\TextInput::make('montant_accorde')
                            ->required()
                            ->numeric()
                            ->prefix('XOF')
                            ->label('Montant accordé'),
                        Forms\Components\TextInput::make('duree_accordee')
                            ->required()
                            ->numeric()
                            ->label('Durée accordée (mois)'),
                        Forms\Components\Select::make('periodicite_accordee')
                            ->required()
                            ->options([
                                'MENSUEL' => 'Mensuel',
                                'TRIMESTRIEL' => 'Trimestriel',
                                'SEMESTRIEL' => 'Semestriel',
                                'ANNUEL' => 'Annuel',
                            ])
                            ->label('Périodicité accordée'),
                        Forms\Components\Textarea::make('observations')
                            ->label('Observations'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_dossier')
                    ->searchable()
                    ->sortable()
                    ->label('Numéro du dossier'),
                Tables\Columns\TextColumn::make('nom_client')
                    ->searchable()
                    ->sortable()
                    ->label('Nom du client'),
                Tables\Columns\TextColumn::make('prenom_client')
                    ->searchable()
                    ->sortable()
                    ->label('Prénom du client'),
                Tables\Columns\TextColumn::make('montant_sollicite')
                    ->money('XOF')
                    ->sortable()
                    ->label('Montant sollicité'),
                Tables\Columns\TextColumn::make('statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'PROVISOIREMENT_VALIDER' => 'warning',
                        'AJOURNER' => 'danger',
                    })
                    ->label('Statut'),
                Tables\Columns\TextColumn::make('date_dernier_avis')
                    ->dateTime()
                    ->sortable()
                    ->label('Date du dernier avis'),
                Tables\Columns\TextColumn::make('delai_restant')
                    ->label('Délai restant')
                    ->formatStateUsing(function ($record) {
                        if (!$record->date_dernier_avis) return 'N/A';
                        
                        $delai = $record->statut === 'PROVISOIREMENT_VALIDER' ? 7 : 14;
                        $joursRestants = $delai - now()->diffInDays($record->date_dernier_avis);
                        
                        if ($joursRestants <= 0) {
                            return 'Délai dépassé';
                        }
                        
                        return $joursRestants . ' jour(s)';
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->options([
                        'PROVISOIREMENT_VALIDER' => 'Provisoirement validé',
                        'AJOURNER' => 'Ajourné',
                    ])
                    ->label('Statut'),
                Tables\Filters\Filter::make('periode')
                    ->form([
                        Forms\Components\DatePicker::make('date_debut')
                            ->label('Date de début'),
                        Forms\Components\DatePicker::make('date_fin')
                            ->label('Date de fin'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_debut'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_dernier_avis', '>=', $date),
                            )
                            ->when(
                                $data['date_fin'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date_dernier_avis', '<=', $date),
                            );
                    })
                    ->label('Période'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Décider'),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDecisions::route('/'),
            'edit' => Pages\EditDecision::route('/{record}/edit'),
        ];
    }
} 