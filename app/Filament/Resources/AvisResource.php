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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('dossier_id')
                    ->relationship('dossier', 'numero_dossier')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Dossier'),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Utilisateur'),
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
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dossier.numero_dossier')
                    ->searchable()
                    ->sortable()
                    ->label('Numéro du dossier'),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->label('Utilisateur'),
                Tables\Columns\TextColumn::make('avis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'FAVORABLE' => 'success',
                        'NON_FAVORABLE' => 'danger',
                    })
                    ->label('Avis'),
                Tables\Columns\TextColumn::make('observations')
                    ->limit(50)
                    ->label('Observations'),
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
