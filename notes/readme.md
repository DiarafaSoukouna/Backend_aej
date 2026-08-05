# Documentation du schéma — Rôle de chaque table

---

## 1. Configuration globale

### `configurations`
Table **singleton** (une seule ligne en pratique) qui centralise tous les paramètres globaux de la plateforme : identité visuelle (logo/sigle du système et de la structure), coordonnées de contact, devise et taux de change, paramètres de sécurité (délai d'inactivité, nombre de sessions/tentatives de connexion, délai de changement de mot de passe), mode maintenance, et paramètres d'intégration (WhatsApp Business, SMTP pour les notifications email).

---

## 2. Données géographiques

### `regions`
Référentiel des régions administratives (niveau géographique le plus haut).

### `departements`
Référentiel des départements, rattachés chacun à une `region`.

### `villes`
Référentiel des villes, rattachées chacune à un `departement`.

### `communes`
Référentiel des communes, rattachées à une `ville`. Contient aussi des liens optionnels vers la division régionale AEJ (`divisionregionaleaej_id`) et le guichet emploi (`guichetemploi_id`) qui couvrent cette commune, utile pour l'affectation territoriale des dossiers.

---

## 3. Référentiels métier

### `secteurs_activites`
Référentiel des grands secteurs d'activité économique (agriculture, élevage, commerce, BTP, TIC, etc.), utilisés pour qualifier les projets et le profil des jeunes.

### `sous_secteurs_activites`
Sous-catégories rattachées à un `secteur_activite`, pour une granularité plus fine (ex. « Élevage » → « Aviculture »).

### `directions`
Référentiel des directions organisationnelles de l'AEJ (ex. DPF, DAICG, DESSE...).

### `services`
Services rattachés à une `direction` (niveau organisationnel intermédiaire).

### `fonctions`
Fonctions/postes rattachés à un `service` (ex. Conseiller en Insertion Professionnelle), utilisées pour qualifier le poste occupé par un `personnel`.

---

## 4. Entreprises & organismes

### `type_entreprises`
Référentiel des types d'entreprises (ex. entreprise individuelle, SARL, GIE...).

### `entreprises`
Répertoire des entreprises (souvent les entreprises qui embauchent des jeunes, cf. table `embauches`), avec identifiants légaux (RCCM, NINEA), coordonnées et localisation.

### `type_organismes`
Référentiel des types d'organismes financiers (banque, institution de microfinance, etc.).

### `organisme_financements`
Répertoire des partenaires financiers (ex. UNACOOPEC-CI, Orange Bank) qui interviennent dans le déblocage des financements aux jeunes, avec leur type, région et coordonnées.

---

## 5. Rôles & permissions

### `roles`
Référentiel des rôles fonctionnels des acteurs du système (CIP, AC, CAR, DPF, DAICG, etc.), utilisé à la fois pour le contrôle d'accès et pour le rattachement des acteurs aux étapes de workflow.

### `permissions`
Matrice des droits d'accès par rôle et par module applicatif (`autorise`, `full_access`, `acces`), permettant de définir finement ce que chaque rôle peut faire dans le système.

---

## 6. Personnels

### `personnels`
Répertoire des utilisateurs internes/externes de la plateforme (agents AEJ, partenaires...), avec leurs identifiants de connexion, leur rôle (`role_id`) et leur fonction (`fonction_id`).

### `notifications`
Notifications applicatives adressées à un `personnel` (titre, message, statut lu/non lu), pour l'information en temps réel des agents (ex. nouvelle étape de workflow à traiter).

---

## 7. Référentiels pour jeunes

### `pieces_identite`
Référentiel des types de pièces d'identité acceptées (CNI, attestation d'identité, etc.).

### `situation_matrimoniale`
Référentiel des situations matrimoniales possibles pour un jeune (célibataire, marié...).

---

## 8. Workflows

### `workflows`
Table racine des **dispositifs opérationnels** (AGR Classique, AGR Plus, MPE, MEPS, Capital Investissement, Mentorat, Permis, Start-Up Boost), identifiés par un `code` unique.

