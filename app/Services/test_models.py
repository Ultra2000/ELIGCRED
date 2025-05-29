import pandas as pd
import numpy as np
from pathlib import Path
import joblib
from datetime import datetime

# Chemins vers les modèles et données
BASE_DIR = Path(__file__).parent.parent.parent
MODELS_DIR = BASE_DIR / 'storage' / 'app' / 'models'
TEST_FILE = BASE_DIR / 'test_data.xlsx'

def load_models():
    """Charge les modèles entraînés"""
    print("Chargement des modèles...")
    classifier = joblib.load(MODELS_DIR / 'credit_classifier.joblib')
    montant_regressor = joblib.load(MODELS_DIR / 'montant_regressor.joblib')
    duree_regressor = joblib.load(MODELS_DIR / 'duree_regressor.joblib')
    scaler = joblib.load(MODELS_DIR / 'feature_scaler.joblib')
    return classifier, montant_regressor, duree_regressor, scaler

def prepare_features(df):
    """Prépare les features pour la prédiction"""
    # Calculer l'âge
    today = pd.Timestamp(datetime.today().date())
    df['age'] = df['date_naissance'].apply(lambda d: (today - pd.to_datetime(d)).days // 365 if pd.notnull(d) else 0)
    
    # Convertir le sexe en numérique
    df['sexe'] = df['sexe'].map({'F': 0, 'M': 1}).fillna(0).astype(int)
    
    # Définir les features
    features = [
        'age', 'sexe', 'personnes_charge', 'residence_depuis', 'salaire_net',
        'total_revenus', 'total_depenses', 'actif_net', 'total_credits', 'cout_total_projet',
        'besoin_financement', 'total_garanties', 'encaisse', 'total_actif', 'total_passif',
        'vente', 'surplus_net', 'ratio_endettement', 'ratio_garanties', 'ratio_rentabilite'
    ]
    
    return df[features].values

def main():
    # Charger les données de test
    print("Chargement des données de test...")
    df = pd.read_excel(TEST_FILE)
    
    # Charger les modèles
    classifier, montant_regressor, duree_regressor, scaler = load_models()
    
    # Préparer les features
    X = prepare_features(df)
    X_scaled = scaler.transform(X)
    
    # Faire les prédictions
    print("\nFaisons quelques prédictions sur les 5 premiers dossiers :")
    for i in range(min(5, len(df))):
        print(f"\nDossier {i+1}:")
        
        # Prédiction du statut
        statut_pred = classifier.predict(X_scaled[i:i+1])[0]
        print(f"Statut prédit: {'FAVORABLE' if statut_pred == 1 else 'DEFAVORABLE'}")
        
        # Prédiction du montant
        montant_pred = montant_regressor.predict(X_scaled[i:i+1])[0]
        print(f"Montant prédit: {montant_pred:,.0f} FCFA")
        
        # Prédiction de la durée
        duree_pred = duree_regressor.predict(X_scaled[i:i+1])[0]
        print(f"Durée prédite: {duree_pred:.0f} mois")
        
        # Valeurs réelles pour comparaison
        print(f"Statut réel: {df['statut'].iloc[i]}")
        print(f"Montant réel: {df['montant_accorde'].iloc[i]:,.0f} FCFA")
        print(f"Durée réelle: {df['duree_accordee'].iloc[i]} mois")

if __name__ == "__main__":
    main() 