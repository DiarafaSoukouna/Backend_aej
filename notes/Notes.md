1. Couche Définition – Modélisation des procédures
### Table workflows
Rôle : répertoire des dispositifs opérationnels (ex. « MPE », « AGR CLASSIQUES »).
Champs clés : code unique, name, is_active pour activer/désactiver un dispositif.
Relations : une ligne peut avoir plusieurs versions (via workflow_versions).

### Table workflow_versions
Rôle : version spécifique d’un dispositif (ex. « MPE 2023 »). Une version est liée à un workflow et possède une version (année ou numéro) et un indicateur is_default.
Relations : workflow_id → workflows ; une version définit un ensemble d’étapes et de transitions.

### Table workflow_etape
Rôle : décrit les étapes (cycles et sous‑cycles) d’un workflow. Chaque étape appartient à une version.
Auto‑référencement : parent_etape_id permet de créer une arborescence (cycle racine level=1, sous‑cycles level=2, etc.).
Ordre : sequence_order détermine l’ordre d’exécution (modifiable).
Période de validité : valid_from / valid_to pour gérer l’évolution des procédures dans le temps.
Relations : workflow_version_id → workflow_versions ; parent_etape_id → workflow_etape (auto‑référence).

### Table workflow_etape_sla
Rôle : règles de délai (SLA) applicables à une étape.
Attributs : durée (duration_value), unité (duration_unit) et type (fixe ou relatif).
Relations : etape_id → workflow_etape.

### Table workflow_etape_transition
Rôle : graphe de transitions entre étapes. Pour une version donnée, définit les enchaînements possibles.
Contrôle : empêche une transition vers la même étape (CHECK from_etape_id <> to_etape_id).
Type : default ou conditional (pour les branchements conditionnels).
Relations : workflow_version_id → workflow_versions ; from_etape_id / to_etape_id → workflow_etape.
Unicité : (workflow_version_id, from_etape_id, to_etape_id) évite les doublons.

2. Couche Décision & Livrable – Points de bifurcation et documents attendus
### Table decision_point
Rôle : point de décision à l’intérieur d’une étape (par exemple, « Décision du comité de sélection »).
Relations : etape_id → workflow_etape.

### Table decision_outcome
Rôle : issues possibles d’un point de décision. Chaque issue peut rediriger vers une autre étape (next_etape_id) ou terminer le workflow (NULL).
Exemples : ACCEPTE → étape suivante, REJETE → fin, ATTENTE → retour à la même étape.
Relations : decision_point_id → decision_point ; next_etape_id → workflow_etape (optionnel).

### Table deliverable_template
Rôle : modèle de livrable exigé à une étape donnée.
Attributs : name, description, is_mandatory.
Relations : etape_id → workflow_etape.

3. Couche Acteurs & Rôles – Organisation des intervenants
### Table roles
Rôle : référentiel des rôles métier (ex. CIP, CAR, DPF).
Champs : code unique, libelle, description.

### Table personnels
Rôle : utilisateurs du système.
Champs : identité, email unique, téléphone, mot de passe, statut (actif/inactif).
Relations : role_id → roles (chaque personnel a un rôle principal).

### Table workflow_etape_roles
Rôle : association N‑N entre étapes et rôles. Précise qui intervient sur quelle étape et avec quelle responsabilité (champ responsibility).
Relations : etape_id → workflow_etape, role_id → roles ; unicité (etape_id, role_id).

4. Couche Organisationnelle et Métier – Entités porteuses de données
### Table promoteurs
Rôle : bénéficiaires ou porteurs de projet.
Champs : identité (nom, prénom, date de naissance), contacts, numéro AEJ, pièce d’identité, numéros CMU/CNPS.
Genre : ENUM (M, F, A).
Relations : type_piece_identite_id vers une table de référence (à créer).

### Table projets
Rôle : projet soumis par un promoteur, rattaché à une version de workflow.
Champs : titre, type (INDIVIDUEL/COLLECTIF), tranche d’âge, nombre de bénéficiaires/emplois, dates de certification et transmission, statut global (BROUILLON, SOUMIS, EN_COURS, TERMINE).
Relations :
promoteur_id → promoteurs
workflow_version_id → workflow_versions (le projet suit cette version)

5. Couche Exécution – Suivi des instances de workflow
### Table workflow_instance
Rôle : une instance de workflow pour un projet donné. Elle matérialise l’avancement du projet dans le processus.
Champs :
current_etape_id : étape en cours (peut être NULL si terminé ou non démarré).
status : en_cours, termine, rejete, abandonne.
started_at / completed_at : dates de début et de fin.
Relations :
projet_id → projets
workflow_version_id → workflow_versions
current_etape_id → workflow_etape

### Table workflow_instance_history
Rôle : trace toutes les étapes parcourues par l’instance, avec horodatage d’entrée/sortie. Permet de reconstituer le chemin réel, même si le modèle de workflow change ultérieurement.
Champs :
role_id : rôle qui a traité l’étape (peut être NULL).
performed_by_id : agent (personnel) ayant réalisé l’action.
decision_outcome_id : issue choisie si l’étape comportait une décision.
comments : commentaire libre.
Relations :
workflow_instance_id → workflow_instance
etape_id → workflow_etape
role_id → roles
performed_by_id → personnels
decision_outcome_id → decision_outcome

### Table workflow_instance_document
Rôle : stocke les livrables effectivement produits pour l’instance.
Champs :
deliverable_template_id : référence au modèle attendu (permet de vérifier la conformité).
file_reference : chemin/URL du fichier.
produced_at / produced_by_id : date et auteur du dépôt.
Relations :
workflow_instance_id → workflow_instance
deliverable_template_id → deliverable_template
produced_by_id → personnels

### Table workflow_instance_etape_comment
Rôle : commentaires libres attachés à une étape pour une instance donnée.
Relations :
workflow_instance_id → workflow_instance
etape_id → workflow_etape (l’étape concernée)
commented_by_id → personnels (auteur du commentaire)
Synthèse des interactions
Un workflow (dispositif) possède plusieurs versions.
Une version définit des étapes (en arborescence) et des transitions entre elles.
Les étapes peuvent avoir des SLA, des points de décision (avec issues), des modèles de livrables et des rôles assignés.
Un projet est porté par un promoteur, rattaché à une agence, et est instancié via une workflow_instance sur une version donnée.
L’exécution est tracée dans workflow_instance_history ; les documents et commentaires sont attachés à l’instance.