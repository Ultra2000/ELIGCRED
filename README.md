# Système d'Évaluation de Crédit avec IA

Un système complet de gestion et d'évaluation de dossiers de crédit utilisant Laravel, Filament et l'intelligence artificielle pour automatiser le processus de décision.

## 🏗️ Architecture du Projet

### Technologies Utilisées

- **Backend**: Laravel 12.0 (PHP 8.2+)
- **Interface Admin**: Filament 3.2
- **IA/ML**: Python 3 avec scikit-learn
- **Base de données**: SQLite/MySQL
- **Authentification**: Laravel Sanctum
- **Traitement Excel**: PhpSpreadsheet

### Structure du Projet

```
app/
├── Models/                 # Modèles Eloquent
│   ├── Dossier.php        # Modèle principal des dossiers de crédit
│   ├── User.php           # Gestion des utilisateurs
│   ├── Avis.php           # Système d'avis/opinions
│   ├── EvaluationIA.php   # Évaluations par IA
│   ├── Role.php           # Rôles utilisateurs
│   ├── Permission.php     # Permissions
│   └── Profile.php        # Profils utilisateurs
├── Http/Controllers/      # Contrôleurs
│   ├── CreditEvaluationController.php  # API d'évaluation
│   ├── DossierController.php          # Gestion des dossiers
│   └── AvisController.php             # Gestion des avis
├── Filament/Resources/    # Interface d'administration
│   ├── DossierResource.php           # Gestion des dossiers
│   ├── UserResource.php              # Gestion des utilisateurs
│   ├── AvisResource.php              # Gestion des avis
│   └── EvaluationIAResource.php      # Visualisation des évaluations IA
└── Services/              # Services métier
    ├── CreditEvaluationService.php   # Service d'évaluation PHP
    ├── predict_dossier.py           # Script de prédiction Python
    ├── train_models.py              # Entraînement des modèles IA
    └── models/                      # Modèles ML sauvegardés
```

## 🚀 Installation et Configuration

### Prérequis

- PHP 8.2 ou supérieur
- Composer
- Node.js et npm
- Python 3.8+ avec pip
- SQLite ou MySQL

### Installation

1. **Cloner le projet et installer les dépendances**
   ```bash
   git clone <repository-url>
   cd <project-directory>
   composer install
   npm install
   ```

2. **Configuration de l'environnement**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Configuration de la base de données**
   - Éditer le fichier `.env` avec vos paramètres de base de données
   - Exécuter les migrations :
   ```bash
   php artisan migrate
   ```

4. **Installation des dépendances Python**
   ```bash
   pip install scikit-learn joblib numpy pandas
   ```

5. **Création d'un utilisateur administrateur**
   ```bash
   php artisan make:filament-user
   ```

6. **Démarrage du serveur de développement**
   ```bash
   composer run dev
   ```
   Ou individuellement :
   ```bash
   php artisan serve
   php artisan queue:listen
   npm run dev
   ```

## 📊 Fonctionnalités Principales

### 1. Gestion des Dossiers de Crédit

Le système permet de gérer des dossiers de crédit avec les informations suivantes :

#### Informations Générales
- Numéro de dossier unique
- Informations client (nom, prénom)
- Montant, durée et périodicité sollicités
- Guichet de traitement
- Code adhérent

#### Informations de l'Emprunteur
- Données personnelles (sexe, âge, adresse)
- Situation professionnelle et familiale
- Revenus et charges
- Historique de crédit

#### Informations Financières
- Bilan personnel et professionnel
- Garanties disponibles
- Compte d'exploitation de l'entreprise

### 2. Système d'Évaluation par IA

#### Fonctionnement
- **Entrée** : Données du dossier (via Excel ou saisie directe)
- **Traitement** : Modèles de machine learning (classification et régression)
- **Sortie** : Score de risque, montant et durée recommandés

#### Modèles Utilisés
- **Classificateur** : Détermine si le crédit doit être accordé
- **Régresseur Montant** : Prédit le montant optimal
- **Régresseur Durée** : Prédit la durée optimale

#### Métriques Calculées
- Ratio d'endettement
- Ratio de garanties
- Ratio de rentabilité

### 3. Workflow de Validation

#### Statuts des Dossiers
- **CHARGE** : Dossier en cours de traitement
- **PROVISOIREMENT_VALIDER** : Tous les avis sont favorables
- **AJOURNER** : Au moins un avis défavorable
- **VALIDER** : Validation définitive (après 7 jours)
- **REJETER** : Rejet définitif

#### Processus d'Avis
1. Chaque dossier nécessite 5 avis d'évaluateurs
2. Types d'avis : FAVORABLE ou NON_FAVORABLE
3. Mise à jour automatique du statut selon les règles :
   - 5 avis favorables → PROVISOIREMENT_VALIDER
   - 5 avis défavorables → REJETER
   - Au moins 1 avis défavorable → AJOURNER

#### Règles de Délai
- **Validation automatique** : Après 7 jours pour les dossiers provisoirement validés
- **Rejet automatique** : Après 14 jours pour les dossiers ajournés

### 4. Interface d'Administration (Filament)

#### Tableaux de Bord
- Vue d'ensemble des dossiers
- Statistiques de performance
- Suivi des évaluations IA

