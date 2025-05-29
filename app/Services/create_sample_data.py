import pandas as pd
import numpy as np
from datetime import datetime, timedelta
import random

def generate_sample_data(n_samples=100):
    """Génère des données d'exemple pour l'entraînement des modèles"""
    data = {
        # Informations personnelles
        'date_naissance': [datetime.now() - timedelta(days=random.randint(365*18, 365*65)) for _ in range(n_samples)],
        'sexe': [random.choice(['M', 'F']) for _ in range(n_samples)],
        'personnes_charge': [random.randint(0, 8) for _ in range(n_samples)],
        'residence_depuis': [random.randint(1, 30) for _ in range(n_samples)],
        
        # Informations financières
        'salaire_net': [random.uniform(50000, 500000) for _ in range(n_samples)],
        'total_revenus': [random.uniform(100000, 1000000) for _ in range(n_samples)],
        'total_depenses': [random.uniform(50000, 500000) for _ in range(n_samples)],
        'actif_net': [random.uniform(100000, 2000000) for _ in range(n_samples)],
        
        # Historique crédit
        'total_credits': [random.uniform(0, 1000000) for _ in range(n_samples)],
        
        # Projet
        'cout_total_projet': [random.uniform(100000, 5000000) for _ in range(n_samples)],
        'besoin_financement': [random.uniform(50000, 2000000) for _ in range(n_samples)],
        
        # Garanties
        'total_garanties': [random.uniform(0, 3000000) for _ in range(n_samples)],
        
        # Bilan entreprise
        'encaisse': [random.uniform(10000, 500000) for _ in range(n_samples)],
        'total_actif': [random.uniform(200000, 5000000) for _ in range(n_samples)],
        'total_passif': [random.uniform(100000, 3000000) for _ in range(n_samples)],
        
        # Compte d'exploitation
        'vente': [random.uniform(200000, 10000000) for _ in range(n_samples)],
        'surplus_net': [random.uniform(50000, 2000000) for _ in range(n_samples)],
    }
    
    # Créer le DataFrame
    df = pd.DataFrame(data)
    
    # Calculer les ratios
    df['ratio_endettement'] = df['total_depenses'] / df['total_revenus'] * 100
    df['ratio_garanties'] = df['total_garanties'] / df['besoin_financement'] * 100
    df['ratio_rentabilite'] = df['surplus_net'] / df['vente'] * 100
    
    # Déterminer le statut en fonction des ratios
    df['statut'] = df.apply(lambda row: 'FAVORABLE' if (
        row['ratio_endettement'] < 50 and
        row['ratio_garanties'] > 70 and
        row['ratio_rentabilite'] > 15
    ) else 'NON_FAVORABLE', axis=1)
    
    # Calculer le montant accordé (80% du besoin de financement si favorable)
    df['montant_accorde'] = df.apply(
        lambda row: row['besoin_financement'] * 0.8 if row['statut'] == 'FAVORABLE' else 0,
        axis=1
    )
    
    # Calculer la durée accordée (entre 12 et 60 mois si favorable)
    df['duree_accordee'] = df.apply(
        lambda row: random.randint(12, 60) if row['statut'] == 'FAVORABLE' else 0,
        axis=1
    )
    
    return df

if __name__ == "__main__":
    # Générer les données
    df = generate_sample_data(100)
    
    # Sauvegarder dans un fichier Excel
    df.to_excel("sample_data.xlsx", index=False)
    print("Données d'exemple générées et sauvegardées dans sample_data.xlsx") 