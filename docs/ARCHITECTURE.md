# Architecture Technique - Système d'Évaluation de Crédit

## 🏗️ Vue d'Ensemble de l'Architecture

Le système d'évaluation de crédit suit une architecture modulaire basée sur Laravel avec intégration d'intelligence artificielle pour l'aide à la décision.

### Diagramme d'Architecture

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Interface     │    │   API REST      │    │   Interface     │
│   Filament      │    │   Endpoints     │    │   Web           │
│   (Admin)       │    │                 │    │   (Public)      │
└─────────┬───────┘    └─────────┬───────┘    └─────────┬───────┘
          │                      │                      │
          └──────────────────────┼──────────────────────┘
                                 │
                    ┌─────────────┴─────────────┐
                    │     Laravel Framework     │
                    │   (Controllers & Routes)  │
                    └─────────────┬─────────────┘
                                 │
                    ┌─────────────┴─────────────┐
                    │     Services Layer        │
                    │  CreditEvaluationService  │
                    └─────────────┬─────────────┘
                                 │
        ┌────────────────────────┼────────────────────────┐
        │                       │                        │
┌───────┴───────┐    ┌─────────┴─────────┐    ┌─────────┴─────────┐
│   Models       │    │   Python ML       │    │   Database        │
│   (Eloquent)   │    │   Scripts         │    │   (MySQL/SQLite)  │
│                │    │                   │    │                   │
│ • Dossier      │    │ • predict_dossier │    │ • dossiers        │
│ • User         │    │ • train_models    │    │ • avis            │
│ • Avis         │    │ • test_models     │    │ • evaluations_ia  │
│ • EvaluationIA │    │                   │    │ • users           │
└────────────────┘    └───────────────────┘    └───────────────────┘
```

## 🔧 Composants Principaux

### 1. Couche Présentation

#### Interface Filament (Admin)
- **Localisation** : `/admin`
- **Fonctionnalité** : CRUD complet pour toutes les entités
- **Sécurité** : Authentification et autorisation basée sur les rôles
- **Composants** :
  - DossierResource : Gestion des dossiers
  - UserResource : Gestion des utilisateurs
  - AvisResource : Gestion des avis
  - EvaluationIAResource : Visualisation des évaluations

#### API REST
- **Localisation** : `/api/*`
- **Authentification** : Laravel Sanctum
- **Format** : JSON
- **Endpoints principaux** :
  - Évaluation de crédit
  - Gestion des dossiers

### 2. Couche Métier (Services)

#### CreditEvaluationService
**Responsabilités** :
- Orchestration du processus d'évaluation
- Communication avec les modèles Python
- Préparation et nettoyage des données
- Sauvegarde des résultats

**Méthodes principales** :
```php
// Évaluation complète d'un dossier
public function evaluateDossier(Dossier $dossier)

// Évaluation à partir d'un fichier Excel  
public function evaluateFromExcel(string $filePath)

// Évaluation à partir de données structurées
public function evaluateFromData(array $data)

// Mise à jour du dossier avec les résultats
public function updateDossierWithEvaluation(Dossier $dossier, array $results)
```

### 3. Couche Données

#### Modèles Eloquent

##### Dossier (Modèle Central)
```php
class Dossier extends Model
{
    // Relations
    public function avis()           // hasMany(Avis::class)
    public function evaluationIA()   // hasOne(EvaluationIA::class)
    
    // Méthodes métier
    public function updateStatutSelonAvis()     // Logique de validation
    public function appliquerDelaisDecision()  // Gestion des délais
    
    // Accesseurs
    public function getNombreAvisAttribute()
    public function getAvisFavorablesAttribute()
    public function getAvisDefavorablesAttribute()
}
```

##### User (Gestion des Utilisateurs)
```php
class User extends Authenticatable implements FilamentUser
{
    // Relations
    public function profile()  // belongsTo(Profile::class)
    public function roles()    // belongsToMany(Role::class)
    public function avis()     // hasMany(Avis::class)
    
    // Méthodes d'autorisation
    public function canAccessPanel(Panel $panel): bool
    public function hasRole(string $role): bool
    public function hasPermission(string $permission): bool
}
```

##### Avis (Système d'Évaluation)
```php
class Avis extends Model
{
    // Relations
    public function dossier()  // belongsTo(Dossier::class)
    public function user()     // belongsTo(User::class)
    
    // Événements
    protected static function booted()
    {
        static::created(function ($avis) {
            $avis->dossier->updateStatutSelonAvis();
        });
    }
}
```

##### EvaluationIA (Résultats IA)
```php
class EvaluationIA extends Model
{
    // Stockage des résultats de l'IA
    protected $casts = [
        'features_utilisees' => 'array',
        'score_ia' => 'decimal:2',
        // ... autres casts numériques
    ];
}
```

## 🤖 Architecture IA/ML

### Pipeline de Machine Learning

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Données       │    │   Préparation   │    │   Modèles ML    │
│   Brutes        │───▶│   Features      │───▶│   Entraînés     │
│                 │    │                 │    │                 │
│ • Excel         │    │ • Nettoyage     │    │ • Classifier    │
│ • Base données  │    │ • Normalisation │    │ • Regressors    │
│ • Saisie        │    │ • Validation    │    │ • Scaler        │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                                                        │
                                                        ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Sauvegarde    │    │   Post-         │    │   Prédictions   │
│   Résultats     │◀───│   Traitement    │◀───│                 │
│                 │    │                 │    │ • Score risque  │
│ • EvaluationIA  │    │ • Calcul ratios │    │ • Montant       │
│ • Dossier       │    │ • Validation    │    │ • Durée         │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### Modèles de Machine Learning

#### 1. Classificateur de Risque
- **Type** : Classification binaire
- **Objectif** : Prédire si le crédit doit être accordé
- **Features** : 15+ variables financières et démographiques
- **Sortie** : Score de probabilité (0-1)

#### 2. Régresseur de Montant
- **Type** : Régression
- **Objectif** : Prédire le montant optimal à accorder
- **Entrée** : Mêmes features + score de classification
- **Sortie** : Montant en devise locale

#### 3. Régresseur de Durée
- **Type** : Régression
- **Objectif** : Prédire la durée optimale du crédit
- **Entrée** : Features + score + montant prédit
- **Sortie** : Durée en mois

### Communication PHP ↔ Python

#### Mécanisme
```php
// Dans CreditEvaluationService.php
private function callPythonScript(array $features)
{
    $scriptPath = base_path('app/Services/predict_dossier.py');
    $command = sprintf('python3 %s', escapeshellarg($scriptPath));
    
    // Communication via pipes
    $process = proc_open($command, $descriptorspec, $pipes);
    fwrite($pipes[0], json_encode($features));  // Envoi des données
    $output = stream_get_contents($pipes[1]);   // Réception des résultats
    
    return json_decode($output, true);
}
```

#### Format d'Échange
```json
// Entrée (PHP → Python)
{
  "age": 35,
  "sexe": 1,
  "salaire_net": 500000,
  "total_revenus": 800000,
  "total_depenses": 400000,
  // ... autres features
}

// Sortie (Python → PHP)
{
  "success": true,
  "score": 0.85,
  "prediction": true,
  "montant": 180000,
  "duree": 24
}
```

## 🗄️ Architecture de Base de Données

### Schéma Relationnel

```sql
-- Table principale des dossiers
dossiers (
    id, numero_dossier, nom_client, prenom_client,
    montant_sollicite, duree_sollicitee, periodicite_sollicitee,
    score_ia, statut, statut_ia, 
    -- 50+ champs financiers et personnels
)

-- Système d'avis
avis (
    id, dossier_id, user_id, avis, observations, created_at
)

-- Évaluations IA détaillées
evaluations_ia (
    id, dossier_id, score_ia, statut_ia,
    montant_prediction, duree_prediction,
    ratio_endettement, ratio_garanties, ratio_rentabilite,
    features_utilisees
)

-- Gestion des utilisateurs
users (id, name, prenom, email, password, profile_id)
profiles (id, libelle, code)
roles (id, name, slug)
permissions (id, name, slug)
role_user (role_id, user_id)
permission_role (permission_id, role_id)
```

### Relations et Contraintes

```php
// Relations principales
Dossier::class
├── hasMany(Avis::class)
├── hasOne(EvaluationIA::class)
└── belongsTo(User::class, 'created_by') // optionnel

User::class
├── belongsTo(Profile::class)
├── belongsToMany(Role::class)
└── hasMany(Avis::class)

Avis::class
├── belongsTo(Dossier::class)
└── belongsTo(User::class)
```

## 🔄 Flux de Données

### 1. Création d'un Dossier

```
User Input (Filament) → Validation → Dossier::create() → Database
                                           ↓
                                    Auto-evaluation IA (optionnel)
```

### 2. Évaluation par IA

```
Trigger (API/Manual) → CreditEvaluationService
                              ↓
                    prepareFeatures(Dossier)
                              ↓
                    callPythonScript(features)
                              ↓
                    Python ML Pipeline
                              ↓
                    updateDossierWithEvaluation()
                              ↓
                    Database Update
```

### 3. Processus d'Avis

```
Evaluateur → Filament Interface → Avis::create()
                                        ↓
                                  Event: avis.created
                                        ↓
                                  updateStatutSelonAvis()
                                        ↓
                                  Mise à jour statut dossier
```

### 4. Validation Automatique

```
Scheduler (Cron) → appliquerDelaisDecision()
                           ↓
                   Vérification des délais
                           ↓
                   Mise à jour statut automatique
                           ↓
                   PROVISOIREMENT_VALIDER → VALIDER (7 jours)
                   AJOURNER → REJETER (14 jours)
```

## 🔐 Sécurité et Authentification

### Système de Rôles et Permissions

```php
// Hiérarchie des rôles
Admin
├── Toutes les permissions
└── Accès complet au système

Superviseur
├── Gestion des dossiers
├── Consultation des évaluations
└── Gestion des équipes

Evaluateur
├── Consultation des dossiers assignés
├── Création d'avis
└── Consultation des statistiques

Consultant
├── Consultation en lecture seule
└── Export de rapports
```

### Contrôle d'Accès Filament

```php
// Dans les Resources Filament
public static function canViewAny(): bool
{
    return auth()->user()->hasPermission('view_dossiers');
}

public static function canCreate(): bool
{
    return auth()->user()->hasPermission('create_dossiers');
}

public static function canEdit(Model $record): bool
{
    return auth()->user()->hasPermission('edit_dossiers') 
        && $record->statut !== 'VALIDER';
}
```

## 📊 Gestion des États

### Machine à États des Dossiers

```
CHARGE (Initial)
    ↓
[Évaluation IA]
    ↓
EN_EVALUATION
    ↓
[5 Avis collectés]
    ↓
┌─────────────────────────────────┐
│ Tous FAVORABLE?                 │
│ ├─ Oui → PROVISOIREMENT_VALIDER │
│ └─ Non → AJOURNER               │
└─────────────────────────────────┘
    ↓                    ↓
[Après 7 jours]    [Après 14 jours]
    ↓                    ↓
  VALIDER            REJETER
```

### Règles de Transition

1. **CHARGE → EN_EVALUATION** : Après évaluation IA
2. **EN_EVALUATION → PROVISOIREMENT_VALIDER** : 5 avis favorables
3. **EN_EVALUATION → AJOURNER** : Au moins 1 avis défavorable
4. **PROVISOIREMENT_VALIDER → VALIDER** : Après 7 jours
5. **AJOURNER → REJETER** : Après 14 jours

## 🧮 Algorithmes et Calculs

### Préparation des Features

```php
private function prepareFeatures(Dossier $dossier)
{
    return [
        // Démographiques
        'age' => $this->calculateAge($dossier->date_naissance),
        'sexe' => $dossier->sexe === 'M' ? 1 : 0,
        'personnes_charge' => $dossier->personnes_charge ?? 0,
        
        // Financiers
        'salaire_net' => $dossier->salaire_net ?? 0,
        'total_revenus' => $dossier->total_revenus ?? 0,
        'total_depenses' => $dossier->total_depenses ?? 0,
        
        // Ratios calculés côté PHP
        'ratio_endettement' => $this->calculateDebtRatio($dossier),
        'ratio_garanties' => $this->calculateGuaranteeRatio($dossier),
        'ratio_rentabilite' => $this->calculateProfitabilityRatio($dossier),
    ];
}
```

### Calculs de Ratios

```php
// Ratio d'endettement
private function calculateDebtRatio(Dossier $dossier): float
{
    return $dossier->total_revenus > 0 
        ? ($dossier->total_depenses / $dossier->total_revenus * 100)
        : 0;
}

// Ratio de garanties
private function calculateGuaranteeRatio(Dossier $dossier): float
{
    return $dossier->besoin_financement > 0 
        ? ($dossier->total_garanties / $dossier->besoin_financement * 100)
        : 0;
}

// Ratio de rentabilité
private function calculateProfitabilityRatio(Dossier $dossier): float
{
    return $dossier->vente > 0 
        ? ($dossier->surplus_net / $dossier->vente * 100)
        : 0;
}
```

## 🔄 Patterns et Bonnes Pratiques

### 1. Repository Pattern (Optionnel)

Pour une architecture plus propre, vous pourriez implémenter :

```php
interface DossierRepositoryInterface
{
    public function findByNumero(string $numero): ?Dossier;
    public function findPendingEvaluation(): Collection;
    public function findByStatut(string $statut): Collection;
}

class DossierRepository implements DossierRepositoryInterface
{
    public function findByNumero(string $numero): ?Dossier
    {
        return Dossier::where('numero_dossier', $numero)->first();
    }
    
    // ... autres méthodes
}
```

### 2. Event-Driven Architecture

```php
// Événements métier
class DossierEvaluated
{
    public function __construct(
        public Dossier $dossier,
        public array $evaluationResults
    ) {}
}

class AvisCreated
{
    public function __construct(
        public Avis $avis,
        public Dossier $dossier
    ) {}
}

// Listeners
class UpdateDossierStatut
{
    public function handle(AvisCreated $event)
    {
        $event->dossier->updateStatutSelonAvis();
    }
}
```

### 3. Service Container et Injection

```php
// Dans AppServiceProvider
public function register()
{
    $this->app->bind(CreditEvaluationService::class, function ($app) {
        return new CreditEvaluationService(
            pythonPath: config('ml.python_path'),
            modelsPath: storage_path('app/models')
        );
    });
}
```

## 🧪 Architecture de Test

### Structure des Tests

```
tests/
├── Feature/                    # Tests d'intégration
│   ├── CreditEvaluationTest.php
│   ├── DossierManagementTest.php
│   └── ApiEndpointsTest.php
├── Unit/                       # Tests unitaires
│   ├── Models/
│   │   ├── DossierTest.php
│   │   └── UserTest.php
│   └── Services/
│       └── CreditEvaluationServiceTest.php
└── TestCase.php               # Classe de base
```

### Stratégie de Test

```php
// Test d'évaluation IA
class CreditEvaluationTest extends TestCase
{
    public function test_evaluation_with_valid_data()
    {
        // Arrange
        $dossier = Dossier::factory()->create();
        
        // Act
        $result = $this->service->evaluateDossier($dossier);
        
        // Assert
        $this->assertArrayHasKey('score', $result);
        $this->assertBetween(0, 1, $result['score']);
    }
}
```

## 📈 Performance et Optimisation

### Stratégies de Cache

```php
// Cache des évaluations IA
public function getCachedEvaluation(Dossier $dossier)
{
    return Cache::remember(
        "evaluation.{$dossier->id}",
        now()->addHours(24),
        fn() => $this->evaluateDossier($dossier)
    );
}

// Cache des statistiques
public function getDashboardStats()
{
    return Cache::remember(
        'dashboard.stats',
        now()->addMinutes(15),
        fn() => $this->calculateStats()
    );
}
```

### Optimisation des Requêtes

```php
// Eager Loading pour éviter N+1
public function getDossiersWithRelations()
{
    return Dossier::with(['avis.user', 'evaluationIA'])
                  ->paginate(20);
}

// Index de base de données
Schema::table('dossiers', function (Blueprint $table) {
    $table->index(['statut', 'created_at']);
    $table->index('numero_dossier');
});
```

### Queue Processing

```php
// Jobs asynchrones pour les évaluations longues
class EvaluateDossierJob implements ShouldQueue
{
    public function handle(CreditEvaluationService $service)
    {
        $service->evaluateDossier($this->dossier);
    }
}

// Dispatch du job
EvaluateDossierJob::dispatch($dossier);
```

## 🔧 Configuration et Environnements

### Configuration Modulaire

```php
// config/ml.php
return [
    'python_path' => env('ML_PYTHON_PATH', 'python3'),
    'models_path' => env('ML_MODELS_PATH', storage_path('app/models')),
    'timeout' => env('ML_TIMEOUT', 30),
    'cache_duration' => env('ML_CACHE_DURATION', 3600),
];

// config/credit.php
return [
    'evaluation_rules' => [
        'required_avis_count' => 5,
        'validation_delay_days' => 7,
        'rejection_delay_days' => 14,
    ],
    'thresholds' => [
        'min_score' => 0.6,
        'max_debt_ratio' => 60,
        'min_guarantee_ratio' => 80,
    ],
];
```

### Environnements

#### Développement
```env
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=sqlite
ML_PYTHON_PATH=python3
ML_CACHE_DURATION=0  # Pas de cache en dev
```

#### Test
```env
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
QUEUE_CONNECTION=sync
ML_PYTHON_PATH=python3
```

#### Production
```env
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
ML_CACHE_DURATION=3600
```

## 🚨 Gestion des Erreurs

### Hiérarchie d'Exceptions

```php
// Exceptions métier
class CreditEvaluationException extends Exception {}
class ModelNotTrainedException extends CreditEvaluationException {}
class InvalidFeatureException extends CreditEvaluationException {}
class PythonScriptException extends CreditEvaluationException {}

// Gestion globale
class Handler extends ExceptionHandler
{
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof CreditEvaluationException) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ], 422);
        }
        
        return parent::render($request, $exception);
    }
}
```

### Logging Structuré

```php
// Dans le service d'évaluation
Log::info('Début évaluation dossier', [
    'dossier_id' => $dossier->id,
    'numero_dossier' => $dossier->numero_dossier,
    'features_count' => count($features)
]);

Log::error('Erreur évaluation IA', [
    'dossier_id' => $dossier->id,
    'error' => $exception->getMessage(),
    'trace' => $exception->getTraceAsString()
]);
```

## 🔮 Extensibilité Future

### Points d'Extension

1. **Nouveaux Modèles IA**
   - Interface `MLModelInterface`
   - Factory pattern pour les modèles
   - Configuration dynamique

2. **Sources de Données Additionnelles**
   - API bancaires externes
   - Services de scoring tiers
   - Données open data

3. **Workflows Personnalisés**
   - Système de workflow configurable
   - Règles métier dynamiques
   - Intégration avec des systèmes tiers

### Architecture Microservices (Future)

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Web Service   │    │   API Gateway   │    │   ML Service    │
│   (Laravel)     │◀──▶│   (Laravel)     │◀──▶│   (Python)      │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Database      │    │   Redis Cache   │    │   ML Models     │
│   (MySQL)       │    │   & Sessions    │    │   Storage       │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

Cette architecture modulaire permet une évolution progressive vers une architecture distribuée si les besoins de performance l'exigent.