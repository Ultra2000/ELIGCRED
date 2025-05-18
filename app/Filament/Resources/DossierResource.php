<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DossierResource\Pages;
use App\Filament\Resources\DossierResource\RelationManagers;
use App\Models\Dossier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DossierResource extends Resource
{
    protected static ?string $model = Dossier::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Gestion des dossiers';

    protected static ?string $modelLabel = 'Dossier';
    protected static ?string $pluralModelLabel = 'Dossiers';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('numero_dossier')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->label('Numéro du dossier'),
                Forms\Components\TextInput::make('nom_client')
                    ->required()
                    ->maxLength(255)
                    ->label('Nom du client'),
                Forms\Components\TextInput::make('prenom_client')
                    ->required()
                    ->maxLength(255)
                    ->label('Prénom du client'),
                Forms\Components\TextInput::make('montant_sollicite')
                    ->required()
                    ->numeric()
                    ->label('Montant sollicité'),
                Forms\Components\TextInput::make('duree_sollicitee')
                    ->required()
                    ->numeric()
                    ->label('Durée sollicitée (en mois)'),
                Forms\Components\Select::make('periodicite_sollicitee')
                    ->required()
                    ->options([
                        'MENSUEL' => 'Mensuel',
                        'TRIMESTRIEL' => 'Trimestriel',
                        'SEMESTRIEL' => 'Semestriel',
                        'ANNUEL' => 'Annuel',
                    ])
                    ->label('Périodicité sollicitée'),
                Forms\Components\TextInput::make('score_ia')
                    ->required()
                    ->numeric()
                    ->label('Score IA'),
                Forms\Components\TextInput::make('montant_propose')
                    ->numeric()
                    ->label('Montant proposé'),
                Forms\Components\TextInput::make('duree_proposee')
                    ->numeric()
                    ->label('Durée proposée (en mois)'),
                Forms\Components\Select::make('periodicite_proposee')
                    ->options([
                        'MENSUEL' => 'Mensuel',
                        'TRIMESTRIEL' => 'Trimestriel',
                        'SEMESTRIEL' => 'Semestriel',
                        'ANNUEL' => 'Annuel',
                    ])
                    ->label('Périodicité proposée'),
                Forms\Components\TextInput::make('montant_accorde')
                    ->numeric()
                    ->label('Montant accordé'),
                Forms\Components\TextInput::make('duree_accordee')
                    ->numeric()
                    ->label('Durée accordée (en mois)'),
                Forms\Components\Select::make('periodicite_accordee')
                    ->options([
                        'MENSUEL' => 'Mensuel',
                        'TRIMESTRIEL' => 'Trimestriel',
                        'SEMESTRIEL' => 'Semestriel',
                        'ANNUEL' => 'Annuel',
                    ])
                    ->label('Périodicité accordée'),
                Forms\Components\Select::make('statut')
                    ->required()
                    ->options([
                        'CHARGE' => 'Chargé',
                        'SOUMIS' => 'Soumis',
                        'PROVISOIREMENT_VALIDER' => 'Provisoirement validé',
                        'AJOURNER' => 'Ajourné',
                        'VALIDER' => 'Validé',
                        'REJETER' => 'Rejeté',
                    ])
                    ->label('Statut'),
                Forms\Components\FileUpload::make('fichier_excel_path')
                    ->required()
                    ->directory('dossiers/excel')
                    ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                    ->label('Fichier Excel'),
                Forms\Components\FileUpload::make('fichier_pdf_path')
                    ->directory('dossiers/pdf')
                    ->acceptedFileTypes(['application/pdf'])
                    ->label('Fichier PDF'),
                Forms\Components\Textarea::make('observations')
                    ->label('Observations'),
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
                Tables\Columns\TextColumn::make('duree_sollicitee')
                    ->sortable()
                    ->label('Durée sollicitée'),
                Tables\Columns\TextColumn::make('periodicite_sollicitee')
                    ->sortable()
                    ->label('Périodicité sollicitée'),
                Tables\Columns\TextColumn::make('score_ia')
                    ->sortable()
                    ->label('Score IA'),
                Tables\Columns\TextColumn::make('statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'CHARGE' => 'gray',
                        'SOUMIS' => 'info',
                        'PROVISOIREMENT_VALIDER' => 'warning',
                        'AJOURNER' => 'warning',
                        'VALIDER' => 'success',
                        'REJETER' => 'danger',
                    })
                    ->label('Statut'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Date de création'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Date de modification'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->options([
                        'CHARGE' => 'Chargé',
                        'SOUMIS' => 'Soumis',
                        'PROVISOIREMENT_VALIDER' => 'Provisoirement validé',
                        'AJOURNER' => 'Ajourné',
                        'VALIDER' => 'Validé',
                        'REJETER' => 'Rejeté',
                    ])
                    ->label('Statut'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Modifier'),
                Tables\Actions\DeleteAction::make()
                    ->label('Supprimer'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Supprimer la sélection'),
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
            'index' => Pages\ListDossiers::route('/'),
            'create' => Pages\CreateDossier::route('/create'),
            'edit' => Pages\EditDossier::route('/{record}/edit'),
            'charger' => Pages\ChargerDossier::route('/charger'),
        ];
    }
}
