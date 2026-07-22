SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ##############################################################
-- 1. CONFIGURATION GLOBALE
-- ##############################################################
CREATE TABLE IF NOT EXISTS configurations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    logo_systeme VARCHAR(255) NULL,
    sigle_systeme VARCHAR(255) NOT NULL,
    intitule_systeme VARCHAR(255) NOT NULL,
    sigle_structure VARCHAR(255) NOT NULL,
    intitule_structure VARCHAR(255) NOT NULL,
    logo_structure VARCHAR(255) NULL,
    adresse_sociale_structure TEXT NULL,
    email_structure VARCHAR(255) NOT NULL,
    whatsapp_structure VARCHAR(255) NOT NULL,
    telephone_structure VARCHAR(255) NOT NULL,
    sigle_monnaie_pays VARCHAR(255) NOT NULL,
    sigle_devise_principale VARCHAR(255) NOT NULL,
    taux_devise_principale DECIMAL(10, 2) NOT NULL,
    mise_en_maintenance TINYINT(1) NOT NULL DEFAULT 0,
    delai_inactivite_minutes INT NOT NULL,
    nombre_session_possible INT NOT NULL,
    nombre_tentatives_connexion INT NOT NULL,
    delai_code_tp_minutes INT NOT NULL,
    delai_changement_mdp_mois INT NOT NULL,
    delai_suppression_secondes INT NOT NULL,
    code_instance_whatsapp VARCHAR(255) NULL,
    token_instance_whatsapp VARCHAR(255) NULL,
    email_notifications VARCHAR(255) NOT NULL,
    mot_de_passe_email_notifications VARCHAR(255) NOT NULL,
    smtp_email_notifications VARCHAR(255) NOT NULL,
    smtp_host_notifications VARCHAR(255) NOT NULL,
    smtp_port_notifications INT DEFAULT 587,
    smtp_encrypt_notifications VARCHAR(10) DEFAULT 'tls',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ##############################################################
