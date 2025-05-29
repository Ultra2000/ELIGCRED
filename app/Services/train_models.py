import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import StandardScaler
from sklearn.ensemble import RandomForestClassifier, RandomForestRegressor
import joblib
import os
from datetime import datetime

class ModelTrainer:
    def __init__(self):
        self.model_path = os.path.join(os.path.dirname(__file__), 'models')
        self.scaler_path = os.path.join(os.path.dirname(__file__), 'scalers')
        
        # Créer les dossiers s'ils n'existent pas
        os.makedirs(self.model_path, exist_ok=True)
        os.makedirs(self.scaler_path, exist_ok=True)

    def prepare_features(self, df):
        """Prépare les features à partir des données"""
        features = {
            # Informations personnelles
            'age': df['date_naissance'].apply(lambda x: (datetime.now() - pd.to_datetime(x)).days / 365.25),
            'sexe': df['sexe'].apply(lambda x: 1 if x == 'M' else 0),
            'personnes_charge': df['personnes_charge'],
            'residence_depuis': df['residence_depuis'],
            
            # Informations financières
            'salaire_net': df['salaire_net'],
            'total_revenus': df['total_revenus'],
            'total_depenses': df['total_depenses'],
            'actif_net': df['actif_net'],
            
            # Historique crédit
            'total_credits': df['total_credits'],
            
            # Projet
            'cout_total_projet': df['cout_total_projet'],
            'besoin_financement': df['besoin_financement'],
            
            # Garanties
            'total_garanties': df['total_garanties'],
            
            # Bilan entreprise
            'encaisse': df['encaisse'],
            'total_actif': df['total_actif'],
            'total_passif': df['total_passif'],
            
            # Compte d'exploitation
            'vente': df['vente'],
            'surplus_net': df['surplus_net'],
            
            # Ratios calculés
            'ratio_endettement': df['total_depenses'] / df['total_revenus'] * 100,
            'ratio_garanties': df['total_garanties'] / df['besoin_financement'] * 100,
            'ratio_rentabilite': df['surplus_net'] / df['vente'] * 100,
        }
        
        return pd.DataFrame(features)

    def train_models(self, data_path):
        """Entraîne les modèles à partir des données historiques"""
        try:
            # Charger les données
            df = pd.read_excel(data_path)
            
            # Préparer les features
            X = self.prepare_features(df)
            
            # Préparer les targets
            y_accord = df['statut'].apply(lambda x: 1 if x == 'FAVORABLE' else 0)
            y_montant = df['montant_accorde']
            y_duree = df['duree_accordee']
            
            # Diviser les données
            X_train, X_test, y_accord_train, y_accord_test = train_test_split(
                X, y_accord, test_size=0.2, random_state=42
            )
            _, _, y_montant_train, y_montant_test = train_test_split(
                X, y_montant, test_size=0.2, random_state=42
            )
            _, _, y_duree_train, y_duree_test = train_test_split(
                X, y_duree, test_size=0.2, random_state=42
            )
            
            # Normaliser les features
            scaler = StandardScaler()
            X_train_scaled = scaler.fit_transform(X_train)
            X_test_scaled = scaler.transform(X_test)
            
            # Entraîner le classificateur
            classifier = RandomForestClassifier(n_estimators=100, random_state=42)
            classifier.fit(X_train_scaled, y_accord_train)
            
            # Entraîner le régresseur pour le montant
            regressor_montant = RandomForestRegressor(n_estimators=100, random_state=42)
            regressor_montant.fit(X_train_scaled, y_montant_train)
            
            # Entraîner le régresseur pour la durée
            regressor_duree = RandomForestRegressor(n_estimators=100, random_state=42)
            regressor_duree.fit(X_train_scaled, y_duree_train)
            
            # Évaluer les modèles
            print("\nÉvaluation des modèles :")
            print(f"Score du classificateur : {classifier.score(X_test_scaled, y_accord_test):.2f}")
            print(f"Score du régresseur montant : {regressor_montant.score(X_test_scaled, y_montant_test):.2f}")
            print(f"Score du régresseur durée : {regressor_duree.score(X_test_scaled, y_duree_test):.2f}")
            
            # Sauvegarder les modèles
            joblib.dump(classifier, os.path.join(self.model_path, 'credit_classifier.joblib'))
            joblib.dump(regressor_montant, os.path.join(self.model_path, 'montant_regressor.joblib'))
            joblib.dump(regressor_duree, os.path.join(self.model_path, 'duree_regressor.joblib'))
            joblib.dump(scaler, os.path.join(self.scaler_path, 'feature_scaler.joblib'))
            
            print("\nModèles entraînés et sauvegardés avec succès !")
            
        except Exception as e:
            print(f"Erreur lors de l'entraînement des modèles : {str(e)}")
            raise

if __name__ == "__main__":
    # Exemple d'utilisation
    trainer = ModelTrainer()
    trainer.train_models("sample_data.xlsx") 