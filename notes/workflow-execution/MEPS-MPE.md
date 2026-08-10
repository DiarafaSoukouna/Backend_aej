
### MODEL MEPS & MPE
> ETAPE_01: Récuperation des dossiers
*Impact: DOSSIER_RECUPERE
*Acteurs: SDRF
*Actions: Récupérer les dossiers
*Documents: Pas de documents

> ETAPE_02:  Transmission au partenaire financier
*Impact: TRANSMIS_PARTENAIRE
*Acteurs: SDRF
*Actions: Transmettre les dossiers au partenaire financier
    - Importer le fichier de repartition (Rattacher au lot de transmission)
    - Sélectionner les dossiers à transmettre dans le lot
    - Remplir le courriel de transmission (Model a fournir par AEJ)
*Documents: Courriel de transmission

> ETAPE_03: Imputation des dossiers approuvés aux agences régionales
*Impact: IMPUTE_AGENCE
*Acteurs: CSFM
*Actions: Imputer les dossiers
    - Sélectionner l'agence régionale de l'imputation
*Documents: Pas de documents

> ETAPE_04: Mise en place du plan de décaissement
*Impact: PLAN_DECAISSEMENT_SAISI
*Acteurs: CIP
*Actions: Saisir le plan de décaissement
    - Saisir les informations du plan de décaissement
    - Joindre le fichier PDF du plan de décaissement signé par le bénéficiaire (Model a fournir pas l'AEJ)
    - Saisir les informations des lignes du plan de décaissement
    - Soumettre le plan de décaissement 
*Documents: Plan de décaissement 

> ETAPE_05_1: Validation du plan de décaissement
*Impact: EN_VALIDATION_INTERNE
*Acteurs: CAR
*Actions: Valider le plan de décaissement
    - Valider le plan de décaissement
*Documents: Pas de documents

> ETAPE_05_2: Validation du plan de décaissement
*Impact: EN_VALIDATION_INTERNE
*Acteurs: SDRF
*Actions: Valider le plan de décaissement
    - Valider le plan de décaissement
*Documents: Pas de documents

> ETAPE_05_3: Validation du plan de décaissement
*Impact: EN_VALIDATION_INTERNE
*Acteurs: SDEF
*Actions: Valider le plan de décaissement
    - Valider le plan de décaissement
*Documents: Pas de documents

> ETAPE_05_4: Validation du plan de décaissement
*Impact: EN_VALIDATION_INTERNE
*Acteurs: SDPF
*Actions: Valider le plan de décaissement
    - Valider le plan de décaissement
*Documents: Pas de documents

> ETAPE_05_5: Validation du plan de décaissement
*Impact: EN_VALIDATION_INTERNE
*Acteurs: DPF
*Actions: Valider le plan de décaissement
    - Valider le plan de décaissement
*Documents: Pas de documents

> ETAPE_06: Traitement des lignes de décaissement
*Impact: EN_FINANCEMENT
*Acteurs: PF
*Actions: Traiter les lignes de décaissement
    - Autoriser les lignes du plan de décaissement
*Documents: Borderaux

> ETAPE_07: Exécution des lignes de décaissement
*Impact: EN_DECAISSEMENT
*Acteurs: CAR / CIP
*Actions: Exécuter les lignes de décaissement
    - Saisir les informations de décaissement
    - Joindre la piece justificatif
*Documents: Justificatif de remboursement, Justificatif de recouvrement

> ETAPE_08: Remboursement
*Impact: EN_REMBOURSEMENT
*Acteurs: PF
*Actions: Suivre le remboursement
    - Cocher les lignes de remboursement
*Documents: Justificatif de remboursement

> ETAPE_09: Recouvrement (OPTIONNEL: Cas impayé)
*Impact: EN_REMBOURSEMENT
*Acteurs: CSFM
*Actions: Suivre les dossiers
    - Saisir les informations de recouvrement 
    - Uploader la pièce jointe
*Documents: Justificatif de recouvrement

> ETAPE_10: Suivi
*Impact: EN_SUIVI
*Acteurs: CIP / CAR / PF
*Actions: Suivre les dossiers
    - Saisir les informations de visite 
    - Uploader des photos
*Documents: Rapports de visite
