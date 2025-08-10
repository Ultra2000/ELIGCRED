# Guide Utilisateur - Système d'Évaluation de Crédit

## 🎯 Introduction

Ce guide vous accompagne dans l'utilisation du système d'évaluation de crédit. Le système est conçu pour automatiser et optimiser le processus de décision de crédit grâce à l'intelligence artificielle et un workflow collaboratif.

## 👥 Types d'Utilisateurs

### 🔑 Administrateur
- Gestion complète du système
- Configuration des utilisateurs et rôles
- Supervision globale des dossiers
- Accès aux statistiques avancées

### 👨‍💼 Superviseur
- Gestion des équipes d'évaluation
- Supervision des dossiers
- Validation des décisions importantes
- Génération de rapports

### 📊 Évaluateur
- Consultation des dossiers assignés
- Émission d'avis sur les dossiers
- Suivi de ses évaluations
- Consultation des recommandations IA

### 👀 Consultant
- Consultation en lecture seule
- Export de données
- Génération de rapports
- Analyse des tendances

## 🚀 Premiers Pas

### Connexion au Système

1. **Accéder à l'interface**
   - Ouvrir votre navigateur
   - Aller à `https://votre-domaine.com/admin`
   - Saisir vos identifiants

2. **Navigation principale**
   - **Tableau de bord** : Vue d'ensemble
   - **Dossiers** : Gestion des dossiers de crédit
   - **Avis** : Suivi des évaluations
   - **Utilisateurs** : Gestion des comptes (admin uniquement)

### Interface Principale