### `workflow_versions`
Versions successives d'un même `workflow` (ex. version 2023, version 2026). Une seule version peut être marquée `is_default` par workflow, ce qui permet de faire évoluer une procédure sans casser l'historique des dossiers déjà engagés sur une version antérieure.

### `workflow_etapes`
Cœur du moteur de workflow : les étapes/cycles/sous-cycles d'une version de workflow (structure arborescente via `parent_etape_id` et `level`). Porte aussi un `impact` métier (ex. `EN_FORMATION`, `EN_FINANCEMENT`) qui reflète le statut que prend un `micro_projet` lorsqu'il se trouve à cette étape, ainsi qu'une période de validité (`valid_from`/`valid_to`).

### `workflow_etapes_sla`
Délais de traitement attendus (SLA) pour une étape : durée, unité (heures/jours/semaines/mois) et type (délai fixe ou relatif à un événement).

### `workflow_etapes_deliverable`
Modèles de livrables attendus à l'issue d'une étape (ex. « PV de présélection »), avec indication du caractère obligatoire (`is_mandatory`).

### `workflow_etapes_roles`
Table d'association qui définit quels `roles` interviennent sur une `etape` donnée et avec quelle responsabilité (texte libre).

### `workflow_etapes_decision`
Points de décision/contrôle positionnés sur une étape (ex. « Décision du comité de présélection »).

### `workflow_decision_outcome`
Issues possibles d'un point de décision (ex. `ACCEPTE`, `REJETE`, `ATTENTE`), chacune pouvant rediriger vers une étape suivante (`next_etape_id`) ou clore le parcours (`NULL`).

---

## 9. Projets

### `projets`
Entité de haut niveau représentant un projet/programme (ex. le programme AGR Classique en tant que tel), rattaché à un `secteur_activite`.

### `zones_intervention`
Zones géographiques d'intervention d'un `projet` (adresse, coordonnées GPS, `departement`), permettant de cartographier la couverture territoriale d'un programme.

---

## 10. Guichets & agences

### `guichets`
Référentiel des guichets de financement (ex. guichet AGR Classique, guichet AGR Plus), avec leurs bornes de montant (`montant_min`/`montant_max`) et leur `type`.

### `agences_regionales`
Répertoire des agences régionales de l'AEJ (localisation, contact, chef d'agence via `chef_agence_id`).

---

## 11. Dispositifs

### `dispositifs`
Déclinaison opérationnelle et budgétaire d'un `projet` (budget alloué, objectifs prévisionnels : emplois, bénéficiaires, micro-projets). C'est l'entité sur laquelle sont rattachés les `micro_projets` financés.

---

## 12. Jeunes

### `jeunes`
Fiche du porteur de projet (le bénéficiaire final) : état civil, contact, pièce d'identité, secteur/sous-secteur d'activité, situation matrimoniale, matricule AEJ. C'est l'acteur central du parcours métier (enrôlement → sélection → formation → financement → remboursement).

---

## 13. Micro projets

### `micro_projets`
Le **dossier de demande de financement** d'un jeune : rattaché à un `dispositif`, un `organisme` financeur, un `guichet`, une `agence` et un `jeune`. Le champ `statut` (ENUM) reflète l'avancement du dossier dans le workflow (soumission, analyse, formation, financement, décaissement, suivi, remboursement, terminé), en miroir du champ `impact` de `workflow_etapes`.

---

## 14. Comptes, observations, documents

### `compte_financements`
Suivi de l'ouverture du compte du bénéficiaire chez le partenaire financier pour un `micro_projet` donné, ainsi que l'avis rendu par le partenaire (`ACCORDE`, `AJOURNE`, `REJETE`).

### `observations`
Journal des observations/remarques (texte libre) déposées par un `personnel` sur un `micro_projet` au fil de son instruction.

### `documents`
Registre des documents/pièces jointes associés à un `micro_projet` (type de document + chemin du fichier).

---

## 15. Financements, lots, décaissements, remboursements

### `budgets`
Le **financement accordé** à un `micro_projet` : montant alloué, statut d'approbation, état de la convention (signature, réception de l'acte de crédit), état du déblocage. C'est la table pivot de toute la partie financière.

