import pandas as pd
import numpy as np
from datetime import datetime, timedelta
import random

def generate_test_data(num_samples=1000):
    """Génère des données de test fictives pour l'entraînement des modèles"""
    
    # Générer des dates de naissance (entre 18 et 65 ans)
    today = datetime.now()
    min_date = today - timedelta(days=65*365)
    max_date = today - timedelta(days=18*365)
    dates_naissance = [min_date + timedelta(days=random.randint(0, (max_date-min_date).days)) 
                      for _ in range(num_samples)]
    
    # Générer des données
    data = {
        'date_naissance': dates_naissance,
        'sexe': np.random.choice(['M', 'F'], num_samples),
        'personnes_charge': np.random.randint(0, 8, num_samples),
        'residence_depuis': np.random.randint(1, 30, num_samples),
        'salaire_net': np.random.uniform(50000, 500000, num_samples),
        'total_revenus': np.random.uniform(60000, 600000, num_samples),
        'total_depenses': np.random.uniform(30000, 300000, num_samples),
        'actif_net': np.random.uniform(100000, 1000000, num_samples),
        'total_credits': np.random.uniform(0, 200000, num_samples),
        'cout_total_projet': np.random.uniform(100000, 2000000, num_samples),
        'besoin_financement': np.random.uniform(50000, 1500000, num_samples),
        'total_garanties': np.random.uniform(0, 1000000, num_samples),
        'encaisse': np.random.uniform(10000, 200000, num_samples),
        'total_actif': np.random.uniform(200000, 2000000, num_samples),
        'total_passif': np.random.uniform(100000, 1500000, num_samples),
        'vente': np.random.uniform(100000, 3000000, num_samples),
        'surplus_net': np.random.uniform(20000, 500000, num_samples)
    }
    
    # Créer un DataFrame
    df = pd.DataFrame(data)
    
    # Calculer des ratios pour déterminer l'éligibilité
    df['ratio_endettement'] = (df['total_depenses'] / df['total_revenus'] * 100).fillna(0)
    df['ratio_garanties'] = (df['total_garanties'] / df['besoin_financement'] * 100).fillna(0)
    df['ratio_rentabilite'] = (df['surplus_net'] / df['vente'] * 100).fillna(0)
    
    # Déterminer le statut (FAVORABLE ou NON_FAVORABLE)
    conditions = [
        (df['ratio_endettement'] < 50) & 
        (df['ratio_garanties'] > 70) & 
        (df['ratio_rentabilite'] > 15) &
        (df['total_credits'] < df['total_revenus'] * 0.5)
    ]
    df['statut'] = np.where(conditions[0], 'FAVORABLE', 'NON_FAVORABLE')
    
    # Déterminer le montant accordé
    df['montant_accorde'] = np.where(
        df['statut'] == 'FAVORABLE',
        df['besoin_financement'] * np.random.uniform(0.7, 1.0, num_samples),
        0
    )
    
    # Déterminer la durée accordée
    df['duree_accordee'] = np.where(
        df['statut'] == 'FAVORABLE',
        np.random.randint(12, 60, num_samples),
        0
    )
    
    # Sauvegarder les données
    df.to_excel('test_data.xlsx', index=False)
    print(f"Données de test générées et sauvegardées dans 'test_data.xlsx'")
    
    # Afficher quelques statistiques
    print("\nStatistiques des données générées :")
    print(f"Nombre total de dossiers : {num_samples}")
    print(f"Nombre de dossiers favorables : {len(df[df['statut'] == 'FAVORABLE'])}")
    print(f"Nombre de dossiers non favorables : {len(df[df['statut'] == 'NON_FAVORABLE'])}")
    print("\nMoyennes des ratios :")
    print(f"Ratio d'endettement moyen : {df['ratio_endettement'].mean():.2f}%")
    print(f"Ratio de garanties moyen : {df['ratio_garanties'].mean():.2f}%")
    print(f"Ratio de rentabilité moyen : {df['ratio_rentabilite'].mean():.2f}%")

if __name__ == "__main__":
    generate_test_data() 