```
┌─────────────────────────────────────────────────────────────┐
│  🏠 Tableau de Bord    📁 Dossiers    📝 Avis    👥 Users   │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  📊 Statistiques Générales                                  │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐           │
│  │ 150 Total   │ │ 45 En cours │ │ 89% Taux    │           │
│  │ Dossiers    │ │ d'évaluation│ │ d'approbation│           │
│  └─────────────┘ └─────────────┘ └─────────────┘           │
│                                                             │
│  📈 Graphiques et Tendances                                 │
│  [Graphique des évaluations par mois]                      │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

## 📁 Gestion des Dossiers

### Création d'un Nouveau Dossier

1. **Accéder à la section Dossiers**
   - Cliquer sur "Dossiers" dans le menu principal
   - Cliquer sur "Nouveau Dossier"

2. **Remplir les informations obligatoires**

   #### Informations Générales
   - **Numéro de dossier** : Identifiant unique (ex: DOS-2024-001)
   - **Nom du client** : Nom de famille
   - **Prénom du client** : Prénom
   - **Montant sollicité** : Montant demandé en FCFA
   - **Durée sollicitée** : Durée en mois
   - **Périodicité** : MENSUEL, TRIMESTRIEL, SEMESTRIEL, ANNUEL

   #### Informations Client
   - **Sexe** : M ou F
   - **Date de naissance** : Format JJ/MM/AAAA
   - **Adresse** : Adresse complète
   - **Métier** : Profession du client
   - **Situation matrimoniale** : Célibataire, Marié(e), etc.

   #### Informations Financières
   - **Salaire net** : Revenus mensuels
   - **Total revenus** : Tous revenus confondus
   - **Total dépenses** : Charges mensuelles
   - **Actif net** : Patrimoine net

3. **Sauvegarde**
   - Cliquer sur "Créer" pour sauvegarder le dossier
   - Le statut initial sera "CHARGE"

### Consultation des Dossiers

#### Vue Liste
```
┌─────────────────────────────────────────────────────────────┐
│ 🔍 Recherche: [_______________] 📅 Période: [__________]    │
├─────────────────────────────────────────────────────────────┤
│ N° Dossier    │ Client        │ Montant    │ Statut │ Score IA │
├─────────────────────────────────────────────────────────────┤
│ DOS-2024-001  │ DUPONT Jean   │ 200 000 F │ CHARGE │ 0.85     │
│ DOS-2024-002  │ MARTIN Marie  │ 150 000 F │ VALIDER│ 0.92     │
│ DOS-2024-003  │ DURAND Paul   │ 300 000 F │ AJOURNER│ 0.45    │
└─────────────────────────────────────────────────────────────┘
```

#### Filtres Disponibles
- **Statut** : CHARGE, EN_EVALUATION, VALIDER, REJETER, etc.
- **Période** : Date de création, dernière modification
- **Score IA** : Plage de scores
- **Montant** : Plage de montants

#### Actions Rapides
- **👁️ Voir** : Consultation détaillée
- **✏️ Modifier** : Édition (si autorisé)
- **🤖 Évaluer** : Lancer l'évaluation IA
- **📄 Exporter** : Export PDF/Excel

### Évaluation par IA

#### Lancement Manuel
1. Ouvrir un dossier
2. Cliquer sur "Évaluer avec IA"
3. Choisir la source :
   - **Données du formulaire** : Utilise les données saisies
   - **Fichier Excel** : Upload d'un fichier Excel

#### Résultats d'Évaluation
```
┌─────────────────────────────────────────────────────────────┐
│ 🤖 Évaluation IA - Résultats                                │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ 📊 Score de Risque: 0.85 (Faible risque)                   │
│ 📈 Recommandation: FAVORABLE                                │
│                                                             │
│ 💰 Montant Recommandé: 180 000 FCFA                        │
│ ⏱️  Durée Recommandée: 24 mois                              │
│                                                             │
│ 📋 Ratios Calculés:                                         │
│ • Endettement: 45.5%                                       │
│ • Garanties: 120.0%                                        │
│ • Rentabilité: 15.2%                                       │
│                                                             │
│ ⚠️  Points d'Attention:                                     │
│ • Ratio d'endettement élevé                                │
│ • Historique de crédit limité                              │
└─────────────────────────────────────────────────────────────┘
```

## 📝 Système d'Avis

### Donner un Avis (Évaluateurs)

1. **Accéder au dossier**
   - Aller dans "Dossiers" 
   - Cliquer sur le dossier à évaluer

2. **Section Avis**
   - Faire défiler jusqu'à "Avis et Évaluations"
   - Cliquer sur "Nouveau Avis"

3. **Formulaire d'Avis**
   ```
   ┌─────────────────────────────────────────────────────┐
   │ 📝 Nouvel Avis                                      │
   ├─────────────────────────────────────────────────────┤
   │                                                     │
   │ Avis: ○ FAVORABLE  ○ NON_FAVORABLE                  │
   │                                                     │
   │ Observations:                                       │
   │ ┌─────────────────────────────────────────────────┐ │
   │ │ Saisir vos commentaires et justifications...    │ │
   │ │                                                 │ │
   │ │                                                 │ │
   │ └─────────────────────────────────────────────────┘ │
   │                                                     │
   │ [Annuler]                            [Sauvegarder] │
   └─────────────────────────────────────────────────────┘
   ```

4. **Validation**
   - Choisir FAVORABLE ou NON_FAVORABLE
   - Ajouter des observations détaillées
   - Cliquer sur "Sauvegarder"

### Consultation des Avis

#### Vue Chronologique
```
┌─────────────────────────────────────────────────────────────┐
│ 📝 Historique des Avis                                      │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ 👤 Jean MARTIN - 15/01/2024 10:30                          │
│ ✅ FAVORABLE                                                │
│ "Dossier solide, garanties suffisantes, bon historique"    │
│                                                             │
│ 👤 Marie DUBOIS - 15/01/2024 14:20                         │
│ ✅ FAVORABLE                                                │
│ "Projet viable, capacité de remboursement confirmée"       │
│                                                             │
│ 👤 Paul BERNARD - 16/01/2024 09:15                         │
│ ❌ NON_FAVORABLE                                            │
│ "Ratio d'endettement trop élevé, risque important"         │
│                                                             │
│ 📊 Résumé: 2 Favorables, 1 Non Favorable (3/5 avis)       │
└─────────────────────────────────────────────────────────────┘
```

## 📊 Tableaux de Bord et Statistiques

### Tableau de Bord Principal

#### Métriques Clés
- **Nombre total de dossiers**
- **Dossiers en cours d'évaluation**
- **Taux d'approbation global**
- **Montant total des crédits accordés**
- **Performance des évaluateurs**

#### Graphiques Disponibles
- **Évolution mensuelle** des dossiers
- **Répartition par statut**
- **Performance de l'IA** vs évaluations humaines
- **Analyse des tendances** de crédit

### Rapports Personnalisés

#### Génération de Rapports
1. Aller dans "Rapports"
2. Choisir le type :
   - **Rapport d'activité** : Période donnée
   - **Analyse de performance** : Évaluateurs
   - **Statistiques IA** : Précision des modèles
3. Sélectionner les filtres
4. Générer et télécharger

#### Formats d'Export
- **PDF** : Rapports formatés
- **Excel** : Données brutes
- **CSV** : Import dans d'autres systèmes

## 🔧 Fonctionnalités Avancées

### Import/Export de Données

#### Import Excel
1. **Préparer le fichier Excel**
   - Utiliser le modèle fourni
   - Respecter les noms de colonnes
   - Vérifier les formats de données

2. **Processus d'Import**
   - Aller dans "Dossiers" → "Importer"
   - Sélectionner le fichier Excel
   - Valider les données
   - Confirmer l'import

#### Export de Données
1. **Sélection des dossiers**
   - Utiliser les filtres pour sélectionner
   - Cocher les dossiers souhaités
   - Ou sélectionner "Tous"

2. **Options d'Export**
   - **Format** : Excel, CSV, PDF
   - **Colonnes** : Choisir les champs à exporter
   - **Période** : Filtrer par dates

### Notifications et Alertes

#### Types de Notifications
- **Nouveau dossier** assigné
- **Avis requis** sur un dossier
- **Décision automatique** appliquée
- **Erreur d'évaluation** IA

#### Configuration
1. Aller dans "Profil" → "Notifications"
2. Choisir les types de notifications
3. Configurer les canaux (email, interface)

## 🔍 Recherche et Filtres

### Recherche Avancée

#### Critères de Recherche
- **Numéro de dossier** : Recherche exacte
- **Nom/Prénom client** : Recherche partielle
- **Montant** : Plage de valeurs
- **Statut** : Un ou plusieurs statuts
- **Score IA** : Plage de scores
- **Dates** : Période de création/modification

#### Exemples de Recherche
```
🔍 Recherche par montant:
   Montant entre 100 000 et 500 000 FCFA