#### Gestion des Ressources
- **Dossiers** : CRUD complet avec validation
- **Utilisateurs** : Gestion des comptes et rôles
- **Avis** : Suivi des évaluations
- **Évaluations IA** : Historique et métriques

#### Système de Permissions
- **Rôles** : Admin, Évaluateur, Superviseur
- **Permissions** : Granulaires par fonctionnalité
- **Profils** : Configuration par type d'utilisateur

## 🔌 API Endpoints

### Routes Web
- `GET /` : Page d'accueil
- `DELETE /avis/{avis}` : Suppression d'un avis
- `POST /dossiers/{dossier}/evaluate` : Évaluation d'un dossier
- `GET /up` : Healthcheck

### API REST
- `POST /api/credit-evaluation/evaluate-excel` : Évaluation via fichier Excel
- `POST /api/credit-evaluation/evaluate-data` : Évaluation via données JSON
- `GET /api/dossiers` : Liste des dossiers

### Paramètres API

#### Évaluation via Excel
```json
{
  "excel_file": "fichier.xlsx",
  "dossier_id": 123
}
```

#### Évaluation via Données
```json
{
  "dossier_id": 123
}
```

## 🗄️ Base de Données

### Tables Principales

#### `dossiers`
Table centrale contenant toutes les informations des dossiers de crédit :
- Informations client et projet
- Données financières
- Résultats d'évaluation IA
- Statut et historique

#### `avis`
Système d'évaluation humaine :
- Référence au dossier et utilisateur
- Type d'avis (FAVORABLE/NON_FAVORABLE)
- Observations

#### `evaluations_ia`
Historique des évaluations par IA :
- Scores et prédictions
- Ratios calculés
- Features utilisées

#### `users`, `roles`, `permissions`
Système de gestion des utilisateurs et permissions.

## 🤖 Intelligence Artificielle

### Pipeline d'Évaluation

1. **Préparation des Features**
   - Extraction des données numériques
   - Calcul de l'âge et conversion du sexe
   - Nettoyage et validation des données

2. **Prédiction**
   - Normalisation des features
   - Classification (accord/refus)
   - Régression (montant et durée)

3. **Post-traitement**
   - Calcul des ratios financiers
   - Sauvegarde des résultats
   - Mise à jour du dossier

### Features Utilisées

- **Démographiques** : âge, sexe, personnes à charge
- **Financières** : revenus, charges, actifs, passifs
- **Historique** : crédits antérieurs, respect des échéanciers
- **Projet** : coût, besoin de financement, garanties
- **Entreprise** : ventes, surplus, encaisse

## 🔧 Scripts de Maintenance

### Entraînement des Modèles
```bash
python app/Services/train_models.py
```

### Test des Modèles
```bash
python app/Services/test_models.py
```

### Export des Données
```bash
python app/Services/export_dossiers.py
```

### Génération de Données de Test
```bash
python app/Services/generate_test_data.py
```

## 📈 Monitoring et Logs

### Logs Laravel
- Erreurs d'évaluation dans `storage/logs/laravel.log`
- Suivi des prédictions et features

### Commandes Utiles
```bash
# Voir les logs en temps réel
php artisan pail

# Nettoyer le cache
php artisan config:clear
php artisan cache:clear

# Lancer les tests
php artisan test
```

## 🔒 Sécurité

### Authentification
- Système de rôles et permissions
- Protection CSRF
- Validation des entrées

### Accès Filament
- Contrôle d'accès basé sur les rôles
- Interface sécurisée pour l'administration

## 🚀 Déploiement

### Environnement de Production

1. **Configuration serveur**
   ```bash
   composer install --optimize-autoloader --no-dev
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

2. **Base de données**
   ```bash
   php artisan migrate --force
   ```

3. **Permissions fichiers**
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

4. **Variables d'environnement**
   - Configurer `APP_ENV=production`
   - Définir `APP_DEBUG=false`
   - Configurer la base de données de production

## 📚 Guide d'Utilisation

### Pour les Administrateurs
1. Accéder à `/admin` avec vos identifiants
2. Gérer les utilisateurs et leurs rôles
3. Superviser les dossiers et évaluations

### Pour les Évaluateurs
1. Consulter les dossiers assignés
2. Donner des avis (FAVORABLE/NON_FAVORABLE)
3. Ajouter des observations

### Pour l'API
1. Utiliser les endpoints `/api/credit-evaluation/*`
2. Authentification via tokens Sanctum
3. Format JSON pour les échanges

## 🐛 Dépannage

### Problèmes Courants

#### Erreur Python
- Vérifier l'installation de Python et des dépendances
- Contrôler les chemins vers les modèles ML

#### Problèmes de Permissions
- Vérifier les permissions des dossiers `storage/` et `bootstrap/cache/`
- S'assurer que l'utilisateur web peut écrire dans ces dossiers

#### Erreurs de Base de Données
- Vérifier la configuration dans `.env`
- Exécuter `php artisan migrate:status`

## 📞 Support

Pour toute question ou problème :
1. Consulter les logs dans `storage/logs/`
2. Vérifier la configuration dans `.env`
3. Tester les endpoints API avec Postman ou curl

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier LICENSE pour plus de détails.
