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
use Illuminate\Support\Facades\Log;

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
        $numeroDossier = trim($worksheet->getCell('B5')->getValue());
        $guichet = trim($worksheet->getCell('B6')->getValue());
        $codeAdherent = trim($worksheet->getCell('B13')->getValue());
        $datePriseInfo = trim($worksheet->getCell('D9')->getValue());
        $renouvellement = trim($worksheet->getCell('D10')->getValue());
        $activiteFinancer = trim($worksheet->getCell('D11')->getValue());
        
        // Montant sollicité (A10)
        $montantSollicite = $worksheet->getCell('A10')->getValue();
        Log::info('Valeur brute du montant sollicité : ' . print_r($montantSollicite, true));
        
        if (empty($montantSollicite) || $montantSollicite === null) {
            Notification::make()
                ->title('Erreur')
                ->body('Le montant sollicité est manquant dans la cellule A10')
                ->danger()
                ->send();
            return;
        }

        // Nettoyer et convertir le montant
        $montantSollicite = str_replace([' ', '€', 'FCFA', 'F', 'CFA'], '', $montantSollicite);
        $montantSollicite = str_replace(',', '.', $montantSollicite);
        $montantSollicite = preg_replace('/[^0-9.]/', '', $montantSollicite);
        Log::info('Montant sollicité après nettoyage : ' . $montantSollicite);
        
        // Vérifier si c'est une formule Excel
        if (is_string($montantSollicite) && strpos($montantSollicite, '=') === 0) {
            $montantSollicite = $worksheet->getCell('A10')->getCalculatedValue();
            Log::info('Montant sollicité après calcul de formule : ' . $montantSollicite);
        }
        
        if (!is_numeric($montantSollicite)) {
            Notification::make()
                ->title('Erreur')
                ->body('Le montant sollicité dans la cellule A10 n\'est pas un nombre valide. Valeur trouvée : ' . $montantSollicite)
                ->danger()
                ->send();
            return;
        }

        $montantSollicite = (float)$montantSollicite;
        Log::info('Montant sollicité final : ' . $montantSollicite);

        // Gestion des valeurs manquantes pour la durée et la périodicité
        $dureeSollicitee = $worksheet->getCell('C13')->getValue();
        Log::info('Valeur brute de la durée sollicitée : ' . print_r($dureeSollicitee, true));
        
        if (empty($dureeSollicitee) || $dureeSollicitee === null) {
            $dureeSollicitee = 12; // Durée par défaut de 12 mois
            Log::info('Utilisation de la durée par défaut : 12 mois');
        } else {
            // Nettoyer la valeur et s'assurer qu'elle est numérique
            $dureeSollicitee = preg_replace('/[^0-9]/', '', $dureeSollicitee);
            if (empty($dureeSollicitee)) {
                $dureeSollicitee = 12;
                Log::info('La durée nettoyée est vide, utilisation de la durée par défaut : 12 mois');
            } else {
                $dureeSollicitee = (int)$dureeSollicitee;
                Log::info('Durée sollicitée convertie en entier : ' . $dureeSollicitee);
            }
        }

        // Périodicité sollicitée (E13:G13)
        $periodiciteSollicitee = trim($worksheet->getCell('E13')->getValue());
        if (empty($periodiciteSollicitee)) {
            $periodiciteSollicitee = 'MENSUEL'; // Périodicité par défaut
        }

        // Informations de l'emprunteur
        $nomClient = trim($worksheet->getCell('B14')->getValue());
        $prenomClient = trim($worksheet->getCell('B15')->getValue());
        $sexe = trim($worksheet->getCell('E16')->getValue());
        $dateNaissance = trim($worksheet->getCell('C17')->getValue());
        $lieuNaissance = trim($worksheet->getCell('F17')->getValue());
        $adresse = trim($worksheet->getCell('B18')->getValue());
        $residenceDepuis = trim($worksheet->getCell('E19')->getValue());
        $metier = trim($worksheet->getCell('B20')->getValue());
        $activites = trim($worksheet->getCell('F20')->getValue());
        $numeroIFU = trim($worksheet->getCell('C22')->getValue());
        $situationMatrimoniale = trim($worksheet->getCell('D26')->getValue());
        $personnesCharge = (int)trim($worksheet->getCell('D27')->getValue());
        $reference = trim($worksheet->getCell('D30')->getValue());

        // Informations sur l'entreprise
        $adresseEntreprise = trim($worksheet->getCell('C32')->getValue());
        $niveauConcurrence = trim($worksheet->getCell('D40')->getValue());

        // État des produits et charges
        $salaireNet = (float)trim($worksheet->getCell('C45')->getValue());
        $totalRevenus = $worksheet->getCell('C53')->getCalculatedValue();
        $totalDepenses = $worksheet->getCell('F53')->getCalculatedValue();
        $actifNet = $totalRevenus - $totalDepenses;

        // Log des valeurs pour debug
        Log::info('Calcul de l\'actif net :', [
            'total_revenus' => $totalRevenus,
            'total_depenses' => $totalDepenses,
            'actif_net_calcule' => $actifNet
        ]);

        // Historique des crédits
        $totalCredits = $worksheet->getCell('D67')->getValue();
        $nombreCreditsAnterieurs = $worksheet->getCell('A59')->getValue();
        $montantMoyenCreditsAnterieurs = $worksheet->getCell('B59')->getValue();
        $echeancierRespecte = $worksheet->getCell('C59')->getValue();

        Log::info('Historique des crédits :', [
            'total_credits' => $totalCredits,
            'nombre_credits_anterieurs' => $nombreCreditsAnterieurs,
            'montant_moyen_credits_anterieurs' => $montantMoyenCreditsAnterieurs,
            'echeancier_respecte' => $echeancierRespecte
        ]);

        // Nettoyer et convertir les valeurs numériques
        $totalCredits = is_numeric($totalCredits) ? (float)str_replace([' ', ','], ['', '.'], $totalCredits) : 0;
        $nombreCreditsAnterieurs = is_numeric($nombreCreditsAnterieurs) ? (int)$nombreCreditsAnterieurs : 0;
        $montantMoyenCreditsAnterieurs = is_numeric($montantMoyenCreditsAnterieurs) ? (float)str_replace([' ', ','], ['', '.'], $montantMoyenCreditsAnterieurs) : 0;
        $echeancierRespecte = trim($echeancierRespecte);

        // Calculer un score basé sur l'historique des crédits
        $scoreHistorique = 0;
        
        // Score basé sur le nombre de crédits antérieurs (max 30 points)
        if ($nombreCreditsAnterieurs > 0) {
            $scoreHistorique += min(30, $nombreCreditsAnterieurs * 10);
        }
        
        // Score basé sur le respect de l'échéancier (max 40 points)
        if (strtoupper($echeancierRespecte) === 'OUI') {
            $scoreHistorique += 40;
        } elseif (strtoupper($echeancierRespecte) === 'PARTIELLEMENT') {
            $scoreHistorique += 20;
        }
        
        // Score basé sur le montant moyen des crédits (max 30 points)
        if ($montantMoyenCreditsAnterieurs > 0) {
            $ratioMontant = $montantMoyenCreditsAnterieurs / $montantSollicite;
            if ($ratioMontant >= 1) {
                $scoreHistorique += 30;
            } elseif ($ratioMontant >= 0.5) {
                $scoreHistorique += 20;
            } elseif ($ratioMontant > 0) {
                $scoreHistorique += 10;
            }
        }

        Log::info('Score historique calculé : ' . $scoreHistorique);

        // Évaluation du besoin
        $coutTotalProjet = $worksheet->getCell('F72')->getCalculatedValue();
        $besoinFinancement = $worksheet->getCell('C77')->getCalculatedValue();

        // Garanties
        $totalGaranties = (float)trim($worksheet->getCell('C96')->getValue());

        // Bilan de l'entreprise
        $encaisse = $worksheet->getCell('C95')->getValue();
        $totalActif = $worksheet->getCell('C106')->getCalculatedValue();
        $totalPassif = $worksheet->getCell('F106')->getCalculatedValue();

        // Log des valeurs brutes du bilan entreprise
        Log::info('Valeurs brutes du bilan entreprise :', [
            'encaisse_raw' => $encaisse,
            'total_actif_raw' => $totalActif,
            'total_passif_raw' => $totalPassif
        ]);

        // Nettoyer et convertir les valeurs
        $encaisse = (float)str_replace([' ', ','], ['', '.'], $encaisse);
        $totalActif = (float)str_replace([' ', ','], ['', '.'], $totalActif);
        $totalPassif = (float)str_replace([' ', ','], ['', '.'], $totalPassif);

        // Log des valeurs nettoyées du bilan entreprise
        Log::info('Valeurs nettoyées du bilan entreprise :', [
            'encaisse_cleaned' => $encaisse,
            'total_actif_cleaned' => $totalActif,
            'total_passif_cleaned' => $totalPassif
        ]);

        // Compte d'exploitation
        $vente = (float)trim($worksheet->getCell('C111')->getValue());
        $surplusNet = $worksheet->getCell('C123')->getCalculatedValue();

        // Avis et décisions
        $avisAC = trim($worksheet->getCell('C138')->getValue());
        $montantApprouveAC = (float)trim($worksheet->getCell('B139')->getValue());
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
        
        if (empty($numeroDossier)) $champsManquants[] = 'Numéro de dossier (B5)';
        if (empty($nomClient)) $champsManquants[] = 'Nom du client (B14)';
        if (empty($prenomClient)) $champsManquants[] = 'Prénom du client (B15)';
        if (empty($montantSollicite)) $champsManquants[] = 'Montant sollicité (A10)';
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
        // On initialise le score à 0, il sera calculé par l'IA
        $score = 0;
        $montantPropose = null;
        $dureeProposee = null;
        $periodiciteProposee = null;

        // Convertir les dates Excel en dates PHP
        try {
            $datePriseInfo = $worksheet->getCell('D9')->getValue();
            Log::info('Valeur brute de la date de prise d\'info : ' . print_r($datePriseInfo, true));
            
            if (is_numeric($datePriseInfo)) {
                $datePriseInfo = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($datePriseInfo);
            } else {
                // Essayer de parser la date si elle est au format texte
                $datePriseInfo = \DateTime::createFromFormat('d/m/Y', $datePriseInfo);
                if (!$datePriseInfo) {
                    $datePriseInfo = null;
                }
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la conversion de la date de prise d\'info : ' . $e->getMessage());
            $datePriseInfo = null;
        }

        try {
            $dateNaissance = $worksheet->getCell('C17')->getValue();
            Log::info('Valeur brute de la date de naissance : ' . print_r($dateNaissance, true));
            
            if (is_numeric($dateNaissance)) {
                $dateNaissance = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateNaissance);
            } else {
                // Essayer de parser la date si elle est au format texte
                $dateNaissance = \DateTime::createFromFormat('d/m/Y', $dateNaissance);
                if (!$dateNaissance) {
                    $dateNaissance = null;
                }
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la conversion de la date de naissance : ' . $e->getMessage());
            $dateNaissance = null;
        }
        
        // Nettoyer le sexe
        $sexe = strtoupper(trim(str_replace(['Sexe :', ':', ' '], '', $sexe)));
        if (!in_array($sexe, ['M', 'F'])) {
            $sexe = null;
        }
        
        // Convertir les valeurs numériques en float
        $montantSollicite = (float)str_replace([' ', ','], ['', '.'], $montantSollicite);
        $salaireNet = (float)str_replace([' ', ','], ['', '.'], $salaireNet);
        $totalRevenus = (float)str_replace([' ', ','], ['', '.'], $totalRevenus);
        $totalDepenses = (float)str_replace([' ', ','], ['', '.'], $totalDepenses);
        $actifNet = (float)str_replace([' ', ','], ['', '.'], $actifNet);
        $totalCredits = (float)str_replace([' ', ','], ['', '.'], $totalCredits);
        $coutTotalProjet = (float)str_replace([' ', ','], ['', '.'], $coutTotalProjet);
        $besoinFinancement = (float)str_replace([' ', ','], ['', '.'], $besoinFinancement);
        $totalGaranties = (float)str_replace([' ', ','], ['', '.'], $totalGaranties);
        $encaisse = (float)str_replace([' ', ','], ['', '.'], $encaisse);
        $totalActif = (float)str_replace([' ', ','], ['', '.'], $totalActif);
        $totalPassif = (float)str_replace([' ', ','], ['', '.'], $totalPassif);
        $vente = (float)str_replace([' ', ','], ['', '.'], $vente);
        $surplusNet = (float)str_replace([' ', ','], ['', '.'], $surplusNet);
        $montantApprouveAC = (float)str_replace([' ', ','], ['', '.'], $montantApprouveAC);
        $montantApprouveCTC1 = (float)str_replace([' ', ','], ['', '.'], $montantApprouveCTC1);
        $montantApprouveCTC2 = (float)str_replace([' ', ','], ['', '.'], $montantApprouveCTC2);

        // Nettoyer les avis
        $avisAC = trim(str_replace('=', '', $avisAC));
        $avisCTC1 = trim(str_replace(['=', '+', 'D151'], '', $avisCTC1));
        $avisCTC2 = trim(str_replace('=', '', $avisCTC2));

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
        $dossier->nombre_credits_anterieurs = $nombreCreditsAnterieurs;
        $dossier->montant_moyen_credits_anterieurs = $montantMoyenCreditsAnterieurs;
        $dossier->echeancier_respecte = $echeancierRespecte;

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