🔍 Recherche par statut:
   Statut = "EN_EVALUATION" OU "PROVISOIREMENT_VALIDER"

🔍 Recherche par score IA:
   Score IA > 0.8 (dossiers à faible risque)

🔍 Recherche par période:
   Créés entre le 01/01/2024 et le 31/01/2024
```

### Tri et Organisation

#### Options de Tri
- **Date de création** : Plus récents d'abord
- **Montant sollicité** : Croissant/Décroissant
- **Score IA** : Meilleurs scores d'abord
- **Statut** : Alphabétique
- **Nombre d'avis** : Dossiers nécessitant une attention

## 📋 Workflows Métier

### Processus d'Évaluation Standard

#### 1. Réception du Dossier
```
📥 Nouveau dossier créé
    ↓
🤖 Évaluation IA automatique (optionnel)
    ↓
📤 Assignation aux évaluateurs
    ↓
📧 Notifications envoyées
```

#### 2. Phase d'Évaluation
```
👥 5 évaluateurs assignés
    ↓
📝 Chaque évaluateur donne son avis
    ↓
⚖️ Système calcule le statut automatiquement:
   • 5 FAVORABLE → PROVISOIREMENT_VALIDER
   • ≥1 NON_FAVORABLE → AJOURNER
   • 5 NON_FAVORABLE → REJETER
```

#### 3. Finalisation
```
⏰ Délais automatiques:
   • PROVISOIREMENT_VALIDER → VALIDER (7 jours)
   • AJOURNER → REJETER (14 jours)
    ↓
✅ Décision finale
    ↓
