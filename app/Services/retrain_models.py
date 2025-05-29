import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import StandardScaler
from sklearn.ensemble import RandomForestClassifier, RandomForestRegressor
from imblearn.over_sampling import SMOTE
import joblib
from pathlib import Path
from datetime import datetime

# Chemin vers les données et modèles
BASE_DIR = Path(__file__).parent.parent.parent
DATA_FILE = BASE_DIR / 'test_data.xlsx'
MODELS_DIR = BASE_DIR / 'storage' / 'app' / 'models'

# Charger les données
print("Chargement des données...")
df = pd.read_excel(DATA_FILE)

# Calculer l'âge à partir de la date de naissance
if 'date_naissance' in df.columns:
    today = pd.Timestamp(datetime.today().date())
    df['age'] = df['date_naissance'].apply(lambda d: (today - pd.to_datetime(d)).days // 365 if pd.notnull(d) else 0)

# Convertir la colonne 'sexe' en valeurs numériques
if 'sexe' in df.columns:
    df['sexe'] = df['sexe'].map({'F': 0, 'M': 1}).fillna(0).astype(int)

# Préparer les features
features = [
    'age', 'sexe', 'personnes_charge', 'residence_depuis', 'salaire_net',
    'total_revenus', 'total_depenses', 'actif_net', 'total_credits', 'cout_total_projet',
    'besoin_financement', 'total_garanties', 'encaisse', 'total_actif', 'total_passif',
    'vente', 'surplus_net', 'ratio_endettement', 'ratio_garanties', 'ratio_rentabilite'
]

X = df[features].values
y_class = (df['statut'] == 'FAVORABLE').astype(int)  # Classification binaire
y_montant = df['montant_accorde'].values
y_duree = df['duree_accordee'].values

# Normaliser les features
scaler = StandardScaler()
X_scaled = scaler.fit_transform(X)

# Sauvegarder le scaler
joblib.dump(scaler, MODELS_DIR / 'feature_scaler.joblib')

# Rééquilibrer les données avec SMOTE
print("Rééquilibrage des données avec SMOTE...")
smote = SMOTE(random_state=42)
X_balanced, y_balanced = smote.fit_resample(X_scaled, y_class)

# Diviser les données
X_train, X_test, y_train, y_test = train_test_split(X_balanced, y_balanced, test_size=0.2, random_state=42)

# Entraîner le classificateur
print("Entraînement du classificateur...")
classifier = RandomForestClassifier(n_estimators=100, random_state=42)
classifier.fit(X_train, y_train)

# Entraîner le régresseur pour le montant
print("Entraînement du régresseur pour le montant...")
montant_regressor = RandomForestRegressor(n_estimators=100, random_state=42)
montant_regressor.fit(X_scaled, y_montant)

# Entraîner le régresseur pour la durée
print("Entraînement du régresseur pour la durée...")
duree_regressor = RandomForestRegressor(n_estimators=100, random_state=42)
duree_regressor.fit(X_scaled, y_duree)

# Sauvegarder les modèles
print("Sauvegarde des modèles...")
joblib.dump(classifier, MODELS_DIR / 'credit_classifier.joblib')
joblib.dump(montant_regressor, MODELS_DIR / 'montant_regressor.joblib')
joblib.dump(duree_regressor, MODELS_DIR / 'duree_regressor.joblib')

print("Modèles réentraînés et sauvegardés avec succès!")

# Remplacer les valeurs manquantes par 0
df['montant_sollicite'] = 0
df['duree_sollicitee'] = 0
df['age'] = 0
df['score_ia'] = 0
df['montant_propose'] = 0
df['duree_proposee'] = 0 