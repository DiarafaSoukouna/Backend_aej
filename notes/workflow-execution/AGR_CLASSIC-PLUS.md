
### MODEL AGR - CLASSIC & PLUS
> ETAPE_01: Récuperation des dossiers
* Impact: APPROUVE
* Roles: CIP
* Actions: Récupérer les dossiers
* Documents: Pas de documents

> ETAPE_02: Ajout des plans d'affaires par les agences régionales
* Impact: PLAN_AFFAIRES_AJOUTE
* Roles: CIP
* Actions: Joindre le plan d'affaires
    - Joindre le fichier du plan d'affaire (deliverables de code PLAN_AFFAIRES)
    - Commentaires (Optionnel)
    - Current_etape_code : PLAN_AFFAIRES_AJOUTE
    - Next_etape_code : PLAN_AFFAIRES_VALIDE
    - L'etape PLAN_AFFAIRES_AJOUTE avec le commentaire est ajoute l'historique de l'instance
    - Le status du micro-projet devient : PLAN_AFFAIRES_AJOUTE
* Documents: Plan d'affaires

> ETAPE_03: Validation des plans d'affaires
* Impact: PLAN_AFFAIRES_VALIDE
* Roles: CAR
* Actions: Valider les plans d'affaires
    - Valider le plan d'affaire
    - Commentaires (Optionnel)
    - Current_etape_code : PLAN_AFFAIRES_VALIDE
    - Next_etape_code : TRANSMIS_PARTENAIRE
    - L'etape PLAN_AFFAIRES_VALIDE avec le commentaire est ajoute l'historique de l'instance
    - Le status du micro-projet devient : PLAN_AFFAIRES_VALIDE
* Documents: Pas de documents

> ETAPE_04: Transmission au partenaire financier
* Impact: TRANSMIS_PARTENAIRE
* Roles: SDRF
* Actions: Transmettre les dossiers au partenaire financier
    - Importer le fichier de repartition (Rattacher au lot de transmission)
    - Selectionner les micro-projets a transmettre dans le lot
    - Remplir le courriel de transmission (Model a fournir par AEJ)
    - Creer un lot de transmission
    - Current_etape_code : TRANSMIS_PARTENAIRE
    - Next_etape_code : EN_ANALYSE_PARTENAIRE
    - L'etape TRANSMIS_PARTENAIRE avec le commentaire est ajoute l'historique de l'instance
    - Le status du micro-projet devient : TRANSMIS_PARTENAIRE
* Documents: Courriel de transmission

> ETAPE_05: Traitement des dossiers par le partenaire financier
* Impact: EN_ANALYSE_PARTENAIRE
* Roles: PF
* Actions: Traiter les dossiers
    - Saisir les informations du compte de financement avec statut (Accepté/Refusé)
    - Commentaires (Optionnel)
    - Upload / Génerer le tableau d'amortissement (Plan de remboursement: Model a fournier par l'AEJ)
    - Current_etape_code : EN_ANALYSE_PARTENAIRE
    - Next_etape_code : EN_FINANCEMENT (Si accepté) ou ANNULE (Si refusé)
    - L'etape EN_ANALYSE_PARTENAIRE avec le commentaire est ajoute l'historique de l'instance
    - Le status du micro-projet devient : EN_ANALYSE_PARTENAIRE
* Documents: Tableau d'amortissement, Contrat de prêt

> ETAPE_06: Décaissement / Remboursement / Recouvrement
* Impact: EN_FINANCEMENT
* Roles: PF / CSFM
* Actions: Suivre les dossiers
    - Saisir les informations de décaissement
    - Commentaires (Optionnel)
    - Joindre la piece justificatif
    - Cocher les lignes de remboursement
    - Saisir les informations de recouvrement 
    - Current_etape_code : EN_FINANCEMENT
    - Next_etape_code : EN_DECAISSEMENT
    - L'etape EN_DECAISSEMENT avec le commentaire est ajoute l'historique de l'instance
    - Le status du micro-projet devient : EN_FINANCEMENT
* Documents: Justificatif de remboursement, Justificatif de recouvrement

> ETAPE_06_1: Décaissement
* Impact: EN_DECAISSEMENT
* Roles: PF
* Actions: Suivre le décaissement
    - Saisir les informations de décaissement
    - Joindre la piece justificatif
    - Commentaires (Optionnel)
    - Current_etape_code : EN_DECAISSEMENT
    - Next_etape_code : EN_REMBOURSEMENT
    - L'etape EN_DECAISSEMENT avec le commentaire est ajoute l'historique de l'instance
    - Le status du micro-projet devient : EN_DECAISSEMENT
* Documents: Justificatif de remboursement 

> ETAPE_06_2: Remboursement
* Impact: EN_REMBOURSEMENT
* Roles: PF
* Actions: Suivre le remboursement
    - Cocher les lignes de remboursement
    - Commentaires (Optionnel)
    - Current_etape_code : EN_REMBOURSEMENT
    - Next_etape_code : EN_SUIVI
    - L'etape EN_REMBOURSEMENT avec le commentaire est ajoute l'historique de l'instance
    - Le status du micro-projet devient : EN_REMBOURSEMENT
* Documents: Justificatif de remboursement

> ETAPE_06_3: Recouvrement (OPTIONNEL: Cas impayé)
* Impact: EN_REMBOURSEMENT
* Roles: CSFM
* Actions: Suivre les dossiers
    - Saisir les informations de recouvrement 
    - Uploader la pièce jointe
    - Commentaires (Optionnel)
    - Current_etape_code : EN_REMBOURSEMENT
    - Next_etape_code : EN_SUIVI
    - L'etape EN_REMBOURSEMENT avec le commentaire est ajoute l'historique de l'instance
    - Le status du micro-projet devient : EN_REMBOURSEMENT
* Documents: Justificatif de recouvrement

> ETAPE_08: Suivi
* Impact: EN_SUIVI
* Roles: CIP / CAR / PF
* Actions: Suivre les dossiers
    - Saisir les informations de visite 
    - Uploader des photos
    - Commentaires (Optionnel)
    - Current_etape_code : EN_SUIVI
    - Next_etape_code : EN_SUIVI
    - L'etape EN_SUIVI avec le commentaire est ajoute l'historique de l'instance
    - Le status du micro-projet devient : EN_SUIVI
* Documents: Rapports de visite
