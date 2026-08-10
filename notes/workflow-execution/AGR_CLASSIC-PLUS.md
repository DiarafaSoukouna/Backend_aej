
### MODEL AGR - CLASSIC & PLUS
> ETAPE_01: Récuperation des dossiers
*Impact: DOSSIER_RECUPERE
*Roles: CIP
*Actions: Récupérer les dossiers
*Documents: Pas de documents

> ETAPE_02: Ajout des plans d'affaires par les agences régionales
*Impact: PLAN_AFFAIRES_AJOUTE
*Roles: CIP
*Actions: Joindre le plan d'affaires
    - Upload du fichier
    - Commentaires
*Documents: Plan d'affaires

> ETAPE_03: Validation des plans d'affaires
*Impact: PLAN_AFFAIRES_VALIDE
*Roles: CAR
*Actions: Valider les plans d'affaires
    - Accepter / Refuser
    - Commentaires
*Documents: Pas de documents

> ETAPE_04: Transmission au partenaire financier
*Impact: TRANSMIS_PARTENAIRE
*Roles: SDRF
*Actions: Transmettre les dossiers au partenaire financier
    - Importer le fichier de repartition (Rattacher au lot de transmission)
    - Sélectionner les dossiers à transmettre dans le lot
    - Remplir le courriel de transmission (Model a fournir par AEJ)
*Documents: Courriel de transmission

> ETAPE_05: Traitement des dossiers par le partenaire financier
*Impact: EN_ANALYSE_PARTENAIRE
*Roles: PF
*Actions: Traiter les dossiers
    - Accepter / Refuser
    - Saisie des informations du compte de financement
    - Upload / Génerer le tableau d'amortissement (Plan de remboursement: Model a fournier par l'AEJ)
*Documents: Tableau d'amortissement, Contrat de pret

> ETAPE_06: Décaissement / Remboursement / Recouvrement
*Impact: EN_FINANCEMENT
*Roles: PF / CSFM
*Actions: Suivre les dossiers
    - Saisir les informations de décaissement
    - Joindre la piece justificatif
    - Cocher les lignes de remboursement
    - Saisir les informations de recouvrement 
    - Uploader des photos
*Documents: Justificatif de remboursement, Justificatif de recouvrement

> ETAPE_06_1: Décaissement
*Impact: EN_DECAISSEMENT
*Roles: PF
*Actions: Suivre le décaissement
    - Saisir les informations de décaissement
    - Joindre la piece justificatif
*Documents: Justificatif de remboursement 

> ETAPE_06_2: Remboursement
*Impact: EN_REMBOURSEMENT
*Roles: PF
*Actions: Suivre le remboursement
    - Cocher les lignes de remboursement
*Documents: Justificatif de remboursement

> ETAPE_06_3: Recouvrement (OPTIONNEL: Cas impayé)
*Impact: EN_REMBOURSEMENT
*Roles: CSFM
*Actions: Suivre les dossiers
    - Saisir les informations de recouvrement 
    - Uploader la pièce jointe
*Documents: Justificatif de recouvrement

> ETAPE_08: Suivi
*Impact: EN_SUIVI
*Roles: CIP / CAR / PF
*Actions: Suivre les dossiers
    - Saisir les informations de visite 
    - Uploader des photos
*Documents: Rapports de visite