### `budgets_remboursements`
Modalités de remboursement attachées à un `budget` (montant à rembourser, garantie, durée, différé, dates d'échéance, ventilation capital/intérêts).

### `remboursements`
Historique des paiements effectués par le jeune sur son `budget` (montant échu vs payé vs impayé, pénalités, statut par échéance).

### `transactions`
Dépenses réalisées par le bénéficiaire dans le cadre de l'exploitation de son `micro_projet` (catégorisées : matériel, stock, salaire, charge, transport, autre), avec justificatif.

### `plan_decaissements`
Plan prévisionnel de décaissement d'un `budget` en plusieurs tranches (montant et date planifiés).

### `decaissements`
Décaissements réellement effectués, rattachés à une ligne du `plan_decaissements`, avec référence bancaire et statut de validation.

---

## 16. Exploitations, indicateurs & suivis

### `exploitations`
Compte-rendu de visite terrain d'un `micro_projet` financé : état d'installation, état de l'activité (bonne marche, en exploitation, sinistrée), géolocalisation, difficultés rencontrées et recommandations de l'agent.

### `visite_photos`
Photos prises lors d'une `exploitation` (preuve visuelle de la visite terrain).

### `indicateurs`
Référentiel des indicateurs de suivi-évaluation définis pour un `micro_projet` (nom, unité, type de valeur).

### `indicateurs_suivi`
Historique des valeurs mesurées dans le temps pour un `indicateur` donné.

### `suivis`
Journal générique de suivi d'un `jeune` sur un `micro_projet` (libellé d'événement de suivi).

### `type_emplois`
Référentiel des types d'emplois créés/occupés (ex. emploi salarié, auto-emploi).

### `embauches`
Enregistrement d'une embauche d'un `jeune` par une `entreprise`, dans le cadre d'un `micro_projet`, avec le poste occupé et le type d'emploi.

---

## 17. Formulaires & questionnaires

### `formulaires_evaluation`
Modèles de formulaires d'évaluation rattachés à un `micro_projet`, destinés à un public cible donné (ex. jeune bénéficiaire, agence).

### `questions_evaluation`
Questions composant un `formulaire_evaluation` (libellé, type de question, options JSON pour les QCM, ordre d'affichage, caractère obligatoire).

### `evaluations`
Une instance de remplissage d'un `formulaire_evaluation` par un `evaluateur` (personnel), avec un score global et un commentaire de synthèse.

### `reponses_evaluation`
Réponses détaillées apportées à chaque `question_evaluation` dans le cadre d'une `evaluation`.

---

## 18. Instances de workflow & historique

### `workflow_instance`
**L'exécution concrète** d'un workflow pour un `micro_projet` donné : quelle version de workflow est suivie, à quelle étape le dossier se trouve actuellement (`current_etape_id`), et quel est son statut global (en cours, terminé, rejeté, abandonné).

### `workflow_instance_history`
Journal d'audit complet du parcours d'une `workflow_instance` : chaque passage par une étape (entrée/sortie, rôle et personne ayant traité l'étape, décision prise le cas échéant, commentaires).

### `workflow_instance_document`
Livrables effectivement produits (fichiers) pour une `workflow_instance`, rattachés au modèle de livrable attendu (`workflow_etapes_deliverable`).

### `workflow_instance_comment`
Commentaires libres déposés sur une étape précise d'une `workflow_instance`, indépendamment de l'historique de passage (échanges/annotations entre acteurs).

---

## 19. Analyse dynamique des données

### `workflow_categories_etapes`
Table d'agrégation destinée aux tableaux de bord : associe une combinaison d'étapes au nombre de projets (`nbre_projets`) qui s'y trouvent, pour du reporting dynamique multi-étapes.

---

## 20. Triggers

### `before_insert_workflow_etape`
Déclencheur exécuté avant toute insertion dans `workflow_etapes` : empêche qu'une étape se déclare comme son propre parent (`parent_etape_id = id`), ce qui casserait l'arborescence.

### `before_update_workflow_etape`
Même contrôle que ci-dessus, mais appliqué avant toute mise à jour d'une ligne de `workflow_etapes`, pour garantir l'intégrité de l'arborescence dans le temps.