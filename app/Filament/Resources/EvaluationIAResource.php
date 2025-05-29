<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EvaluationIAResource\Pages;
use App\Models\EvaluationIA;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EvaluationIAResource extends Resource
{
    protected static ?string $model = EvaluationIA::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationGroup = 'Gestion des dossiers';

    protected static ?string $navigationLabel = 'Évaluations IA';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations du dossier')
                    ->schema([
                        Forms\Components\Select::make('dossier_id')
                            ->relationship('dossier', 'numero_dossier')
                            ->required()
                            ->disabled()
                            ->label('Numéro du dossier'),
                        Forms\Components\TextInput::make('dossier.nom_client')
                            ->disabled()
                            ->label('Nom du client'),
                        Forms\Components\TextInput::make('dossier.prenom_client')
                            ->disabled()
                            ->label('Prénom du client'),
                        Forms\Components\TextInput::make('dossier.montant_sollicite')
                            ->numeric()
                            ->disabled()
                            ->label('Montant sollicité')
                            ->prefix('FCFA'),
                        Forms\Components\TextInput::make('dossier.duree_sollicitee')
                            ->numeric()
                            ->disabled()
                            ->label('Durée sollicitée')
                            ->suffix('mois'),
                    ])->columns(2),

                Forms\Components\Section::make('Résultats de l\'évaluation IA')
                    ->schema([
                        Forms\Components\TextInput::make('score_ia')
                            ->numeric()
                            ->disabled()
                            ->label('Score IA')
                            ->suffix('%'),
                        Forms\Components\TextInput::make('statut_ia')
                            ->disabled()
                            ->label('Statut IA'),
                        Forms\Components\TextInput::make('montant_prediction')
                            ->numeric()
                            ->disabled()
                            ->label('Montant prédit')
                            ->prefix('FCFA'),
                        Forms\Components\TextInput::make('duree_prediction')
                            ->numeric()
                            ->disabled()
                            ->label('Durée prédite')
                            ->suffix('mois'),
                    ])->columns(2),

                Forms\Components\Section::make('Ratios financiers')
                    ->schema([
                        Forms\Components\TextInput::make('ratio_endettement')
                            ->numeric()
                            ->disabled()
                            ->label('Ratio d\'endettement')
                            ->suffix('%'),
                        Forms\Components\TextInput::make('ratio_garanties')
                            ->numeric()
                            ->disabled()
                            ->label('Ratio de garanties')
                            ->suffix('%'),
                        Forms\Components\TextInput::make('ratio_rentabilite')
                            ->numeric()
                            ->disabled()
                            ->label('Ratio de rentabilité')
                            ->suffix('%'),
                    ])->columns(3),

                Forms\Components\Section::make('Features utilisées pour l\'évaluation')
                    ->schema([
                        Forms\Components\KeyValue::make('features_utilisees')
                            ->disabled()
                            ->label('Features utilisées'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dossier.numero_dossier')
                    ->searchable()
                    ->sortable()
                    ->label('Dossier'),
                Tables\Columns\TextColumn::make('dossier.nom_client')
                    ->searchable()
                    ->sortable()
                    ->label('Nom client'),
                Tables\Columns\TextColumn::make('dossier.prenom_client')
                    ->searchable()
                    ->sortable()
                    ->label('Prénom client'),
                Tables\Columns\TextColumn::make('score_ia')
                    ->numeric()
                    ->sortable()
                    ->label('Score IA')
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%'),
                Tables\Columns\TextColumn::make('statut_ia')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'FAVORABLE' => 'success',
                        'NON_FAVORABLE' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('montant_prediction')
                    ->money('XOF')
                    ->sortable()
                    ->label('Montant prédit'),
                Tables\Columns\TextColumn::make('duree_prediction')
                    ->numeric()
                    ->sortable()
                    ->label('Durée prédite')
                    ->suffix(' mois'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Date d\'évaluation'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('statut_ia')
                    ->options([
                        'FAVORABLE' => 'Favorable',
                        'NON_FAVORABLE' => 'Non favorable',
                    ])
                    ->label('Statut IA'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Voir détails')
                    ->icon('heroicon-o-eye')
                    ->color('primary'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvaluationIAs::route('/'),
            'create' => Pages\CreateEvaluationIA::route('/create'),
            'view' => Pages\ViewEvaluationIA::route('/{record}'),
        ];
    }
} 