📄 Génération des documents
```

### Cas d'Usage Spécifiques

#### Dossier Urgent
1. Marquer le dossier comme "Prioritaire"
2. Notification immédiate aux évaluateurs
3. Suivi renforcé dans le tableau de bord

#### Dossier Complexe
1. Ajouter des documents supplémentaires
2. Demander des avis d'experts
3. Étendre le délai d'évaluation

#### Recours/Révision
1. Changer le statut vers "EN_REVISION"
2. Demander de nouveaux avis
3. Justifier la demande de révision

## 🎨 Personnalisation de l'Interface

### Préférences Utilisateur

#### Tableau de Bord Personnel
- **Widgets personnalisables** : Choisir les métriques affichées
- **Raccourcis** : Accès rapide aux fonctions fréquentes
- **Notifications** : Configuration des alertes

#### Vue Liste Personnalisée
- **Colonnes affichées** : Sélectionner les champs pertinents
- **Tri par défaut** : Définir l'ordre préféré
- **Nombre d'éléments** : Par page (10, 25, 50, 100)

### Thèmes et Apparence
- **Mode sombre/clair** : Adaptation à vos préférences
- **Taille de police** : Ajustement pour le confort de lecture
- **Langue** : Interface multilingue (si configuré)

## 📱 Utilisation Mobile

### Interface Responsive
Le système s'adapte automatiquement aux écrans mobiles :

#### Fonctionnalités Mobile
- **Consultation** des dossiers
- **Ajout d'avis** simplifié
- **Notifications** push
- **Recherche** rapide

#### Limitations Mobile
- **Création de dossiers** : Recommandé sur desktop
- **Import Excel** : Non disponible
- **Rapports complexes** : Affichage limité

## 🚨 Gestion des Erreurs et Support

### Messages d'Erreur Courants

#### "Erreur d'évaluation IA"
**Cause** : Problème avec le modèle de machine learning
**Solution** :
1. Vérifier que toutes les données obligatoires sont saisies
2. Contacter l'administrateur si le problème persiste

#### "Permissions insuffisantes"
**Cause** : Tentative d'accès à une fonction non autorisée
**Solution** :
1. Vérifier votre rôle utilisateur
2. Demander les permissions nécessaires à l'administrateur

#### "Dossier verrouillé"
**Cause** : Tentative de modification d'un dossier validé
**Solution** :
1. Les dossiers validés ne peuvent plus être modifiés
2. Demander une révision si nécessaire

### Obtenir de l'Aide

#### Support Intégré
- **Tooltips** : Aide contextuelle sur les champs
- **Guide rapide** : Accessible via le menu "?"
- **FAQ** : Questions fréquentes

#### Contact Support
1. **Chat intégré** : Bouton en bas à droite
2. **Email** : support@votre-domaine.com
3. **Documentation** : Liens vers cette documentation

## 📚 Bonnes Pratiques

### Pour les Évaluateurs

#### Qualité des Avis
- **Soyez précis** : Justifiez vos décisions
- **Documentez** : Ajoutez des observations détaillées
- **Cohérence** : Maintenez des critères constants
- **Rapidité** : Respectez les délais d'évaluation

#### Utilisation de l'IA
- **Complément** : L'IA aide mais ne remplace pas votre expertise
- **Validation** : Vérifiez toujours les recommandations
- **Feedback** : Signalez les incohérences

### Pour les Superviseurs

#### Gestion d'Équipe
- **Assignation équilibrée** : Répartir la charge de travail
- **Suivi de performance** : Monitorer les délais et qualité
- **Formation continue** : Organiser des sessions sur les nouveaux outils

#### Qualité des Décisions
- **Révision régulière** : Contrôler la cohérence des avis
- **Analyse des tendances** : Identifier les patterns
- **Amélioration continue** : Ajuster les processus

### Pour les Administrateurs

#### Maintenance du Système
- **Surveillance** : Monitorer les performances
- **Mises à jour** : Appliquer les correctifs de sécurité
- **Sauvegardes** : Vérifier les sauvegardes régulières

#### Gestion des Utilisateurs
- **Principe du moindre privilège** : Accorder les permissions minimales
- **Audit des accès** : Réviser régulièrement les droits
- **Formation** : Accompagner les nouveaux utilisateurs

## 🔧 Personnalisation Avancée

### Configuration des Règles Métier

#### Seuils d'Évaluation
Les administrateurs peuvent configurer :
- **Score IA minimum** : Seuil d'acceptation automatique
- **Ratio d'endettement max** : Limite d'endettement acceptable
- **Ratio de garanties min** : Garanties minimales requises

#### Délais de Validation
- **Délai de validation** : Temps avant validation automatique
- **Délai de rejet** : Temps avant rejet automatique
- **Notifications de rappel** : Fréquence des alertes

### Intégrations Externes

#### API Bancaires
- Configuration des connexions aux systèmes bancaires
- Synchronisation des données client
- Vérification automatique des comptes

#### Services de Scoring
- Intégration avec des bureaux de crédit
- Récupération automatique des scores externes
- Consolidation des informations

## 📞 Support et Formation

### Ressources de Formation

#### Documentation
- **Guide utilisateur** : Ce document
- **Documentation technique** : Pour les développeurs
- **API Reference** : Pour les intégrations

#### Formation Pratique
- **Tutoriels vidéo** : Disponibles dans l'interface
- **Sessions de formation** : Organisées par l'équipe
- **Webinaires** : Formations à distance

### Contact et Assistance

#### Niveaux de Support
1. **Auto-assistance** : Documentation et FAQ
2. **Support utilisateur** : Chat et email
3. **Support technique** : Pour les problèmes système
4. **Support urgent** : Ligne dédiée pour les urgences

#### Informations de Contact
- **Email général** : support@votre-domaine.com
- **Support technique** : tech@votre-domaine.com
- **Urgences** : +33 X XX XX XX XX
- **Documentation** : https://docs.votre-domaine.com