-- 2. DONNÉES GÉOGRAPHIQUES
-- ##############################################################
CREATE TABLE IF NOT EXISTS regions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) UNIQUE,
    libelle VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS departements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    region_id BIGINT UNSIGNED NOT NULL,
    code VARCHAR(10) UNIQUE,
    libelle VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (region_id) REFERENCES regions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS communes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    departement_id BIGINT UNSIGNED NOT NULL,
    code VARCHAR(10) UNIQUE,
    libelle VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (departement_id) REFERENCES departements (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- CREATE TABLE IF NOT EXISTS villes (
--     id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--     commune_id BIGINT UNSIGNED NOT NULL,
--     code VARCHAR(10) UNIQUE,
--     libelle VARCHAR(100) NOT NULL,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     FOREIGN KEY (commune_id) REFERENCES communes (id) ON DELETE CASCADE
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ##############################################################
-- 3. RÉFÉRENTIELS MÉTIER
-- ##############################################################
CREATE TABLE IF NOT EXISTS secteurs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) UNIQUE,
    libelle VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS directions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    code VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS services (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    code VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NULL,
    direction_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT services_direction_id_foreign FOREIGN KEY (direction_id) REFERENCES directions (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS fonctions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    code VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NULL,
    service_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fonctions_service_id_foreign FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS guichets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) UNIQUE,
    libelle VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS branches_activite (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) UNIQUE,
    libelle VARCHAR(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pieces_identite (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) UNIQUE,
    libelle VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ##############################################################
-- 4. AGENCES & ENTREPRISES
-- ##############################################################
CREATE TABLE IF NOT EXISTS type_entreprises (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(255) NOT NULL UNIQUE,
    libelle VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS entreprises (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(50) UNIQUE,
    raison_sociale VARCHAR(200) NOT NULL,
    sigle VARCHAR(30),
    rccm VARCHAR(50) UNIQUE,
    ninea VARCHAR(50) UNIQUE,
    type_entreprise_id BIGINT UNSIGNED NULL,
    adresse TEXT,
    contact VARCHAR(30),
    email VARCHAR(100),
    region_id BIGINT UNSIGNED,
    commune_id BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (type_entreprise_id) REFERENCES type_entreprises (id),
    FOREIGN KEY (region_id) REFERENCES regions (id),
    FOREIGN KEY (commune_id) REFERENCES communes (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS type_organismes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(255) NOT NULL UNIQUE,
    libelle VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS organismes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(200) NOT NULL,
    sigle VARCHAR(50) NOT NULL,
    type BIGINT UNSIGNED NOT NULL,
    site_web VARCHAR(255) NULL,
    description TEXT NULL,
    adresse VARCHAR(255) NULL,
    telephone VARCHAR(20) NULL,
    email VARCHAR(100) NULL,
    region_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT organismes_type_foreign FOREIGN KEY (type) REFERENCES type_organismes (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT organismes_region_foreign FOREIGN KEY (region_id) REFERENCES regions (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ##############################################################
-- 5. RÔLES & PERMISSIONS
-- ##############################################################
CREATE TABLE IF NOT EXISTS roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    libelle VARCHAR(100) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id BIGINT UNSIGNED NOT NULL,
    module VARCHAR(100),
    autorise BOOLEAN,
    acces VARCHAR(255),
    full_access BOOLEAN,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ##############################################################
-- 6. PERSONNELS
-- ##############################################################
CREATE TABLE IF NOT EXISTS personnels (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    telephone VARCHAR(20),
    mot_de_passe VARCHAR(255) NOT NULL,
    type_utilisateur ENUM('interne', 'externe') DEFAULT 'interne',
    role_id BIGINT UNSIGNED NOT NULL,
    organisme_id BIGINT UNSIGNED NULL,
    statut TINYINT(1) NOT NULL DEFAULT 1,
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles (id),
    FOREIGN KEY (organisme_id) REFERENCES organismes (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ##############################################################
-- 7. JEUNES (porteurs de projet)
-- ##############################################################
CREATE TABLE IF NOT EXISTS jeunes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    sexe ENUM('MASCULIN', 'FEMININ') NOT NULL,
    date_naissance DATE NOT NULL,
    lieu_naissance VARCHAR(150),
    piece_identite_id BIGINT UNSIGNED NULL,
    numero_piece_identite VARCHAR(50) NOT NULL UNIQUE,
    photo_path VARCHAR(255),
    telephone VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(180) UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    statut TINYINT(1) DEFAULT 1,
    numero_aej VARCHAR(50) UNIQUE,
    raison_sociale VARCHAR(200),
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (piece_identite_id) REFERENCES pieces_identite (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ##############################################################
-- 8. WORKFLOWS
-- ##############################################################
CREATE TABLE IF NOT EXISTS workflows (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS workflow_versions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workflow_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    version VARCHAR(20) NOT NULL DEFAULT '2026',
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    is_default BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (workflow_id) REFERENCES workflows (id) ON DELETE CASCADE,
    UNIQUE (workflow_id, version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS workflow_etape (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workflow_version_id BIGINT UNSIGNED NOT NULL,
    parent_etape_id BIGINT UNSIGNED,
    code VARCHAR(30) NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    level SMALLINT NOT NULL DEFAULT 1,
    sequence_order INTEGER NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    valid_from DATE NOT NULL DEFAULT (CURRENT_DATE),
    valid_to DATE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (workflow_version_id) REFERENCES workflow_versions (id) ON DELETE CASCADE,
    FOREIGN KEY (parent_etape_id) REFERENCES workflow_etape (id) ON DELETE CASCADE,
    UNIQUE (workflow_version_id, code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS workflow_etape_sla (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    etape_id BIGINT UNSIGNED NOT NULL,
    duration_value INTEGER NOT NULL,
    duration_unit VARCHAR(20) NOT NULL DEFAULT 'JOURS',
    delay_type VARCHAR(20) NOT NULL DEFAULT 'FIXE',
    description TEXT,
    CHECK (duration_unit IN ('HEURES', 'JOURS', 'SEMAINES', 'MOIS')),
    CHECK (delay_type IN ('FIXE', 'RELATIF')),
    FOREIGN KEY (etape_id) REFERENCES workflow_etape (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS workflow_etape_transition (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workflow_version_id BIGINT UNSIGNED NOT NULL,
    from_etape_id BIGINT UNSIGNED NOT NULL,
    to_etape_id BIGINT UNSIGNED NOT NULL,
    transition_type VARCHAR(20) NOT NULL DEFAULT 'DEFAULT',
    sequence_order INTEGER NOT NULL DEFAULT 1,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    CHECK (from_etape_id <> to_etape_id),
    CHECK (transition_type IN ('DEFAULT', 'CONDITIONNEL')),
    FOREIGN KEY (workflow_version_id) REFERENCES workflow_versions (id) ON DELETE CASCADE,
    FOREIGN KEY (from_etape_id) REFERENCES workflow_etape (id) ON DELETE CASCADE,
    FOREIGN KEY (to_etape_id) REFERENCES workflow_etape (id) ON DELETE CASCADE,
    UNIQUE (workflow_version_id, from_etape_id, to_etape_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS decision_point (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    etape_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    FOREIGN KEY (etape_id) REFERENCES workflow_etape (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS decision_outcome (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    decision_point_id BIGINT UNSIGNED NOT NULL,
    code VARCHAR(30) NOT NULL,
    label VARCHAR(150) NOT NULL,
    next_etape_id BIGINT UNSIGNED,
    FOREIGN KEY (decision_point_id) REFERENCES decision_point (id) ON DELETE CASCADE,
    FOREIGN KEY (next_etape_id) REFERENCES workflow_etape (id),
    UNIQUE (decision_point_id, code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS deliverable_template (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    etape_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    is_mandatory BOOLEAN NOT NULL DEFAULT TRUE,
    FOREIGN KEY (etape_id) REFERENCES workflow_etape (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS workflow_etape_roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    etape_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    responsibility TEXT,
    FOREIGN KEY (etape_id) REFERENCES workflow_etape (id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE,
    UNIQUE (etape_id, role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Index sur workflow_etape
CREATE INDEX idx_etape_workflow_versions ON workflow_etape (workflow_version_id);
CREATE INDEX idx_etape_parent ON workflow_etape (parent_etape_id);
CREATE INDEX idx_transition_from ON workflow_etape_transition (from_etape_id);
CREATE INDEX idx_transition_to ON workflow_etape_transition (to_etape_id);

-- ##############################################################
-- 9. PROJETS
-- ##############################################################
CREATE TABLE IF NOT EXISTS projets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    jeune_id BIGINT UNSIGNED NOT NULL,
    secteur_id BIGINT UNSIGNED,
    guichet_id BIGINT UNSIGNED,
    organisme_id BIGINT UNSIGNED,
    workflow_version_id BIGINT UNSIGNED,
    branche_activite_id BIGINT UNSIGNED,
    titre VARCHAR(255) NOT NULL,
    type_projet ENUM('INDIVIDUEL', 'COLLECTIF') DEFAULT 'INDIVIDUEL',
    tranche_age ENUM('18_40', 'PLUS_40'),
    date_certification DATE,
    date_transmission_partenaire DATE,
    statut ENUM(
        'BROUILLON', 'A_VALIDER', 'APPROUVE', 'NON_APPROUVE',
        'EN_DECAISSEMENT', 'EN_EXPLOITATION', 'EN_REMBOURSEMENT',
        'TERMINE', 'ANNULE'
    ) DEFAULT 'BROUILLON',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (workflow_version_id) REFERENCES workflow_versions (id),
    FOREIGN KEY (jeune_id) REFERENCES jeunes (id) ON DELETE CASCADE,
    FOREIGN KEY (secteur_id) REFERENCES secteurs (id),
    FOREIGN KEY (guichet_id) REFERENCES guichets (id),
    FOREIGN KEY (organisme_id) REFERENCES organismes (id),
    FOREIGN KEY (branche_activite_id) REFERENCES branches_activite (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_projets_statut ON projets (statut);

CREATE TABLE IF NOT EXISTS budgets_projets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    projet_id BIGINT UNSIGNED NOT NULL UNIQUE,
    budget_alloue DECIMAL(15, 2) NOT NULL,
    devise VARCHAR(10) NOT NULL DEFAULT 'FCFA',
    date_debut_budget DATE,
    date_fin_budget DATE,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (projet_id) REFERENCES projets (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS depenses_projets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    projet_id BIGINT UNSIGNED NOT NULL,
    categorie ENUM(
        'MATERIEL', 'STOCK', 'SALAIRE', 'CHARGE', 'TRANSPORT', 'AUTRE'
    ) NOT NULL,
    libelle VARCHAR(200) NOT NULL,
    montant DECIMAL(15, 2) NOT NULL,
    date_depense DATE NOT NULL,
    justificatif_path VARCHAR(255),
    saisi_par BIGINT UNSIGNED,
    note TEXT,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (projet_id) REFERENCES projets (id) ON DELETE CASCADE,
    FOREIGN KEY (saisi_par) REFERENCES personnels (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS zones_intervention (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    projet_id BIGINT UNSIGNED NOT NULL,
    region_id BIGINT UNSIGNED,
    departement_id BIGINT UNSIGNED,
    commune_id BIGINT UNSIGNED,
    adresse TEXT,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projet_id) REFERENCES projets (id) ON DELETE CASCADE,
    FOREIGN KEY (region_id) REFERENCES regions (id),
    FOREIGN KEY (departement_id) REFERENCES departements (id),
    FOREIGN KEY (commune_id) REFERENCES communes (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS observations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    projet_id BIGINT UNSIGNED NOT NULL,
    auteur_id BIGINT UNSIGNED,
    observation TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projet_id) REFERENCES projets (id) ON DELETE CASCADE,
    FOREIGN KEY (auteur_id) REFERENCES personnels (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    projet_id BIGINT UNSIGNED NOT NULL,
    type_document VARCHAR(100),
    fichier VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projet_id) REFERENCES projets (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ##############################################################
-- 10. FINANCEMENTS, LOTS, DECAISSEMENTS, REMBOURSEMENTS
-- ##############################################################
CREATE TABLE IF NOT EXISTS financements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    projet_id BIGINT UNSIGNED NOT NULL,
    jeune_id BIGINT UNSIGNED NOT NULL,
    organisme_id BIGINT UNSIGNED NOT NULL,
    montant_demande DECIMAL(18, 2),
    montant_octroye DECIMAL(18, 2),
    statut ENUM('EN_ATTENTE', 'APPROUVE', 'NON_APPROUVE') DEFAULT 'EN_ATTENTE',
    date_validation DATE,
    observations TEXT,
    valide_par BIGINT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projet_id) REFERENCES projets (id) ON DELETE CASCADE,
    FOREIGN KEY (jeune_id) REFERENCES jeunes (id),
    FOREIGN KEY (organisme_id) REFERENCES organismes (id),
    FOREIGN KEY (valide_par) REFERENCES personnels (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_financements_statut ON financements (statut);

CREATE TABLE IF NOT EXISTS lots_financements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    projet_id BIGINT UNSIGNED NOT NULL,
    financement_id BIGINT UNSIGNED NOT NULL,
    code VARCHAR(50) NOT NULL,
    libelle VARCHAR(200) NOT NULL,
    montant_planifie DECIMAL(18, 2) NOT NULL,
    date_prevue DATE,
    date_reelle DATE,
    est_dernier_lot BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (projet_id) REFERENCES projets (id) ON DELETE CASCADE,
    FOREIGN KEY (financement_id) REFERENCES financements (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS decaissements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    projet_id BIGINT UNSIGNED NOT NULL,
    lot_id BIGINT UNSIGNED,
    numero_tranche INT,
    montant DECIMAL(18, 2),
    date_decaissement DATE,
    reference_banque VARCHAR(100),
    statut ENUM('EN_ATTENTE', 'VALIDE', 'NON_VALIDE') DEFAULT 'EN_ATTENTE',
    observations TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (projet_id) REFERENCES projets (id) ON DELETE CASCADE,
    FOREIGN KEY (lot_id) REFERENCES lots_financements (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS exploitations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    projet_id BIGINT UNSIGNED NOT NULL,
    date_debut_visite DATE,
    date_fin_visite DATE,
    agent_id BIGINT UNSIGNED,
    statut_projet VARCHAR(100),
    nbre_emplois INT,
    chiffre_affaires DECIMAL(18, 2),
    difficultes TEXT,
    recommandations TEXT,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    observations TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projet_id) REFERENCES projets (id) ON DELETE CASCADE,
    FOREIGN KEY (agent_id) REFERENCES personnels (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS visite_photos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    exploitation_id BIGINT UNSIGNED NOT NULL,
    photo_url VARCHAR(500) NOT NULL,
    description VARCHAR(255),
    prise_le TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    prise_par_id BIGINT UNSIGNED,
    FOREIGN KEY (exploitation_id) REFERENCES exploitations (id) ON DELETE CASCADE,
    FOREIGN KEY (prise_par_id) REFERENCES personnels (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS remboursements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    projet_id BIGINT UNSIGNED NOT NULL,
    jeune_id BIGINT UNSIGNED NOT NULL,
    financement_id BIGINT UNSIGNED NOT NULL,
    numero_echeance INT,
    date_echeance DATE,
    date_paiement DATE,
    montant_prevu DECIMAL(18, 2),
    montant_paye DECIMAL(18, 2),
    capital_amorti DECIMAL(18, 2) DEFAULT 0,
    interets DECIMAL(18, 2) DEFAULT 0,
    penalites DECIMAL(18, 2) DEFAULT 0,
    reste DECIMAL(18, 2),
    statut ENUM('EN_ATTENTE', 'PAYE', 'PARTIEL', 'NON_PAYE') DEFAULT 'NON_PAYE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projet_id) REFERENCES projets (id) ON DELETE CASCADE,
    FOREIGN KEY (jeune_id) REFERENCES jeunes (id) ON DELETE CASCADE,
    FOREIGN KEY (financement_id) REFERENCES financements (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_remboursements_statut ON remboursements (statut);
CREATE INDEX idx_remboursements_echeance ON remboursements (date_echeance);

-- ##############################################################
-- 11. INDICATEURS
-- ##############################################################
CREATE TABLE IF NOT EXISTS indicateurs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NULL,
    type_valeur VARCHAR(255) NULL,
    unite VARCHAR(255) NULL,
    statut TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS indicateurs_suivi (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    indicateur_id BIGINT UNSIGNED NOT NULL,
    projet_id BIGINT UNSIGNED NOT NULL,
    valeur VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (indicateur_id) REFERENCES indicateurs (id) ON DELETE CASCADE,
    FOREIGN KEY (projet_id) REFERENCES projets (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS suivis (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    jeune_id BIGINT UNSIGNED NOT NULL,
    intitule VARCHAR(200) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (jeune_id) REFERENCES jeunes (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS type_emplois (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(255) NOT NULL UNIQUE,
    libelle VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS embauches (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    jeune_id BIGINT UNSIGNED NOT NULL,
    entreprise_id BIGINT UNSIGNED NOT NULL,
    type_emploi_id BIGINT UNSIGNED NULL,
    poste VARCHAR(200) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (jeune_id) REFERENCES jeunes (id) ON DELETE CASCADE,
    FOREIGN KEY (entreprise_id) REFERENCES entreprises (id) ON DELETE CASCADE,
    FOREIGN KEY (type_emploi_id) REFERENCES type_emplois (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    personnel_id BIGINT UNSIGNED NOT NULL,
    titre VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    lue TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (personnel_id) REFERENCES personnels (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ##############################################################
-- 12. FORMULAIRES
-- ##############################################################
CREATE TABLE IF NOT EXISTS formulaires_evaluation (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    libelle VARCHAR(200) NOT NULL,
    public_cible VARCHAR(50) NOT NULL,
    actif TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS questions_evaluation (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    formulaire_id BIGINT UNSIGNED NOT NULL,
    code VARCHAR(50) NOT NULL,
    libelle TEXT NOT NULL,
    type_question VARCHAR(50) NOT NULL,
    options JSON DEFAULT NULL,
    ordre SMALLINT NOT NULL DEFAULT 0,
    affichage BOOLEAN DEFAULT NULL,
    obligatoire TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_questions_formulaire FOREIGN KEY (formulaire_id) REFERENCES formulaires_evaluation (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS evaluations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    formulaire_id BIGINT UNSIGNED NOT NULL,
    cible_type VARCHAR(50) NOT NULL,
    evaluateur_id BIGINT UNSIGNED NOT NULL,
    date_evaluation DATETIME NOT NULL,
    score_global DECIMAL(5, 2) DEFAULT NULL,
    commentaire TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_evaluations_formulaire FOREIGN KEY (formulaire_id) REFERENCES formulaires_evaluation (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_evaluations_evaluateur FOREIGN KEY (evaluateur_id) REFERENCES personnels (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reponses_evaluation (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    evaluation_id BIGINT UNSIGNED NOT NULL,
    question_id BIGINT UNSIGNED NOT NULL,
    reponse_texte TEXT DEFAULT NULL,
    INDEX idx_reponses_evaluation (evaluation_id),
    CONSTRAINT fk_reponses_evaluation FOREIGN KEY (evaluation_id) REFERENCES evaluations (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_reponses_question FOREIGN KEY (question_id) REFERENCES questions_evaluation (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ##############################################################
-- 13. INSTANCES DE WORKFLOW & HISTORIQUE
-- ##############################################################
CREATE TABLE IF NOT EXISTS workflow_instance (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    projet_id BIGINT UNSIGNED NOT NULL,
    workflow_version_id BIGINT UNSIGNED NOT NULL,
    current_etape_id BIGINT UNSIGNED,
    status VARCHAR(20) NOT NULL DEFAULT 'EN_COURS' CHECK (
        status IN ('EN_COURS', 'TERMINE', 'REJETE', 'ABANDONNE')
    ),
    started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (projet_id) REFERENCES projets (id) ON DELETE CASCADE,
    FOREIGN KEY (workflow_version_id) REFERENCES workflow_versions (id),
    FOREIGN KEY (current_etape_id) REFERENCES workflow_etape (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_workflow_instance_status ON workflow_instance (status);
CREATE INDEX idx_workflow_instance_current_etape ON workflow_instance (current_etape_id);

CREATE TABLE IF NOT EXISTS workflow_instance_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workflow_instance_id BIGINT UNSIGNED NOT NULL,
    etape_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED,
    performed_by_id BIGINT UNSIGNED,
    entered_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    exited_at TIMESTAMP NULL,
    decision_outcome_id BIGINT UNSIGNED,
    comments TEXT,
    FOREIGN KEY (workflow_instance_id) REFERENCES workflow_instance (id) ON DELETE CASCADE,
    FOREIGN KEY (etape_id) REFERENCES workflow_etape (id),
    FOREIGN KEY (role_id) REFERENCES roles (id),
    FOREIGN KEY (performed_by_id) REFERENCES personnels (id),
    FOREIGN KEY (decision_outcome_id) REFERENCES decision_outcome (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_history_instance ON workflow_instance_history (workflow_instance_id);
CREATE INDEX idx_history_etape ON workflow_instance_history (etape_id);

CREATE TABLE IF NOT EXISTS workflow_instance_document (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workflow_instance_id BIGINT UNSIGNED NOT NULL,
    deliverable_template_id BIGINT UNSIGNED,
    file_reference VARCHAR(500),
    produced_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    produced_by_id BIGINT UNSIGNED,
    FOREIGN KEY (workflow_instance_id) REFERENCES workflow_instance (id) ON DELETE CASCADE,
    FOREIGN KEY (deliverable_template_id) REFERENCES deliverable_template (id),
    FOREIGN KEY (produced_by_id) REFERENCES personnels (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_document_instance ON workflow_instance_document (workflow_instance_id);

CREATE TABLE IF NOT EXISTS workflow_instance_comment (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workflow_instance_id BIGINT UNSIGNED NOT NULL,
    etape_id BIGINT UNSIGNED NOT NULL,
    commented_by_id BIGINT UNSIGNED,
    comment TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (workflow_instance_id) REFERENCES workflow_instance (id) ON DELETE CASCADE,
    FOREIGN KEY (etape_id) REFERENCES workflow_etape (id),
    FOREIGN KEY (commented_by_id) REFERENCES personnels (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ##############################################################
-- 14. TRIGGERS (Empêcher l'auto-référencement dans workflow_etape)
-- ##############################################################
DELIMITER //

CREATE TRIGGER before_insert_workflow_etape
BEFORE INSERT ON workflow_etape
FOR EACH ROW
BEGIN
    IF NEW.parent_etape_id IS NOT NULL AND NEW.parent_etape_id = NEW.id THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Une étape ne peut pas être son propre parent';
    END IF;
END//

CREATE TRIGGER before_update_workflow_etape
BEFORE UPDATE ON workflow_etape
FOR EACH ROW
BEGIN
    IF NEW.parent_etape_id IS NOT NULL AND NEW.parent_etape_id = NEW.id THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Une étape ne peut pas être son propre parent';
    END IF;
END//

DELIMITER ;
