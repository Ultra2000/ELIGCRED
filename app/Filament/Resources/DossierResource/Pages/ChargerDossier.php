<?php

namespace App\Filament\Resources\DossierResource\Pages;

use App\Filament\Resources\DossierResource;
use Filament\Resources\Pages\Page;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class ChargerDossier extends Page
{
    protected static string $resource = DossierResource::class;

    protected static string $view = 'filament.resources.dossier-resource.pages.charger-dossier';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('numero_dossier')
                    ->label('Numéro du dossier')
                    ->required(),
                FileUpload::make('fichier_excel')
                    ->label('Fichier Excel')
                    ->required()
                    ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                    ->directory('dossiers/excel')
                    ->helperText('Veuillez sélectionner le fichier Excel du dossier à charger'),
            ])
            ->statePath('data');
    }

    public function charger(): void
    {
        $data = $this->form->getState();
        
        // Vérifier que le fichier a été uploadé
        if (empty($data['fichier_excel'])) {
            Notification::make()
                ->title('Erreur')
                ->body('Aucun fichier n\'a été uploadé')
                ->danger()
                ->send();
            return;
        }

        // Afficher le chemin du fichier pour debug
        $fichierPath = storage_path('app/public/' . $data['fichier_excel']);
        
        // Vérifier que le fichier existe
        if (!file_exists($fichierPath)) {
            Notification::make()
                ->title('Erreur')
                ->body('Le fichier n\'a pas été trouvé à l\'emplacement : ' . $fichierPath)
                ->danger()
                ->send();
            return;
        }

        // Lire le fichier Excel
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($fichierPath);
        $worksheet = $spreadsheet->getActiveSheet();

        // Récupérer les données du fichier Excel
        $numeroDossier = trim($worksheet->getCell('D6')->getValue());
        $guichet = trim($worksheet->getCell('D7')->getValue());
        $codeAdherent = trim($worksheet->getCell('D8')->getValue());
        $datePriseInfo = trim($worksheet->getCell('D9')->getValue());
        $renouvellement = trim($worksheet->getCell('D10')->getValue());
        $activiteFinancer = trim($worksheet->getCell('D11')->getValue());
        
        // Montant sollicité (A13:B13)
        $montantSollicite = (float)trim($worksheet->getCell('A13')->getValue());
        
        // Gestion des valeurs manquantes pour la durée et la périodicité
        $dureeSollicitee = $worksheet->getCell('C13')->getValue();
        \Log::info('Valeur brute de la durée sollicitée : ' . print_r($dureeSollicitee, true));
        
        if (empty($dureeSollicitee) || $dureeSollicitee === null) {
            $dureeSollicitee = 12; // Durée par défaut de 12 mois
            \Log::info('Utilisation de la durée par défaut : 12 mois');
        } else {
            // Nettoyer la valeur et s'assurer qu'elle est numérique
            $dureeSollicitee = preg_replace('/[^0-9]/', '', $dureeSollicitee);
            if (empty($dureeSollicitee)) {
                $dureeSollicitee = 12;
                \Log::info('La durée nettoyée est vide, utilisation de la durée par défaut : 12 mois');
            } else {
                $dureeSollicitee = (int)$dureeSollicitee;
                \Log::info('Durée sollicitée convertie en entier : ' . $dureeSollicitee);
            }
        }

        // Périodicité sollicitée (E13:G13)
        $periodiciteSollicitee = trim($worksheet->getCell('E13')->getValue());
        if (empty($periodiciteSollicitee)) {
            $periodiciteSollicitee = 'MENSUEL'; // Périodicité par défaut
        }

        // Informations de l'emprunteur
        $nomClient = trim($worksheet->getCell('B17')->getValue());
        $prenomClient = trim($worksheet->getCell('B18')->getValue());
        $sexe = trim($worksheet->getCell('D19')->getValue());
        $dateNaissance = trim($worksheet->getCell('B20')->getValue());
        $lieuNaissance = trim($worksheet->getCell('F20')->getValue());
        $adresse = trim($worksheet->getCell('B21')->getValue());
        $residenceDepuis = trim($worksheet->getCell('F21')->getValue());
        $metier = trim($worksheet->getCell('B22')->getValue());
        $activites = trim($worksheet->getCell('F22')->getValue());
        $numeroIFU = trim($worksheet->getCell('C24')->getValue());
        $situationMatrimoniale = trim($worksheet->getCell('D26')->getValue());
        $personnesCharge = (int)trim($worksheet->getCell('D27')->getValue());
        $reference = trim($worksheet->getCell('D30')->getValue());

        // Informations sur l'entreprise
        $adresseEntreprise = trim($worksheet->getCell('D33')->getValue());
        $niveauConcurrence = trim($worksheet->getCell('D40')->getValue());

        // État des produits et charges
        $salaireNet = (float)trim($worksheet->getCell('C45')->getValue());
        $totalRevenus = (float)trim($worksheet->getCell('C53')->getValue());
        $totalDepenses = (float)trim($worksheet->getCell('F53')->getValue());
        $actifNet = (float)trim($worksheet->getCell('F54')->getValue());

        // Historique des crédits
        $totalCredits = (float)trim($worksheet->getCell('D67')->getValue());

        // Évaluation du besoin
        $coutTotalProjet = (float)trim($worksheet->getCell('F80')->getValue());
        $besoinFinancement = (float)trim($worksheet->getCell('C85')->getValue());

        // Garanties
        $totalGaranties = (float)trim($worksheet->getCell('C96')->getValue());

        // Bilan de l'entreprise
        $encaisse = (float)trim($worksheet->getCell('C101')->getValue());
        $totalActif = (float)trim($worksheet->getCell('C112')->getValue());
        $totalPassif = (float)trim($worksheet->getCell('F112')->getValue());

        // Compte d'exploitation
        $vente = (float)trim($worksheet->getCell('C118')->getValue());
        $surplusNet = (float)trim($worksheet->getCell('C130')->getValue());

        // Avis et décisions
        $avisAC = trim($worksheet->getCell('D150')->getValue());
        $montantApprouveAC = (float)trim($worksheet->getCell('C151')->getValue());
        $avisCTC1 = trim($worksheet->getCell('D158')->getValue());
        $montantApprouveCTC1 = (float)trim($worksheet->getCell('C159')->getValue());
        $avisCTC2 = trim($worksheet->getCell('D165')->getValue());
        $montantApprouveCTC2 = (float)trim($worksheet->getCell('C166')->getValue());

        // Commentaires
        $commentairesAC = trim($worksheet->getCell('D154')->getValue());
        $commentairesCTC1 = trim($worksheet->getCell('D162')->getValue());
        $commentairesCTC2 = trim($worksheet->getCell('D168')->getValue());

        // Vérifier que les champs obligatoires ne sont pas vides
        $champsManquants = [];
        
        if (empty($numeroDossier)) $champsManquants[] = 'Numéro de dossier (D6)';
        if (empty($nomClient)) $champsManquants[] = 'Nom du client (B17)';
        if (empty($prenomClient)) $champsManquants[] = 'Prénom du client (B18)';
        if (empty($montantSollicite)) $champsManquants[] = 'Montant sollicité (A13:B13)';
        if (empty($dureeSollicitee)) $champsManquants[] = 'Durée sollicitée (C13)';
        if (empty($periodiciteSollicitee)) $champsManquants[] = 'Périodicité sollicitée (E13:G13)';

        if (!empty($champsManquants)) {
            Notification::make()
                ->title('Erreur')
                ->body('Les champs suivants sont manquants dans le fichier Excel : ' . implode(', ', $champsManquants))
                ->danger()
                ->send();
            return;
        }

        // TODO: Appel IA pour calculer score, montant, durée, périodicité
        // Exemple fictif :
        $score = 85;
        $montantPropose = 10000;
        $dureeProposee = 12;
        $periodiciteProposee = 'MENSUEL';

        $dossier = new \App\Models\Dossier();
        $dossier->numero_dossier = $numeroDossier;
        $dossier->nom_client = $nomClient;
        $dossier->prenom_client = $prenomClient;
        $dossier->montant_sollicite = $montantSollicite;
        $dossier->duree_sollicitee = $dureeSollicitee;
        $dossier->periodicite_sollicitee = $periodiciteSollicitee;
        $dossier->fichier_excel_path = $data['fichier_excel'];
        $dossier->score_ia = $score;
        $dossier->montant_propose = $montantPropose;
        $dossier->duree_proposee = $dureeProposee;
        $dossier->periodicite_proposee = $periodiciteProposee;
        $dossier->statut = 'CHARGE';

        // Informations générales
        $dossier->guichet = $guichet;
        $dossier->code_adherent = $codeAdherent;
        $dossier->date_prise_info = $datePriseInfo;
        $dossier->renouvellement = $renouvellement === 'Oui';
        $dossier->activite_financer = $activiteFinancer;

        // Informations de l'emprunteur
        $dossier->sexe = $sexe;
        $dossier->date_naissance = $dateNaissance;
        $dossier->lieu_naissance = $lieuNaissance;
        $dossier->adresse = $adresse;
        $dossier->residence_depuis = $residenceDepuis;
        $dossier->metier = $metier;
        $dossier->activites = $activites;
        $dossier->numero_ifu = $numeroIFU;
        $dossier->situation_matrimoniale = $situationMatrimoniale;
        $dossier->personnes_charge = $personnesCharge;
        $dossier->reference = $reference;

        // Informations sur l'entreprise
        $dossier->adresse_entreprise = $adresseEntreprise;
        $dossier->niveau_concurrence = $niveauConcurrence;

        // État des produits et charges
        $dossier->salaire_net = $salaireNet;
        $dossier->total_revenus = $totalRevenus;
        $dossier->total_depenses = $totalDepenses;
        $dossier->actif_net = $actifNet;

        // Historique des crédits
        $dossier->total_credits = $totalCredits;

        // Évaluation du besoin
        $dossier->cout_total_projet = $coutTotalProjet;
        $dossier->besoin_financement = $besoinFinancement;

        // Garanties
        $dossier->total_garanties = $totalGaranties;

        // Bilan de l'entreprise
        $dossier->encaisse = $encaisse;
        $dossier->total_actif = $totalActif;
        $dossier->total_passif = $totalPassif;

        // Compte d'exploitation
        $dossier->vente = $vente;
        $dossier->surplus_net = $surplusNet;

        // Avis et décisions
        $dossier->avis_ac = $avisAC;
        $dossier->montant_approuve_ac = $montantApprouveAC;
        $dossier->avis_ctc1 = $avisCTC1;
        $dossier->montant_approuve_ctc1 = $montantApprouveCTC1;
        $dossier->avis_ctc2 = $avisCTC2;
        $dossier->montant_approuve_ctc2 = $montantApprouveCTC2;

        // Commentaires
        $dossier->commentaires_ac = $commentairesAC;
        $dossier->commentaires_ctc1 = $commentairesCTC1;
        $dossier->commentaires_ctc2 = $commentairesCTC2;

        try {
            $dossier->save();
            
            Notification::make()
                ->title('Succès')
                ->body('Le dossier a été chargé avec succès')
                ->success()
                ->send();

            $this->redirect(route('filament.admin.resources.dossiers.index'));
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur')
                ->body('Une erreur est survenue lors de l\'enregistrement du dossier : ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
