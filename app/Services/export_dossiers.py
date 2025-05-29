import pandas as pd
from datetime import datetime
import os
import requests
import json

def export_dossiers_to_excel():
    """Exporte les dossiers de la base de données vers un fichier Excel"""
    try:
        # Récupérer les dossiers via l'API
        response = requests.get('http://localhost:8000/api/dossiers')
        dossiers = response.json()
        
        # Convertir en DataFrame
        data = []
        for dossier in dossiers:
            # Calculer les ratios
            total_revenus = float(dossier.get('total_revenus', 0))
            total_depenses = float(dossier.get('total_depenses', 0))
            besoin_financement = float(dossier.get('besoin_financement', 0))
            total_garanties = float(dossier.get('total_garanties', 0))
            vente = float(dossier.get('vente', 0))
            surplus_net = float(dossier.get('surplus_net', 0))
            
            ratio_endettement = total_depenses / total_revenus * 100 if total_revenus else 0
            ratio_garanties = total_garanties / besoin_financement * 100 if besoin_financement else 0
            ratio_rentabilite = surplus_net / vente * 100 if vente else 0
            
            # Ajouter les données
            data.append({
                # Informations personnelles
                'date_naissance': dossier.get('date_naissance'),
                'sexe': dossier.get('sexe'),
                'personnes_charge': dossier.get('personnes_charge'),
                'residence_depuis': dossier.get('residence_depuis'),
                
                # Informations financières
                'salaire_net': dossier.get('salaire_net'),
                'total_revenus': total_revenus,
                'total_depenses': total_depenses,
                'actif_net': dossier.get('actif_net'),
                
                # Historique crédit
                'total_credits': dossier.get('total_credits'),
                
                # Projet
                'cout_total_projet': dossier.get('cout_total_projet'),
                'besoin_financement': besoin_financement,
                
                # Garanties
                'total_garanties': total_garanties,
                
                # Bilan entreprise
                'encaisse': dossier.get('encaisse'),
                'total_actif': dossier.get('total_actif'),
                'total_passif': dossier.get('total_passif'),
                
                # Compte d'exploitation
                'vente': vente,
                'surplus_net': surplus_net,
                
                # Ratios calculés
                'ratio_endettement': ratio_endettement,
                'ratio_garanties': ratio_garanties,
                'ratio_rentabilite': ratio_rentabilite,
                
                # Résultats
                'statut': dossier.get('statut_ia'),
                'montant_accorde': dossier.get('montant_predit'),
                'duree_accordee': dossier.get('duree_predite')
            })
        
        # Créer le DataFrame
        df = pd.DataFrame(data)
        
        # Créer le dossier s'il n'existe pas
        os.makedirs('data', exist_ok=True)
        
        # Sauvegarder dans un fichier Excel
        output_file = 'data/dossiers_reels.xlsx'
        df.to_excel(output_file, index=False)
        print(f"Données exportées avec succès vers {output_file}")
        
    except Exception as e:
        print(f"Erreur lors de l'export des données : {str(e)}")
        raise

if __name__ == "__main__":
    export_dossiers_to_excel() 