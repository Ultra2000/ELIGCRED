#!/usr/bin/env python3

import sys
import json
import joblib
import numpy as np
from pathlib import Path
import os
import traceback

def load_models():
    """Charge les modèles depuis le dossier storage/app/models/"""
    # Obtenir le chemin absolu du répertoire du projet
    base_path = Path(os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__)))))
    model_path = base_path / 'storage' / 'app' / 'models'
    
    classifier = joblib.load(model_path / 'credit_classifier.joblib')
    montant_regressor = joblib.load(model_path / 'montant_regressor.joblib')
    duree_regressor = joblib.load(model_path / 'duree_regressor.joblib')
    feature_scaler = joblib.load(model_path / 'feature_scaler.joblib')
    
    return classifier, montant_regressor, duree_regressor, feature_scaler

def main():
    try:
        # Charger les modèles
        classifier, montant_regressor, duree_regressor, feature_scaler = load_models()
        
        # Lire les features depuis stdin
        features_json = sys.stdin.read()
        features = json.loads(features_json)
        
        # Convertir les features en array numpy
        X = np.array([[
            features['age'],
            features['sexe'],
            features['personnes_charge'],
            features['residence_depuis'],
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
            features['nombre_credits_anterieurs'],
            features['montant_moyen_credits_anterieurs'],
            features['echeancier_respecte']
        ]])
        
        # Normaliser les features
        X_scaled = feature_scaler.transform(X)
        
        # Faire les prédictions
        prediction_proba = classifier.predict_proba(X_scaled)[0][1]
        montant_pred = montant_regressor.predict(X_scaled)[0]
        duree_pred = duree_regressor.predict(X_scaled)[0]
        
        # Calculer le score de confiance
        score = calculate_confidence_score(X_scaled[0], prediction_proba)
        
        # Préparer le résultat
        result = {
            'success': True,
            'score': score,
            'prediction': int(prediction_proba > 0.7),
            'montant': float(montant_pred),
            'duree': int(duree_pred)
        }
        
        # Écrire le résultat en JSON sur stdout
        print(json.dumps(result))
        
    except Exception as e:
        # En cas d'erreur, retourner un message d'erreur détaillé
        traceback.print_exc(file=sys.stderr)
        error_result = {
            'success': False,
            'error': str(e)
        }
        print(json.dumps(error_result))
        sys.exit(1)

def calculate_confidence_score(features, prediction_proba):
    """Calcule le score de confiance basé sur la probabilité de prédiction et les features"""
    # Score de base basé sur la probabilité de prédiction (60% du score total)
    base_score = prediction_proba * 60
    
    # Ajustements basés sur les ratios métier (40% du score total)
    ratio_score = 0
    
    # Ratio d'endettement (idéalement < 50%)
    ratio_endettement = features[17]
    if ratio_endettement < 50:
        ratio_score += (50 - ratio_endettement) / 50 * 13.33
    
    # Ratio de garanties (idéalement > 70%)
    ratio_garanties = features[18]
    if ratio_garanties > 70:
        ratio_score += min((ratio_garanties - 70) / 30, 1) * 13.33
    
    # Ratio de rentabilité (idéalement > 15%)
    ratio_rentabilite = features[19]
    if ratio_rentabilite > 15:
        ratio_score += min((ratio_rentabilite - 15) / 15, 1) * 13.33
    
    # Score final
    final_score = base_score + ratio_score
    
    # Bonus de 30% si le nombre de crédits antérieurs est égal au nombre d'échéanciers respectés
    if features[17] == features[19]:
        final_score += 30
    
    return min(final_score, 100)

if __name__ == "__main__":
    main() 