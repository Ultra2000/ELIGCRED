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
use Filament\Notifications\Notification;

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
                    ->disabled(fn ($record) => $record && $record->statut === 'VALIDER' && !auth()->user()->hasRole('admin'))
                    ->label('Numéro du dossier'),
                Forms\Components\TextInput::make('nom_client')
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn ($record) => $record && $record->statut === 'VALIDER' && !auth()->user()->hasRole('admin'))
                    ->label('Nom du client'),
                Forms\Components\TextInput::make('prenom_client')
                    ->required()
                    ->maxLength(255)
                    ->disabled(fn ($record) => $record && $record->statut === 'VALIDER' && !auth()->user()->hasRole('admin'))
                    ->label('Prénom du client'),
                Forms\Components\TextInput::make('montant_sollicite')
                    ->required()
                    ->numeric()
                    ->disabled(fn ($record) => $record && $record->statut === 'VALIDER' && !auth()->user()->hasRole('admin'))
                    ->label('Montant sollicité'),
                Forms\Components\TextInput::make('duree_sollicitee')
                    ->required()
                    ->numeric()
                    ->disabled(fn ($record) => $record && $record->statut === 'VALIDER' && !auth()->user()->hasRole('admin'))
                    ->label('Durée sollicitée (en mois)'),
                Forms\Components\Select::make('periodicite_sollicitee')
                    ->required()
                    ->options([
                        'MENSUEL' => 'Mensuel',
                        'TRIMESTRIEL' => 'Trimestriel',
                        'SEMESTRIEL' => 'Semestriel',
                        'ANNUEL' => 'Annuel',
                    ])
                    ->disabled(fn ($record) => $record && $record->statut === 'VALIDER' && !auth()->user()->hasRole('admin'))
                    ->label('Périodicité sollicitée'),
                Forms\Components\TextInput::make('score_ia')
                    ->required()
                    ->numeric()
                    ->disabled(fn ($record) => $record && $record->statut === 'VALIDER' && !auth()->user()->hasRole('admin'))
                    ->label('Score IA'),
                Forms\Components\TextInput::make('montant_propose')
                    ->numeric()
                    ->disabled(fn ($record) => $record && $record->statut === 'VALIDER' && !auth()->user()->hasRole('admin'))
                    ->label('Montant proposé'),
                Forms\Components\TextInput::make('duree_proposee')
                    ->numeric()
                    ->disabled(fn ($record) => $record && $record->statut === 'VALIDER' && !auth()->user()->hasRole('admin'))
                    ->label('Durée proposée (en mois)'),
                Forms\Components\Select::make('periodicite_proposee')
                    ->options([
                        'MENSUEL' => 'Mensuel',
                        'TRIMESTRIEL' => 'Trimestriel',
                        'SEMESTRIEL' => 'Semestriel',
                        'ANNUEL' => 'Annuel',
                    ])
                    ->disabled(fn ($record) => $record && $record->statut === 'VALIDER' && !auth()->user()->hasRole('admin'))
                    ->label('Périodicité proposée'),
                Forms\Components\TextInput::make('montant_accorde')
                    ->numeric()
                    ->disabled(fn ($record) => $record && $record->statut === 'VALIDER' && !auth()->user()->hasRole('admin'))
                    ->label('Montant accordé'),
                Forms\Components\TextInput::make('duree_accordee')
                    ->numeric()
                    ->disabled(fn ($record) => $record && $record->statut === 'VALIDER' && !auth()->user()->hasRole('admin'))
                    ->label('Durée accordée (en mois)'),
                Forms\Components\Select::make('periodicite_accordee')
                    ->options([
                        'MENSUEL' => 'Mensuel',
                        'TRIMESTRIEL' => 'Trimestriel',
                        'SEMESTRIEL' => 'Semestriel',
                        'ANNUEL' => 'Annuel',
                    ])
                    ->disabled(fn ($record) => $record && $record->statut === 'VALIDER' && !auth()->user()->hasRole('admin'))
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
                    ->disabled(fn ($record) => $record && $record->statut === 'VALIDER' && !auth()->user()->hasRole('admin'))
                    ->label('Statut'),
                Forms\Components\FileUpload::make('fichier_excel_path')
                    ->required()
                    ->directory('dossiers/excel')
                    ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                    ->disabled(fn ($record) => $record && $record->statut === 'VALIDER' && !auth()->user()->hasRole('admin'))
                    ->label('Fichier Excel'),
                Forms\Components\FileUpload::make('fichier_pdf_path')
                    ->directory('dossiers/pdf')
                    ->acceptedFileTypes(['application/pdf'])
                    ->disabled(fn ($record) => $record && $record->statut === 'VALIDER' && !auth()->user()->hasRole('admin'))
                    ->label('Fichier PDF'),
                Forms\Components\Textarea::make('observations')
                    ->disabled(fn ($record) => $record && $record->statut === 'VALIDER' && !auth()->user()->hasRole('admin'))
                    ->label('Observations'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_dossier')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nom_client')
                    ->searchable(),
                Tables\Columns\TextColumn::make('prenom_client')
                    ->searchable(),
                Tables\Columns\TextColumn::make('montant_sollicite')
                    ->money('XOF')
                    ->sortable(),
                Tables\Columns\TextColumn::make('duree_sollicitee')
                    ->sortable(),
                Tables\Columns\TextColumn::make('score_ia')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) . '%' : '—'),
                Tables\Columns\TextColumn::make('statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'CHARGE' => 'gray',
                        'EVALUE' => 'success',
                        'REFUSE' => 'danger',
                        default => 'gray',
                    }),
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
                Tables\Actions\Action::make('view_details')
                    ->label('Voir détails')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->modalContent(function ($record) {
                        return new \Illuminate\Support\HtmlString(
                            '<div class="space-y-6">
                                <div class="bg-primary-50 dark:bg-gray-800 rounded-lg shadow-sm border border-primary-200 dark:border-gray-700 p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informations générales</h3>
                                    <div class="grid grid-cols-2 gap-6">
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Numéro du dossier</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . $record->numero_dossier . '</div>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Client</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . $record->nom_client . ' ' . $record->prenom_client . '</div>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Guichet</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . $record->guichet . '</div>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Code adhérent</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . $record->code_adherent . '</div>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Date prise d\'information</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . $record->date_prise_info . '</div>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Renouvellement</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . $record->renouvellement . '</div>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Activité à financer</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . $record->activite_financer . '</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-primary-50 dark:bg-gray-800 rounded-lg shadow-sm border border-primary-200 dark:border-gray-700 p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informations de l\'emprunteur</h3>
                                    <div class="grid grid-cols-2 gap-6">
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Sexe</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . $record->sexe . '</div>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Date de naissance</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . $record->date_naissance . '</div>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Lieu de naissance</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . $record->lieu_naissance . '</div>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Adresse</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . $record->adresse . '</div>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Résidence depuis</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . $record->residence_depuis . '</div>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Métier</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . $record->metier . '</div>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Activités</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . $record->activites . '</div>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Numéro IFU</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . $record->numero_ifu . '</div>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Situation matrimoniale</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . $record->situation_matrimoniale . '</div>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Personnes à charge</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . $record->personnes_charge . '</div>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Référence</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . $record->reference . '</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-primary-50 dark:bg-gray-800 rounded-lg shadow-sm border border-primary-200 dark:border-gray-700 p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informations sur l\'entreprise</h3>
                                    <div class="grid grid-cols-2 gap-6">
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Adresse de l\'entreprise</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . $record->adresse_entreprise . '</div>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Niveau de concurrence</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . $record->niveau_concurrence . '</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-primary-50 dark:bg-gray-800 rounded-lg shadow-sm border border-primary-200 dark:border-gray-700 p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">État des produits et charges</h3>
                                    <div class="grid grid-cols-2 gap-6">
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Salaire net</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . number_format($record->salaire_net, 0, ',', ' ') . ' XOF</div>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Total revenus</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . number_format($record->total_revenus, 0, ',', ' ') . ' XOF</div>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Total dépenses</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . number_format($record->total_depenses, 0, ',', ' ') . ' XOF</div>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Actif net</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . number_format($record->actif_net, 0, ',', ' ') . ' XOF</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-primary-50 dark:bg-gray-800 rounded-lg shadow-sm border border-primary-200 dark:border-gray-700 p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Historique des crédits</h3>
                                    <div class="grid grid-cols-2 gap-6">
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Total crédits</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . number_format($record->total_credits, 0, ',', ' ') . ' XOF</div>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Nombre de crédits antérieurs</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . $record->nombre_credits_anterieurs . '</div>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Montant moyen des crédits antérieurs</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . number_format($record->montant_moyen_credits_anterieurs, 0, ',', ' ') . ' XOF</div>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Échéancier respecté</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . $record->echeancier_respecte . '</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-primary-50 dark:bg-gray-800 rounded-lg shadow-sm border border-primary-200 dark:border-gray-700 p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Évaluation du besoin</h3>
                                    <div class="grid grid-cols-2 gap-6">
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Coût total du projet</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . number_format($record->cout_total_projet, 0, ',', ' ') . ' XOF</div>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Besoin en financement</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . number_format($record->besoin_financement, 0, ',', ' ') . ' XOF</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-primary-50 dark:bg-gray-800 rounded-lg shadow-sm border border-primary-200 dark:border-gray-700 p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Garanties</h3>
                                    <div class="grid grid-cols-2 gap-6">
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Total garanties</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . number_format($record->total_garanties, 0, ',', ' ') . ' XOF</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-primary-50 dark:bg-gray-800 rounded-lg shadow-sm border border-primary-200 dark:border-gray-700 p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Bilan de l\'entreprise</h3>
                                    <div class="grid grid-cols-2 gap-6">
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Encaisse</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . number_format($record->encaisse, 0, ',', ' ') . ' XOF</div>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Total actif</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . number_format($record->total_actif, 0, ',', ' ') . ' XOF</div>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Total passif</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . number_format($record->total_passif, 0, ',', ' ') . ' XOF</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-primary-50 dark:bg-gray-800 rounded-lg shadow-sm border border-primary-200 dark:border-gray-700 p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Compte d\'exploitation</h3>
                                    <div class="grid grid-cols-2 gap-6">
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Ventes</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . number_format($record->vente, 0, ',', ' ') . ' XOF</div>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Surplus net</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . number_format($record->surplus_net, 0, ',', ' ') . ' XOF</div>
                                        </div>
                                    </div>
                                </div>
                            </div>'
                        );
                    })
                    ->modalHeading(fn ($record) => 'Détails du dossier ' . $record->numero_dossier)
                    ->modalWidth('7xl'),
                Tables\Actions\Action::make('view_evaluation')
                    ->label('Évaluer IA')
                    ->icon('heroicon-o-cpu-chip')
                    ->color('success')
                    ->action(function (Dossier $dossier) {
                        try {
                            $evaluationService = new \App\Services\CreditEvaluationService();
                            $evaluationService->evaluateDossier($dossier);
                            
                            Notification::make()
                                ->title('Évaluation réussie')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erreur')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\EditAction::make()
                    ->label('Modifier')
                    ->visible(function ($record) {
                        // Si le dossier est validé, seul l'admin peut le modifier
                        if ($record->statut === 'VALIDER') {
                            return auth()->user()->hasRole('admin');
                        }
                        return true;
                    }),
                Tables\Actions\DeleteAction::make()
                    ->label('Supprimer')
                    ->visible(function ($record) {
                        // Si le dossier est validé, seul l'admin peut le supprimer
                        if ($record->statut === 'VALIDER') {
                            return auth()->user()->hasRole('admin');
                        }
                        return true;
                    }),
                Tables\Actions\Action::make('voir_avis')
                    ->label('Voir les avis')
                    ->icon('heroicon-o-eye')
                    ->visible(fn () => auth()->user()->hasRole('president'))
                    ->modalContent(function ($record) {
                        $avis = $record->avis()->with('user')->get();
                        
                        return new \Illuminate\Support\HtmlString(
                            '<div class="space-y-6">
                                <div class="bg-primary-50 dark:bg-gray-800 rounded-lg shadow-sm border border-primary-200 dark:border-gray-700 p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informations du dossier</h3>
                                    <div class="grid grid-cols-2 gap-6">
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Numéro du dossier</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . $record->numero_dossier . '</div>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Client</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . $record->nom_client . ' ' . $record->prenom_client . '</div>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Montant sollicité</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . number_format($record->montant_sollicite, 0, ',', ' ') . ' XOF</div>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Statut</div>
                                            <div class="text-base text-gray-900 dark:text-white font-medium">' . $record->statut . '</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-primary-50 dark:bg-gray-800 rounded-lg shadow-sm border border-primary-200 dark:border-gray-700 p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Tous les avis</h3>
                                    <div class="space-y-4">' .
                                    $avis->map(function ($unAvis) {
                                        return '<div class="bg-primary-50 dark:bg-gray-800 rounded-lg p-4 border border-primary-200 dark:border-gray-700">
                                            <div class="flex items-center justify-between mb-2">
                                                <div class="font-medium text-gray-900 dark:text-white">' . $unAvis->user->name . '</div>
                                                <div class="flex items-center space-x-2">
                                                    <div class="text-sm ' . ($unAvis->avis === 'FAVORABLE' ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400') . ' font-medium">
                                                        ' . ($unAvis->avis === 'FAVORABLE' ? 'Favorable' : 'Non favorable') . '
                                                    </div>
                                                    ' . (auth()->id() === $unAvis->user_id ? '
                                                    <form action="' . route('avis.destroy', $unAvis->id) . '" method="POST" class="inline">
                                                        ' . csrf_field() . '
                                                        ' . method_field('DELETE') . '
                                                        <button type="submit" class="text-danger-600 dark:text-danger-400 hover:text-danger-700 dark:hover:text-danger-300">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </form>' : '') . '
                                                </div>
                                            </div>' .
                                            ($unAvis->observations ? '<div class="mt-2 text-sm text-gray-900 dark:text-white bg-primary-100 dark:bg-gray-700 rounded p-3 border border-primary-200 dark:border-gray-600">' . $unAvis->observations . '</div>' : '') . '
                                            <div class="text-xs text-gray-600 dark:text-gray-400 mt-2">' . $unAvis->created_at->format('d/m/Y H:i') . '</div>
                                        </div>';
                                    })->join('') . '
                                    </div>
                                </div>
                            </div>'
                        );
                    })
                    ->modalHeading(fn ($record) => 'Avis pour le dossier ' . $record->numero_dossier)
                    ->modalWidth('4xl'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Supprimer la sélection')
                        ->visible(function () {
                            return auth()->user()->hasRole('admin');
                        }),
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

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        // Si l'utilisateur est un préposé au chargement, ne montrer que ses dossiers
        if (auth()->user()->hasRole('prepose-chargement')) {
            $query->where('user_id', auth()->id());
        }

        // Si l'utilisateur est un agent de crédit, ne montrer que les dossiers soumis
        if (auth()->user()->hasRole('agent-credit')) {
            $query->where('statut', 'SOUMIS');
        }

        return $query;
    }
}
