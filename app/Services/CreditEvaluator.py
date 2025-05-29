import pandas as pd
import numpy as np
from sklearn.preprocessing import StandardScaler
from sklearn.ensemble import RandomForestClassifier, RandomForestRegressor
import joblib
import os
from datetime import datetime

class CreditEvaluator:
    def __init__(self):
        self.model_path = os.path.join(os.path.dirname(__file__), 'models')
        self.scaler_path = os.path.join(os.path.dirname(__file__), 'scalers')
        
        # Charger les modèles et scalers
        self.load_models()
        
    def load_models(self):
        """Charge les modèles et scalers sauvegardés"""
        try:
            self.classifier = joblib.load(os.path.join(self.model_path, 'credit_classifier.joblib'))
            self.regressor_montant = joblib.load(os.path.join(self.model_path, 'montant_regressor.joblib'))
            self.regressor_duree = joblib.load(os.path.join(self.model_path, 'duree_regressor.joblib'))
            self.scaler = joblib.load(os.path.join(self.scaler_path, 'feature_scaler.joblib'))
        except Exception as e:
            print(f"Erreur lors du chargement des modèles: {str(e)}")
            raise

    def prepare_features(self, dossier_data):
        """Prépare les features à partir des données du dossier"""
        features = {
            # Informations personnelles
            'age': self.calculate_age(dossier_data.get('date_naissance')),
            'sexe': 1 if dossier_data.get('sexe') == 'M' else 0,
            'personnes_charge': dossier_data.get('personnes_charge', 0),
            'residence_depuis': dossier_data.get('residence_depuis', 0),
            
            # Informations financières
            'salaire_net': dossier_data.get('salaire_net', 0),
            'total_revenus': dossier_data.get('total_revenus', 0),
            'total_depenses': dossier_data.get('total_depenses', 0),
            'actif_net': dossier_data.get('actif_net', 0),
            
            # Historique crédit
            'total_credits': dossier_data.get('total_credits', 0),
            
            # Projet
            'cout_total_projet': dossier_data.get('cout_total_projet', 0),
            'besoin_financement': dossier_data.get('besoin_financement', 0),
            
            # Garanties
            'total_garanties': dossier_data.get('total_garanties', 0),
            
            # Bilan entreprise
            'encaisse': dossier_data.get('encaisse', 0),
            'total_actif': dossier_data.get('total_actif', 0),
            'total_passif': dossier_data.get('total_passif', 0),
            
            # Compte d'exploitation
            'vente': dossier_data.get('vente', 0),
            'surplus_net': dossier_data.get('surplus_net', 0),
            
            # Ratios calculés
            'ratio_endettement': self.calculate_ratio_endettement(dossier_data),
            'ratio_garanties': self.calculate_ratio_garanties(dossier_data),
            'ratio_rentabilite': self.calculate_ratio_rentabilite(dossier_data),
        }
        
        return pd.DataFrame([features])

    def calculate_age(self, date_naissance):
        """Calcule l'âge à partir de la date de naissance"""
        if not date_naissance:
            return 0
        try:
            birth_date = datetime.strptime(date_naissance, '%Y-%m-%d')
            today = datetime.now()
            return today.year - birth_date.year - ((today.month, today.day) < (birth_date.month, birth_date.day))
        except:
            return 0

    def calculate_ratio_endettement(self, dossier_data):
        """Calcule le ratio d'endettement"""
        total_revenus = dossier_data.get('total_revenus', 0)
        total_depenses = dossier_data.get('total_depenses', 0)
        if total_revenus == 0:
            return 0
        return (total_depenses / total_revenus) * 100

    def calculate_ratio_garanties(self, dossier_data):
        """Calcule le ratio de garanties"""
        besoin_financement = dossier_data.get('besoin_financement', 0)
        total_garanties = dossier_data.get('total_garanties', 0)
        if besoin_financement == 0:
            return 0
        return (total_garanties / besoin_financement) * 100

    def calculate_ratio_rentabilite(self, dossier_data):
        """Calcule le ratio de rentabilité"""
        vente = dossier_data.get('vente', 0)
        surplus_net = dossier_data.get('surplus_net', 0)
        if vente == 0:
            return 0
        return (surplus_net / vente) * 100

    def evaluate_dossier(self, dossier_data):
        """Évalue un dossier de crédit et retourne les prédictions"""
        try:
            # Préparer les features
            features_df = self.prepare_features(dossier_data)
            
            # Normaliser les features
            features_scaled = self.scaler.transform(features_df)
            
            # Faire les prédictions
            prediction_accord = self.classifier.predict_proba(features_scaled)[0][1]
            montant_propose = self.regressor_montant.predict(features_scaled)[0]
            duree_proposee = self.regressor_duree.predict(features_scaled)[0]
            
            # Ajuster les prédictions
            montant_propose = max(0, min(montant_propose, dossier_data.get('besoin_financement', 0)))
            duree_proposee = max(1, min(int(duree_proposee), 60))  # Limite entre 1 et 60 mois
            
            return {
                'score_ia': round(prediction_accord * 100, 2),
                'montant_propose': round(montant_propose, 2),
                'duree_proposee': int(duree_proposee),
                'avis_ia': 'FAVORABLE' if prediction_accord > 0.7 else 'NON_FAVORABLE'
            }
            
        except Exception as e:
            print(f"Erreur lors de l'évaluation du dossier: {str(e)}")
            raise

    def process_excel_file(self, excel_path):
        """Traite un fichier Excel et extrait les données pertinentes"""
        try:
            df = pd.read_excel(excel_path)
            
            # Créer un dictionnaire avec les données extraites
            dossier_data = {}
            
            # Mapping des colonnes Excel vers les champs de la base de données
            column_mapping = {
                'Date de naissance': 'date_naissance',
                'Sexe': 'sexe',
                'Personnes à charge': 'personnes_charge',
                'Résidence depuis (années)': 'residence_depuis',
                'Salaire net': 'salaire_net',
                'Total revenus': 'total_revenus',
                'Total dépenses': 'total_depenses',
                'Actif net': 'actif_net',
                'Total crédits': 'total_credits',
                'Coût total projet': 'cout_total_projet',
                'Besoin financement': 'besoin_financement',
                'Total garanties': 'total_garanties',
                'Encaisse': 'encaisse',
                'Total actif': 'total_actif',
                'Total passif': 'total_passif',
                'Vente': 'vente',
                'Surplus net': 'surplus_net'
            }
            
            # Extraire les données selon le mapping
            for excel_col, db_field in column_mapping.items():
                if excel_col in df.columns:
                    dossier_data[db_field] = df[excel_col].iloc[0]
            
            return dossier_data
            
        except Exception as e:
            print(f"Erreur lors du traitement du fichier Excel: {str(e)}")
            raise

if __name__ == "__main__":
    # Exemple d'utilisation
    evaluator = CreditEvaluator()
    
    # Exemple avec un fichier Excel
    excel_path = "chemin/vers/votre/fichier.xlsx"
    dossier_data = evaluator.process_excel_file(excel_path)
    
    # Évaluation du dossier
    resultats = evaluator.evaluate_dossier(dossier_data)
    print("Résultats de l'évaluation:", resultats) 