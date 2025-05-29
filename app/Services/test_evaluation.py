import joblib
import numpy as np
import os
from pathlib import Path

# Chemin vers les modèles
MODELS_DIR = Path(__file__).parent.parent.parent / 'storage' / 'app' / 'models'

# Charger les modèles
classifier = joblib.load(MODELS_DIR / 'credit_classifier.joblib')
montant_regressor = joblib.load(MODELS_DIR / 'montant_regressor.joblib')
duree_regressor = joblib.load(MODELS_DIR / 'duree_regressor.joblib')
feature_scaler = joblib.load(MODELS_DIR / 'feature_scaler.joblib')

# Données du dossier
features = {
    'montant_sollicite': 100000.0,
    'duree_sollicitee': 6,
    'salaire_net': 1500000.0,
    'total_revenus': 2000000.0,
    'total_depenses': 100000.0,
    'actif_net': 5000000.0,
    'total_credits': 0.0,
    'cout_total_projet': 200000.0,
    'besoin_financement': 100000.0,
    'total_garanties': 500000.0,
    'encaisse': 200000.0,
    'total_actif': 5000000.0,
    'total_passif': 100000.0,
    'vente': 2000000.0,
    'surplus_net': 500000.0,
    'personnes_charge': 0,
    'age': 40,
    'score_ia': 0.0,
    'montant_propose': 0.0,
    'duree_proposee': 0.0
}

# Préparer les features pour la prédiction
X = np.array([[
    features['montant_sollicite'],
    features['duree_sollicitee'],
    features['salaire_net'],
    features['total_revenus'],
    features['total_depenses'],
    features['actif_net'],
    features['total_credits'],
    features['cout_total_projet'],
    features['besoin_financement'],
    features['total_garanties'],
    features['encaisse'],
    features['total_actif'],
    features['total_passif'],
    features['vente'],
    features['surplus_net'],
    features['personnes_charge'],
    features['age'],
    features['score_ia'],
    features['montant_propose'],
    features['duree_proposee']
]])

# Normaliser les features
X_scaled = feature_scaler.transform(X)

# Faire les prédictions
score = classifier.predict_proba(X_scaled)[0][1] * 100
montant_pred = montant_regressor.predict(X_scaled)[0]
duree_pred = duree_regressor.predict(X_scaled)[0]

print(f"\nRésultats de l'évaluation IA :")
print(f"Score IA : {score:.2f}%")
print(f"Montant prédit : {montant_pred:.2f} FCFA")
print(f"Durée prédite : {duree_pred:.2f} mois")

# Déterminer le statut IA avec des seuils plus permissifs
if score >= 70:
    statut = "FAVORABLE"
elif score >= 50:
    statut = "FAVORABLE_AVEC_RESERVES"
elif score >= 20:  # Nouveau seuil pour NON_FAVORABLE
    statut = "FAVORABLE_AVEC_RESERVES"  # On considère comme favorable avec réserves
else:
    statut = "NON_FAVORABLE"

print(f"Statut IA : {statut}") 