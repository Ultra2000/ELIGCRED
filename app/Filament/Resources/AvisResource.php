<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AvisResource\Pages;
use App\Filament\Resources\AvisResource\RelationManagers;
use App\Models\Avis;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AvisResource extends Resource
{
    protected static ?string $model = Avis::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Gestion des dossiers';

    protected static ?string $modelLabel = 'Avis';
    protected static ?string $pluralModelLabel = 'Avis';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        // Ne montrer que les avis de l'utilisateur connecté
        if (auth()->user()->hasRole('mcc')) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('dossier_id')
                    ->relationship('dossier', 'numero_dossier', function ($query) {
                        return $query->where('statut', 'SOUMIS');
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Dossier'),
                Forms\Components\Hidden::make('user_id')
                    ->default(fn () => auth()->id()),
                Forms\Components\Select::make('avis')
                    ->required()
                    ->options([
                        'FAVORABLE' => 'Favorable',
                        'NON_FAVORABLE' => 'Non favorable',
                    ])
                    ->label('Avis'),
                Forms\Components\Textarea::make('observations')
                    ->label('Observations'),
            ]);
    }

    public static function table(Table $table): Table
    {
        // Si c'est le président, on affiche une vue différente
        if (auth()->user()->hasRole('president')) {
            return $table
                ->query(
                    \App\Models\Dossier::query()
                        ->whereHas('avis')
                        ->withCount('avis')
                        ->withCount(['avis as avis_favorables' => function ($query) {
                            $query->where('avis', 'FAVORABLE');
                        }])
                        ->withCount(['avis as avis_non_favorables' => function ($query) {
                            $query->where('avis', 'NON_FAVORABLE');
                        }])
                        ->withMax('avis', 'created_at')
                )
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
                    Tables\Columns\TextColumn::make('avis_count')
                        ->label('Nombre d\'avis'),
                    Tables\Columns\TextColumn::make('avis_favorables')
                        ->label('Avis favorables'),
                    Tables\Columns\TextColumn::make('avis_non_favorables')
                        ->label('Avis non favorables'),
                    Tables\Columns\TextColumn::make('avis_max_created_at')
                        ->dateTime()
                        ->sortable()
                        ->label('Dernier avis'),
                ])
                ->actions([
                    Tables\Actions\Action::make('voir_details')
                        ->label('Voir les détails')
                        ->icon('heroicon-o-eye')
                        ->modalContent(function ($record) {
                            return new \Illuminate\Support\HtmlString(
                                view('filament.resources.avis.modal-content', [
                                    'dossier' => $record,
                                    'avis' => $record->avis()->with('user')->get(),
                                ])->render()
                            );
                        })
                        ->modalHeading(fn ($record) => 'Détails du dossier ' . $record->numero_dossier)
                        ->modalWidth('3xl'),
                ])
                ->bulkActions([]);
        }

        // Pour les membres du comité, on garde l'affichage normal
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dossier.numero_dossier')
                    ->searchable()
                    ->sortable()
                    ->label('Numéro du dossier'),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->label('Membre du comité'),
                Tables\Columns\TextColumn::make('avis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'FAVORABLE' => 'success',
                        'NON_FAVORABLE' => 'danger',
                    })
                    ->label('Avis'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Date de l\'avis'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('avis')
                    ->options([
                        'FAVORABLE' => 'Favorable',
                        'NON_FAVORABLE' => 'Non favorable',
                    ])
                    ->label('Avis'),
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
            'index' => Pages\ListAvis::route('/'),
            'create' => Pages\CreateAvis::route('/create'),
            'edit' => Pages\EditAvis::route('/{record}/edit'),
        ];
    }
}
