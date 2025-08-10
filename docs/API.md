# Documentation API - Système d'Évaluation de Crédit

## Vue d'ensemble

Cette API permet d'interagir avec le système d'évaluation de crédit. Elle offre des endpoints pour évaluer des dossiers de crédit via des fichiers Excel ou des données JSON.

## Authentification

L'API utilise Laravel Sanctum pour l'authentification. Vous devez inclure un token d'authentification dans vos requêtes.

```bash
Authorization: Bearer YOUR_TOKEN
```

## Endpoints

### 1. Évaluation via Fichier Excel

**POST** `/api/credit-evaluation/evaluate-excel`

Évalue un dossier de crédit à partir d'un fichier Excel uploadé.

#### Paramètres

| Paramètre | Type | Requis | Description |
|-----------|------|---------|-------------|
| `excel_file` | File | Oui | Fichier Excel (.xlsx, .xls) |
| `dossier_id` | Integer | Oui | ID du dossier existant |

#### Exemple de requête

```bash
curl -X POST \
  http://localhost:8000/api/credit-evaluation/evaluate-excel \
  -H 'Authorization: Bearer YOUR_TOKEN' \
  -H 'Content-Type: multipart/form-data' \
  -F 'excel_file=@dossier.xlsx' \
  -F 'dossier_id=123'
```

#### Réponse

```json
{
  "success": true,
  "message": "Dossier évalué avec succès",
  "results": {
    "score": 0.85,
    "statut": "FAVORABLE",
    "montant": 150000,
    "duree": 24
  }
}
```

### 2. Évaluation via Données JSON

**POST** `/api/credit-evaluation/evaluate-data`

Évalue un dossier de crédit à partir des données déjà présentes en base.

#### Paramètres

| Paramètre | Type | Requis | Description |
|-----------|------|---------|-------------|
| `dossier_id` | Integer | Oui | ID du dossier à évaluer |

#### Exemple de requête

```bash
curl -X POST \
  http://localhost:8000/api/credit-evaluation/evaluate-data \
  -H 'Authorization: Bearer YOUR_TOKEN' \
  -H 'Content-Type: application/json' \
  -d '{"dossier_id": 123}'
```

#### Réponse

```json
{
  "success": true,
  "message": "Dossier évalué avec succès",
  "results": {
    "score": 0.72,
    "statut": "NON_FAVORABLE",
    "montant": 100000,
    "duree": 18
  }
}
```

### 3. Liste des Dossiers

**GET** `/api/dossiers`

Récupère la liste de tous les dossiers.

#### Exemple de requête

```bash
curl -X GET \
  http://localhost:8000/api/dossiers \
  -H 'Authorization: Bearer YOUR_TOKEN'
```

#### Réponse

```json
[
  {
    "id": 1,
    "numero_dossier": "DOS-2024-001",
    "nom_client": "DUPONT",
    "prenom_client": "Jean",
    "montant_sollicite": "200000.00",
    "statut": "CHARGE",
    "score_ia": "0.85",
    "created_at": "2024-01-15T10:30:00.000000Z"
  }
]
```

## Codes d'Erreur

### Erreurs HTTP Standards

| Code | Description |
|------|-------------|
| 200 | Succès |
| 400 | Requête invalide |
| 401 | Non authentifié |
| 403 | Non autorisé |
| 404 | Ressource non trouvée |
| 422 | Erreur de validation |
| 500 | Erreur serveur |

### Erreurs Spécifiques

#### Validation des Fichiers Excel

```json
{
  "success": false,
  "message": "The excel file field is required.",
  "errors": {
    "excel_file": ["The excel file field is required."]
  }
}
```

#### Erreur d'Évaluation IA

```json
{
  "success": false,
  "message": "Erreur lors de l'évaluation du dossier: Script Python non trouvé"
}
```

## Structure des Données

### Dossier

```json
{
  "id": 1,
  "numero_dossier": "DOS-2024-001",
  "nom_client": "DUPONT",
  "prenom_client": "Jean",
  "montant_sollicite": "200000.00",
  "duree_sollicitee": 24,
  "periodicite_sollicitee": "MENSUEL",
  "score_ia": "0.85",
  "statut": "CHARGE",
  "statut_ia": "FAVORABLE",
  "montant_predit": "180000.00",
  "duree_predite": 22,
  "created_at": "2024-01-15T10:30:00.000000Z",
  "updated_at": "2024-01-15T10:30:00.000000Z"
}
```

### Évaluation IA

```json
{
  "score_ia": 0.85,
  "statut_ia": "FAVORABLE",
  "montant_prediction": 180000,
  "duree_prediction": 22,
  "ratio_endettement": 45.5,
  "ratio_garanties": 120.0,
  "ratio_rentabilite": 15.2
}
```

## Exemples d'Intégration

### JavaScript/Node.js

```javascript
const axios = require('axios');
const FormData = require('form-data');
const fs = require('fs');

// Évaluation via Excel
async function evaluateFromExcel(filePath, dossierId, token) {
  const form = new FormData();
  form.append('excel_file', fs.createReadStream(filePath));
  form.append('dossier_id', dossierId);

  try {
    const response = await axios.post(
      'http://localhost:8000/api/credit-evaluation/evaluate-excel',
      form,
      {
        headers: {
          ...form.getHeaders(),
          'Authorization': `Bearer ${token}`
        }
      }
    );
    return response.data;
  } catch (error) {
    console.error('Erreur:', error.response.data);
  }
}

// Évaluation via données
async function evaluateFromData(dossierId, token) {
  try {
    const response = await axios.post(
      'http://localhost:8000/api/credit-evaluation/evaluate-data',
      { dossier_id: dossierId },
      {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      }
    );
    return response.data;
  } catch (error) {
    console.error('Erreur:', error.response.data);
  }
}
```

### Python

```python
import requests

def evaluate_from_excel(file_path, dossier_id, token):
    url = 'http://localhost:8000/api/credit-evaluation/evaluate-excel'
    
    files = {'excel_file': open(file_path, 'rb')}
    data = {'dossier_id': dossier_id}
    headers = {'Authorization': f'Bearer {token}'}
    
    response = requests.post(url, files=files, data=data, headers=headers)
    return response.json()

def evaluate_from_data(dossier_id, token):
    url = 'http://localhost:8000/api/credit-evaluation/evaluate-data'
    
    data = {'dossier_id': dossier_id}
    headers = {
        'Authorization': f'Bearer {token}',
        'Content-Type': 'application/json'
    }
    
    response = requests.post(url, json=data, headers=headers)
    return response.json()
```

## Bonnes Pratiques

### Gestion des Erreurs

Toujours vérifier le champ `success` dans la réponse :

```javascript
if (response.success) {
  // Traiter les résultats
  console.log('Score IA:', response.results.score);
} else {
  // Gérer l'erreur
  console.error('Erreur:', response.message);
}
```

### Limites et Contraintes

- **Taille des fichiers Excel** : Maximum 10MB
- **Formats supportés** : .xlsx, .xls
- **Timeout** : 30 secondes pour l'évaluation IA
- **Rate limiting** : 60 requêtes par minute par token

### Sécurité

- Toujours utiliser HTTPS en production
- Renouveler régulièrement les tokens d'authentification
- Valider les données côté client avant envoi
- Ne jamais exposer les tokens dans les logs