SET
    NAMES utf8mb4;

SET
    FOREIGN_KEY_CHECKS = 0;

-- ##############################################################
-- 1. CONFIGURATION GLOBALE
-- ##############################################################
CREATE TABLE
    IF NOT EXISTS configurations (
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
        mise_en_maintenance TINYINT (1) NOT NULL DEFAULT 0,
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
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ##############################################################
-- 2. DONNÉES GÉOGRAPHIQUES
-- ##############################################################
CREATE TABLE
    IF NOT EXISTS regions (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) UNIQUE,
        nom VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS departements (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        region_id BIGINT UNSIGNED,
        code VARCHAR(50) UNIQUE,
        nom VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (region_id) REFERENCES regions (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Via https://agenceemploijeunes.ci/api/v1.0/load-projet-parameter
CREATE TABLE
    IF NOT EXISTS villes (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Via https://agenceemploijeunes.ci/api/v1.0/communes-old
CREATE TABLE
    IF NOT EXISTS communes (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        ville_id BIGINT UNSIGNED,
        divisionregionaleaej_id BIGINT UNSIGNED,
        guichetemploi_id BIGINT UNSIGNED,
        code VARCHAR(50) UNIQUE,
        nom VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (ville_id) REFERENCES villes (id) ON DELETE CASCADE,
        FOREIGN KEY (divisionregionaleaej_id) REFERENCES division_regionale (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Via https://agenceemploijeunes.ci/api/v1.0/load-projet-parameter
CREATE TABLE
    IF NOT EXISTS division_regionale (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) UNIQUE,
        nom VARCHAR(100) NOT NULL
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ##############################################################
-- 3. RÉFÉRENTIELS MÉTIER
-- ##############################################################
-- Via https://agenceemploijeunes.ci/api/v1.0/secteurs
CREATE TABLE
    IF NOT EXISTS secteurs_activites (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(50) UNIQUE,
        libelle VARCHAR(100) NOT NULL
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Via https://agenceemploijeunes.ci/api/v1.0/sous-secteurs
CREATE TABLE
    IF NOT EXISTS sous_secteurs_activites (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        secteur_activite_id BIGINT UNSIGNED,
        libelle VARCHAR(150) NOT NULL,
        FOREIGN KEY (secteur_activite_id) REFERENCES secteurs_activites (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Via API Indisponible
CREATE TABLE
    IF NOT EXISTS directions (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(255) NOT NULL,
        code VARCHAR(255) NOT NULL UNIQUE,
        description TEXT NULL
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Via API Indisponible
CREATE TABLE
    IF NOT EXISTS services (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(255) NOT NULL,
        code VARCHAR(255) NOT NULL UNIQUE,
        description TEXT NULL,
        direction_id BIGINT UNSIGNED,
        FOREIGN KEY (direction_id) REFERENCES directions (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Via API Indisponible
CREATE TABLE
    IF NOT EXISTS fonctions (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(255) NOT NULL,
        code VARCHAR(255) NOT NULL UNIQUE,
        description TEXT NULL,
        service_id BIGINT UNSIGNED,
        FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ##############################################################
-- 4. ENTREPRISES & ORGANISMES
-- ##############################################################
-- Via API Indisponible
CREATE TABLE
    IF NOT EXISTS type_entreprises (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(255) NOT NULL UNIQUE,
        libelle VARCHAR(255) NOT NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Via API Indisponible
CREATE TABLE
    IF NOT EXISTS entreprises (
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
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Via API Indisponible
CREATE TABLE
    IF NOT EXISTS type_organismes (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(255) NOT NULL UNIQUE,
        libelle VARCHAR(255) NOT NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Via API Indisponible
CREATE TABLE
    IF NOT EXISTS organisme_financements (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(200) NOT NULL,
        sigle VARCHAR(50) NOT NULL,
        type BIGINT UNSIGNED,
        site_web VARCHAR(255) NULL,
        description TEXT NULL,
        adresse VARCHAR(255) NULL,
        telephone VARCHAR(20) NULL,
        email VARCHAR(100) NULL,
        region_id BIGINT UNSIGNED,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        FOREIGN KEY (type) REFERENCES type_organismes (id) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (region_id) REFERENCES regions (id)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Via https://agenceemploijeunes.ci/api/v1.0/list-agence-regionale
CREATE TABLE
    IF NOT EXISTS agences_regionales (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) UNIQUE,
        nom VARCHAR(100) NOT NULL,
        latitude VARCHAR(100),
        longitude VARCHAR(100),
        contact VARCHAR(100),
        localisation VARCHAR(50),
        adresse TEXT,
        telephone VARCHAR(30),
        email VARCHAR(100),
        chef_agence_id BIGINT UNSIGNED,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (chef_agence_id) REFERENCES personnels (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ##############################################################
-- 5. RÔLES & PERMISSIONS
-- ##############################################################
CREATE TABLE
    IF NOT EXISTS roles (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) NOT NULL UNIQUE,
        libelle VARCHAR(100) NOT NULL,
        description TEXT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS permissions (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        role_id BIGINT UNSIGNED,
        module VARCHAR(100),
        autorise BOOLEAN,
        acces VARCHAR(255),
        full_access BOOLEAN,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ##############################################################
-- 6. PERSONNELS
-- ##############################################################
CREATE TABLE
    IF NOT EXISTS personnels (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        prenom VARCHAR(100) NOT NULL,
        email VARCHAR(180) NOT NULL UNIQUE,
        telephone VARCHAR(20),
        mot_de_passe VARCHAR(255) NOT NULL,
        type_utilisateur ENUM ('interne', 'externe') DEFAULT 'interne',
        role_id BIGINT UNSIGNED,
        fonction_id BIGINT UNSIGNED,
        agence_id BIGINT UNSIGNED,
        organisme_id BIGINT UNSIGNED,
        statut TINYINT (1) NOT NULL DEFAULT 1,
        remember_token VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (role_id) REFERENCES roles (id),
        FOREIGN KEY (fonction_id) REFERENCES fonctions (id),
        FOREIGN KEY (agence_id) REFERENCES agences_regionales (id),
        FOREIGN KEY (organisme_id) REFERENCES organisme_financements (id)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS notifications (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        personnel_id BIGINT UNSIGNED,
        titre VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        lue TINYINT (1) NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL,
        FOREIGN KEY (personnel_id) REFERENCES personnels (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE 
    IF NOT EXISTS tokens (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        personnel_id BIGINT UNSIGNED,
        token VARCHAR(255) NOT NULL, --Hashed
        type ENUM ('RESET', 'SETUP') NOT NULL,
        used TINYINT (1) NOT NULL DEFAULT 0, 
        expires_at DATETIME NOT NULL,
        created_at DATETIME NOT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (personnel_id) REFERENCES personnels (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE 
    IF NOT EXISTS otp_codes (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        personnel_id BIGINT UNSIGNED,
        code VARCHAR(255) NOT NULL, --Hashed
        expires_at DATETIME NOT NULL,
        used TINYINT (1) NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (personnel_id) REFERENCES personnels (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ##############################################################
-- 7. RÉFÉRENTIELS POUR JEUNES
-- ##############################################################
-- Via https://agenceemploijeunes.ci/api/v1.0/sexes
CREATE TABLE
    IF NOT EXISTS sexes (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        libelle VARCHAR(150) NOT NULL
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Via https://agenceemploijeunes.ci/api/v1.0/lieu-habitations
CREATE TABLE
    IF NOT EXISTS lieux_habitation (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(150) NOT NULL,
        ville_id BIGINT UNSIGNED,
        FOREIGN KEY (ville_id) REFERENCES villes (id)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Via https://agenceemploijeunes.ci/api/v1.0/types-pieces-identites
CREATE TABLE
    IF NOT EXISTS type_pieces_identite (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        libelle VARCHAR(100) NOT NULL,
        description TEXT,
        actif TINYINT(1) NOT NULL DEFAULT 1
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Via https://agenceemploijeunes.ci/api/v1.0/niveaux-etudes
CREATE TABLE
    IF NOT EXISTS niveau_etude (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        libelle VARCHAR(150) NOT NULL
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Via https://agenceemploijeunes.ci/api/v1.0/pays
CREATE TABLE
    IF NOT EXISTS pays (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code_iso VARCHAR(10) UNIQUE,
        nom VARCHAR(100) NOT NULL
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Via https://agenceemploijeunes.ci/api/v1.0/situations-handicaps
CREATE TABLE
    IF NOT EXISTS types_situation_handicap (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        libelle VARCHAR(100) NOT NULL
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Via https://agenceemploijeunes.ci/api/v1.0/situations-matrimoniale
CREATE TABLE
    IF NOT EXISTS situation_matrimoniale (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        libelle VARCHAR(100) NOT NULL
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ##############################################################
-- 8. WORKFLOWS
-- ##############################################################
CREATE TABLE
    IF NOT EXISTS workflows (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) NOT NULL UNIQUE,
        name VARCHAR(150) NOT NULL,
        description TEXT,
        is_active BOOLEAN NOT NULL DEFAULT TRUE,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS workflow_versions (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        workflow_code VARCHAR(50) NOT NULL,
        version VARCHAR(20) NOT NULL DEFAULT '2026',
        code VARCHAR(50) NOT NULL UNIQUE, -- GENERATE BY [workflow_code + '_' + VERSION] IF NOT EXISTS
        name VARCHAR(150) NOT NULL,
        description TEXT,
        is_active BOOLEAN NOT NULL DEFAULT TRUE,
        is_default BOOLEAN NOT NULL DEFAULT FALSE,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (workflow_code) REFERENCES workflows (code) ON DELETE CASCADE,
        UNIQUE (workflow_code, version)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS workflow_roles (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) NOT NULL UNIQUE,
        name VARCHAR(150) NOT NULL,
        description TEXT,
        is_active BOOLEAN NOT NULL DEFAULT TRUE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS workflow_decision_outcome (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) NOT NULL UNIQUE,
        label VARCHAR(150) NOT NULL
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS workflow_deliverables (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) NOT NULL UNIQUE,
        name VARCHAR(200) NOT NULL,
        description TEXT,
        is_active BOOLEAN NOT NULL DEFAULT TRUE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS workflow_etapes (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        workflow_version VARCHAR(50) NOT NULL,
        parent_etape_code VARCHAR(50),
        code VARCHAR(50) NOT NULL UNIQUE,
        name VARCHAR(200) NOT NULL,
        impact VARCHAR(50),
        statut VARCHAR(10) DEFAULT 'NON',
        description TEXT,
        order INTEGER NOT NULL,
        is_active BOOLEAN NOT NULL DEFAULT TRUE,
        valid_from DATE NOT NULL DEFAULT (CURRENT_DATE),
        valid_to DATE,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (workflow_version) REFERENCES workflow_versions (code) ON DELETE CASCADE,
        FOREIGN KEY (parent_etape_code) REFERENCES workflow_etapes (code) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS workflow_etapes_sla (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        etape_code VARCHAR(50) NOT NULL,
        duration_value INTEGER NOT NULL,
        duration_unit VARCHAR(20) NOT NULL DEFAULT 'JOURS',
        description TEXT,
        CHECK (duration_unit IN ('HEURES', 'JOURS', 'SEMAINES', 'MOIS')),
        FOREIGN KEY (etape_code) REFERENCES workflow_etapes (code) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS workflow_etapes_roles (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        etape_code VARCHAR(50) NOT NULL,
        role_code VARCHAR(50) NOT NULL,
        action VARCHAR(50) NOT NULL,
        FOREIGN KEY (etape_code) REFERENCES workflow_etapes (code) ON DELETE CASCADE,
        FOREIGN KEY (role_code) REFERENCES workflow_roles (code) ON DELETE CASCADE,
        UNIQUE (etape_code, role_code)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS workflow_etapes_decision (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        etape_code VARCHAR(50) NOT NULL,
        code VARCHAR(50) NOT NULL,
        name VARCHAR(150) NOT NULL,
        description TEXT,
        outcomes TEXT, -- Pipe de code separe par |
        FOREIGN KEY (etape_code) REFERENCES workflow_etapes (code) ON DELETE CASCADE,
        UNIQUE(etape_code, code)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS workflow_etapes_deliverable (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        etape_code VARCHAR(50) NOT NULL,
        deliverable_code VARCHAR(50) NOT NULL,
        is_required BOOLEAN NOT NULL DEFAULT TRUE,
        FOREIGN KEY (etape_code) REFERENCES workflow_etapes (code) ON DELETE CASCADE,
        FOREIGN KEY (deliverable_code) REFERENCES workflow_deliverables (code) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ##############################################################
-- 9. PROJETS
-- ##############################################################
-- Via API Indisponible
CREATE TABLE
    IF NOT EXISTS projets (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        secteur_id BIGINT UNSIGNED,
        titre VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (secteur_id) REFERENCES secteurs_activites (id)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS zones_intervention (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        projet_id BIGINT UNSIGNED,
        departement_id BIGINT UNSIGNED,
        adresse TEXT,
        latitude DECIMAL(10, 8),
        longitude DECIMAL(11, 8),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (projet_id) REFERENCES projets (id) ON DELETE CASCADE,
        FOREIGN KEY (departement_id) REFERENCES departements (id)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ##############################################################
-- 10. GUICHETS FINANCEMENTS & AGENCES REGIONALES
-- ##############################################################
-- Via API Indisponible
CREATE TABLE
    IF NOT EXISTS guichets (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        workflow_code VARCHAR(50),
        code VARCHAR(50) UNIQUE,
        libelle VARCHAR(100) NOT NULL,
        description TEXT,
        couleur VARCHAR(7),
        montant_min DECIMAL(15, 2) DEFAULT 0,
        montant_max DECIMAL(15, 2) DEFAULT 0,
        is_active BOOLEAN DEFAULT TRUE,
        is_form_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (workflow_code) REFERENCES workflow_codes (code)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ##############################################################
-- 11. DISPOSITIFS
-- ##############################################################
CREATE TABLE
    IF NOT EXISTS dispositifs (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) UNIQUE,
        projet_id BIGINT UNSIGNED UNIQUE,
        intitule VARCHAR(200) NOT NULL,
        budget_alloue DECIMAL(15, 2) NOT NULL,
        nbre_emplois_prevu INT DEFAULT 0,
        nbre_beneficiaire_prevu INT DEFAULT 0,
        nbre_micro_projet_prevu INT DEFAULT 0,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        FOREIGN KEY (projet_id) REFERENCES projets (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ##############################################################
-- 12. JEUNES (porteurs de projet => Promoteur)
-- ##############################################################
-- Via API Indisponible
CREATE TABLE
    IF NOT EXISTS promoteurs (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        profile TEXT,
        nom VARCHAR(100) NOT NULL,
        prenom VARCHAR(100) NOT NULL,
        email VARCHAR(180) UNIQUE,
        telephone VARCHAR(20) NOT NULL UNIQUE,
        tranche_age ENUM ('18_40', 'PLUS_40'),
        datenaissance DATE NOT NULL,
        lieunaissance VARCHAR(150),
        matriculeaej VARCHAR(50) UNIQUE,
        numerocni VARCHAR(50) UNIQUE,
        numerocmu VARCHAR(50) UNIQUE,
        numerocnps VARCHAR(50) UNIQUE,
        raison_sociale VARCHAR(200),
        handicap VARCHAR(100),
        nomdupere VARCHAR(200),
        nomdelamere VARCHAR(200),
        sexe_id BIGINT UNSIGNED,
        personnel_id BIGINT UNSIGNED,
        lieuhabitation_id BIGINT UNSIGNED,
        agenceregionale_id BIGINT UNSIGNED,
        secteuractivite_id BIGINT UNSIGNED,
        soussecteuractivite_id BIGINT UNSIGNED,
        situationmatrimoniale_id BIGINT UNSIGNED,
        typesituationhandicap_id BIGINT UNSIGNED,
        typepieceidentite_id BIGINT UNSIGNED,
        niveauetude_id BIGINT UNSIGNED,
        paysnationalite_id BIGINT UNSIGNED,
        statut TINYINT (1) DEFAULT 1,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        FOREIGN KEY (sexe_id) REFERENCES sexes (id),
        FOREIGN KEY (personnel_id) REFERENCES personnels (id),
        FOREIGN KEY (lieuhabitation_id) REFERENCES lieux_habitation (id),
        FOREIGN KEY (agenceregionale_id) REFERENCES agences_regionales (id),
        FOREIGN KEY (typepieceidentite_id) REFERENCES type_pieces_identite (id),
        FOREIGN KEY (niveauetude_id) REFERENCES niveau_etude (id),
        FOREIGN KEY (paysnationalite_id) REFERENCES pays (id),
        FOREIGN KEY (secteuractivite_id) REFERENCES secteurs_activites (id),
        FOREIGN KEY (soussecteuractivite_id) REFERENCES sous_secteurs_activites (id),
        FOREIGN KEY (situationmatrimoniale_id) REFERENCES situation_matrimoniale (id),
        FOREIGN KEY (typesituationhandicap_id) REFERENCES types_situation_handicap (id)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ##############################################################
-- 13. MICRO PROJETS
-- ##############################################################
-- Via API Indisponible
CREATE TABLE
    IF NOT EXISTS micro_projets (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) UNIQUE,
        intitule VARCHAR(100) NOT NULL,
        matricule VARCHAR(50) UNIQUE,
        description TEXT,
        montant_total DECIMAL(15, 2) DEFAULT 0,
        dispositif_id BIGINT UNSIGNED,
        organisme_id BIGINT UNSIGNED,
        guichet_id BIGINT UNSIGNED,
        secteur_id BIGINT UNSIGNED,
        commune_id BIGINT UNSIGNED,
        agence_id BIGINT UNSIGNED,
        agence_imputation_id BIGINT UNSIGNED,
        promoteur_id BIGINT UNSIGNED,
        stade_projet ENUM ('CREATION', 'DEVELOPPEMENT') DEFAULT 'CREATION',
        type_projet ENUM ('INDIVIDUEL', 'COLLECTIF') DEFAULT 'INDIVIDUEL',
        statut ENUM (
            'BROUILLON',
            'EN_SOUMISSION',
            'EN_COURS',
            'EN_ANALYSE',
            'EN_ATTENTE',
            'ANNULE',
            'NON_APPROUVE',
            'APPROUVE',
            'EN_FORMATION',
            'EN_FINANCEMENT',
            'EN_DECAISSEMENT',
            'EN_SUIVI',
            'EN_REMBOURSEMENT',
            'TERMINE'
        ) DEFAULT 'BROUILLON',
        localisation VARCHAR(50),
        geolocalisation TEXT,
        date_certification DATE,
        date_transmission_partenaire DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (dispositif_id) REFERENCES dispositifs (id) ON DELETE CASCADE,
        FOREIGN KEY (organisme_id) REFERENCES organisme_financements (id) ON DELETE CASCADE,
        FOREIGN KEY (guichet_id) REFERENCES guichets (id) ON DELETE CASCADE,
        FOREIGN KEY (secteur_id) REFERENCES secteurs_activites (id) ON DELETE CASCADE,
        FOREIGN KEY (commune_id) REFERENCES communes (id) ON DELETE CASCADE,
        FOREIGN KEY (agence_id) REFERENCES agences_regionales (id) ON DELETE CASCADE,
        FOREIGN KEY (agence_imputation_id) REFERENCES agences_regionales (id) ON DELETE CASCADE,
        FOREIGN KEY (promoteur_id) REFERENCES promoteurs (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ##############################################################
-- 14. LOTS, OBSERVATIONS, DOCUMENTS
-- ##############################################################
CREATE TABLE
    IF NOT EXISTS lots_transmission (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        organisme_id BIGINT UNSIGNED,
        code VARCHAR(50),
        titre VARCHAR(255),
        fichier_repartition TEXT,
        fichier_courrier TEXT,
        reference_courrier VARCHAR(100),
        reference_convention VARCHAR(100),
        date_transmission DATE,
        taux_recouvrement DECIMAL(5, 2),
        duree_differee INT,
        duree_remboursement INT,
        dossiers TEXT, --PIPE code micro-projet ("|")
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (organisme_id) REFERENCES organisme_financements (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS observations (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        micro_projet_id BIGINT UNSIGNED,
        auteur_id BIGINT UNSIGNED,
        content TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (micro_projet_id) REFERENCES micro_projets (id) ON DELETE CASCADE,
        FOREIGN KEY (auteur_id) REFERENCES personnels (id)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS documents (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        micro_projet_id BIGINT UNSIGNED,
        type_document VARCHAR(100),
        fichier VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (micro_projet_id) REFERENCES micro_projets (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ##############################################################
-- 15. FINANCEMENTS, DECAISSEMENTS, REMBOURSEMENTS
-- ##############################################################
CREATE TABLE
    IF NOT EXISTS budgets (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        micro_projet_id BIGINT UNSIGNED UNIQUE,
        intitule VARCHAR(100) NOT NULL,
        montant_accorde DECIMAL(15, 2) NOT NULL,
        date_accord DATE,
        source VARCHAR(100),
        statut ENUM ('EN_ATTENTE', 'APPROUVE', 'NON_APPROUVE') DEFAULT 'EN_ATTENTE',
        devise VARCHAR(10) NOT NULL DEFAULT 'FCFA',
        deblocage ENUM ('OUI', 'NON') DEFAULT 'NON',
        date_deblocage DATE,
        signature_convention ENUM ('SIGNEE', 'NON_SIGNEE') DEFAULT 'NON_SIGNEE',
        date_signature DATE,
        reception_acte_credit ENUM ('OUI', 'NON', 'PARTIEL') DEFAULT 'NON',
        date_reception DATE,
        observations TEXT,
        valide_par BIGINT UNSIGNED,
        created_at DATETIME NOT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (micro_projet_id) REFERENCES micro_projets (id) ON DELETE CASCADE,
        FOREIGN KEY (valide_par) REFERENCES personnels (id)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS compte_financements (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        micro_projet_id BIGINT UNSIGNED,
        organisme_id BIGINT UNSIGNED,
        budget_id BIGINT UNSIGNED UNIQUE,
        etat_ouverture ENUM ('OUVERT', 'FERME', 'NON_OUVERT') DEFAULT 'NON_OUVERT',
        avis_partenaire ENUM ('ACCORDE', 'AJOURNE', 'REJETE'),
        montant_accorde DECIMAL(15, 2),
        duree_pret INT,
        duree_remboursement INT,
        taux_interet DECIMAL(5, 2),
        date_ouverture DATE,
        lieu_ouverture VARCHAR(100),
        observations TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (micro_projet_id) REFERENCES micro_projets (id) ON DELETE CASCADE,
        FOREIGN KEY (organisme_id) REFERENCES organisme_financements (id) ON DELETE CASCADE,
        FOREIGN KEY (budget_id) REFERENCES budgets (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- A SUPPRIMER
-- CREATE TABLE
--     IF NOT EXISTS compte_remboursements (
--         budget_id BIGINT UNSIGNED UNIQUE,
--         montant_remboursement DECIMAL(18, 2),
--         montant_garantie DECIMAL(18, 2),
--         montant_recouvrement DECIMAL(18, 2),
--         dure_remboursement INT,
--         dure_differe INT,
--         date_premiere_echeance DATE,
--         date_derniere_cheance DATE,
--         echeance_rembourse DECIMAL(18, 2), -- A calculer montant/dure echeance
--         restructuration_pret ENUM ('OUI', 'NON') DEFAULT 'NON',
--         capital_amorti DECIMAL(18, 2) DEFAULT 0,
--         interets DECIMAL(18, 2) DEFAULT 0,
--         FOREIGN KEY (budget_id) REFERENCES budgets (id) ON DELETE CASCADE
--     ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS plan_decaissements (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        micro_projet_id BIGINT UNSIGNED,
        budget_id BIGINT UNSIGNED,
        compte_financement_id BIGINT UNSIGNED,
        montant_planifie DECIMAL(18, 2) NOT NULL,
        date_prevue DATE,
        justificatif_path TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (micro_projet_id) REFERENCES micro_projets (id) ON DELETE CASCADE,
        FOREIGN KEY (budget_id) REFERENCES budgets (id) ON DELETE CASCADE,
        FOREIGN KEY (compte_financement_id) REFERENCES compte_financements (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS ligne_decaissements (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        plan_decaissement_id BIGINT UNSIGNED,
        numero_ligne INT NOT NULL,
        object_ligne VARCHAR(100),
        montant_ligne DECIMAL(18, 2) NOT NULL,
        mode_decaisse ENUM ('CHEQUE', 'VIREMENT'),
        date_prevue DATE,
        intitule_prestataire VARCHAR(100),
        numero_compte VARCHAR(100),
        contact VARCHAR(100),
        statut ENUM ('VALIDE', 'NON_VALIDE'),
        observations TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (plan_decaissement_id) REFERENCES plan_decaissements (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE 
    IF NOT EXISTS decaissements_declarations (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        plan_decaissement_id BIGINT UNSIGNED NOT NULL,
        ligne_decaissement_id BIGINT UNSIGNED,
        promoteur_id BIGINT UNSIGNED NOT NULL,
        montant_declare DECIMAL(18,2) NOT NULL,
        date_declaree DATE,
        reference_banque VARCHAR(100),
        justificatif_path TEXT,
        observations TEXT,
        statut ENUM('BROUILLON','SOUMIS','TRAITE') DEFAULT 'BROUILLON',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY(plan_decaissement_id) REFERENCES plan_decaissements(id),
        FOREIGN KEY (ligne_decaissement_id) REFERENCES ligne_decaissememts(id),
        FOREIGN KEY(promoteur_id) REFERENCES promoteurs(id)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS decaissements (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        plan_decaissement_id BIGINT UNSIGNED,
        ligne_decaissement_id BIGINT UNSIGNED,
        agence_id BIGINT UNSIGNED,
        montant_decaisse DECIMAL(18, 2),
        date_decaissement DATE,
        reference_banque TEXT,
        statut ENUM ('EN_ATTENTE', 'VALIDE', 'NON_VALIDE') DEFAULT 'EN_ATTENTE',
        observations TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (plan_decaissement_id) REFERENCES plan_decaissements (id),
        FOREIGN KEY (ligne_decaissement_id) REFERENCES ligne_decaissememts(id),
        FOREIGN KEY (agence_id) REFERENCES agences_regionales (id)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE 
    IF NOT EXISTS plan_remboursements (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        micro_projet_id BIGINT UNSIGNED,
        budget_id BIGINT UNSIGNED,
        echeance_mensuelle DATE,
        montant_echeance DECIMAL(18, 2),
        periode INT, -- Année
        capital_rembourse DECIMAL(18, 2),
        capital_restant DECIMAL(18, 2),
        interets DECIMAL(18, 2),
        amortissement_capital DECIMAL(18, 2),
        justificatif_path TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (micro_projet_id) REFERENCES micro_projets (id) ON DELETE CASCADE,
        FOREIGN KEY (budget_id) REFERENCES budgets (id) ON DELETE CASCADE,
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE 
    IF NOT EXISTS remboursements_declarations (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        plan_remboursement_id BIGINT UNSIGNED,
        promoteur_id BIGINT UNSIGNED,
        montant_declare DECIMAL(18,2),
        date_declaree DATE,
        reference_banque VARCHAR(100),
        justificatif_path TEXT,
        observations TEXT,
        statut ENUM('BROUILLON','SOUMIS','TRAITE') DEFAULT 'BROUILLON',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (plan_remboursement_id) REFERENCES plan_remboursements (id),
        FOREIGN KEY (promoteur_id) REFERENCES promoteurs (id)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS remboursements (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        plan_remboursement_id BIGINT UNSIGNED,
        promoteur_id BIGINT UNSIGNED,
        montant_echu DECIMAL(18, 2),
        montant_paye DECIMAL(18, 2),
        montant_impaye DECIMAL(18, 2),
        penalites DECIMAL(18, 2) DEFAULT 0,
        date_paiement DATE,
        observations TEXT,
        statut ENUM ('EN_ATTENTE', 'PAYE', 'PARTIEL', 'NON_PAYE') DEFAULT 'NON_PAYE',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (promoteur_id) REFERENCES promoteurs (id) ON DELETE CASCADE,
        FOREIGN KEY (plan_remboursement_id) REFERENCES plan_remboursements (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE 
    IF NOT EXISTS recouvrements (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        micro_projet_id BIGINT UNSIGNED,
        plan_remboursement_id BIGINT UNSIGNED,
        agent_id BIGINT UNSIGNED,
        montant_recouvre DECIMAL(18, 2),
        date_recouvrement DATE,
        type_action ENUM ('APPEL', 'COURRIER', 'DECHARGE', 'MISE_EN_DEMEURE', 'CONTENTIEUX'),
        observations TEXT,
        justificatif_path TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (micro_projet_id) REFERENCES micro_projets (id) ON DELETE CASCADE,
        FOREIGN KEY (plan_remboursement_id) REFERENCES plan_remboursements (id) ON DELETE CASCADE,
        FOREIGN KEY (agent_id) REFERENCES personnels (id) ON DELETE CASCADE
    )

-- TRANSACTIONS BENEFICIAIRE (DEPENSES - RECETTES)
CREATE TABLE
    IF NOT EXISTS categories_transactions (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) UNIQUE,
        libelle VARCHAR(100) NOT NULL,
        description TEXT,
        niveau INT NOT NULL DEFAULT 1,
        parent_id BIGINT UNSIGNED,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (parent_id) REFERENCES categories_transactions (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS transactions_financieres (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        micro_projet_id BIGINT UNSIGNED,
        promoteur_id BIGINT UNSIGNED,
        categorie_id BIGINT UNSIGNED,
        libelle VARCHAR(200) NOT NULL,
        type ENUM ('RECETTE', 'DEPENSE'),
        montant DECIMAL(15, 2) NOT NULL,
        statut ENUM(
            'BROUILLON',
            'SOUMIS',
            'VALIDE',
            'REJETE',
            'ANNULE'
        ) DEFAULT 'BROUILLON',
        mode_paiement ENUM(
            'ESPECES',
            'BANQUE',
            'MOBILE_MONEY',
            'CHEQUE',
            'AUTRE'
        ),
        reference VARCHAR(50) UNIQUE,
        justificatif_path TEXT,
        observations TEXT,
        date DATE NOT NULL,
        saisi_par BIGINT UNSIGNED,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        FOREIGN KEY (micro_projet_id) REFERENCES micro_projets (id) ON DELETE CASCADE,
        FOREIGN KEY (promoteur_id) REFERENCES promoteurs (id) ON DELETE CASCADE,
        FOREIGN KEY (categorie_id) REFERENCES categories_transactions (id) ON DELETE CASCADE,
        FOREIGN KEY (saisi_par) REFERENCES personnels (id)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ##############################################################
-- 16. EXPLOITATIONS & INDICATEURS & SUIVIS
-- ##############################################################
CREATE TABLE
    IF NOT EXISTS exploitations (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        micro_projet_id BIGINT UNSIGNED,
        etat_installation ENUM ('ACHEVE', 'EN_COURS', 'NON_ENTAME') DEFAULT 'NON_ENTAME',
        etat_activite ENUM ('EN_BONNE_MARCHE', 'EN_EXPLOITATION', 'SINISTRE') DEFAULT 'EN_BONNE_MARCHE',
        realisation ENUM ('OUI', 'NON') DEFAULT 'NON',
        date_visite DATE,
        difficultes TEXT,
        recommandations TEXT,
        latitude DECIMAL(10, 8),
        longitude DECIMAL(11, 8),
        observations TEXT,
        agent_id BIGINT UNSIGNED,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (micro_projet_id) REFERENCES micro_projets (id) ON DELETE CASCADE,
        FOREIGN KEY (agent_id) REFERENCES personnels (id)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS visite_photos (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        exploitation_id BIGINT UNSIGNED,
        photo_url VARCHAR(500) NOT NULL,
        description VARCHAR(255),
        prise_le TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        prise_par_id BIGINT UNSIGNED,
        FOREIGN KEY (exploitation_id) REFERENCES exploitations (id) ON DELETE CASCADE,
        FOREIGN KEY (prise_par_id) REFERENCES personnels (id)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS indicateurs (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        micro_projet_id BIGINT UNSIGNED,
        nom VARCHAR(255) NOT NULL UNIQUE,
        description TEXT NULL,
        type_valeur VARCHAR(255) NULL,
        unite VARCHAR(255) NULL,
        statut TINYINT (1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        FOREIGN KEY (micro_projet_id) REFERENCES micro_projets (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS indicateurs_suivi (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        indicateur_id BIGINT UNSIGNED,
        valeur VARCHAR(255) NOT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        FOREIGN KEY (indicateur_id) REFERENCES indicateurs (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS suivis (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        micro_projet_id BIGINT UNSIGNED,
        promoteur_id BIGINT UNSIGNED,
        libelle VARCHAR(100) NOT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        FOREIGN KEY (micro_projet_id) REFERENCES micro_projets (id) ON DELETE CASCADE,
        FOREIGN KEY (promoteur_id) REFERENCES promoteurs (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS type_emplois (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(255) NOT NULL UNIQUE,
        libelle VARCHAR(255) NOT NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS embauches (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        promoteur_id BIGINT UNSIGNED,
        entreprise_id BIGINT UNSIGNED,
        micro_projet_id BIGINT UNSIGNED,
        type_emploi_id BIGINT UNSIGNED NULL,
        poste VARCHAR(200) NOT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        FOREIGN KEY (promoteur_id) REFERENCES promoteurs (id) ON DELETE CASCADE,
        FOREIGN KEY (entreprise_id) REFERENCES entreprises (id) ON DELETE CASCADE,
        FOREIGN KEY (micro_projet_id) REFERENCES micro_projets (id) ON DELETE CASCADE,
        FOREIGN KEY (type_emploi_id) REFERENCES type_emplois (id)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ##############################################################
-- 17. FORMULAIRES & QUESTIONNAIRES
-- ##############################################################
CREATE TABLE
    IF NOT EXISTS formulaires_evaluation (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        micro_projet_id BIGINT UNSIGNED,
        code VARCHAR(50) NOT NULL UNIQUE,
        libelle VARCHAR(200) NOT NULL,
        public_cible VARCHAR(50) NOT NULL,
        actif TINYINT (1) NOT NULL DEFAULT 1,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (micro_projet_id) REFERENCES micro_projets (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS questions_evaluation (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        formulaire_id BIGINT UNSIGNED,
        code VARCHAR(50) NOT NULL,
        libelle TEXT NOT NULL,
        type_question VARCHAR(50) NOT NULL,
        options JSON DEFAULT NULL,
        ordre SMALLINT NOT NULL DEFAULT 0,
        affichage BOOLEAN DEFAULT NULL,
        obligatoire TINYINT (1) NOT NULL DEFAULT 1,
        FOREIGN KEY (formulaire_id) REFERENCES formulaires_evaluation (id) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS evaluations (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        formulaire_id BIGINT UNSIGNED,
        cible_type VARCHAR(50) NOT NULL,
        evaluateur_id BIGINT UNSIGNED,
        date_evaluation DATETIME NOT NULL,
        score_global DECIMAL(5, 2) DEFAULT NULL,
        commentaire TEXT DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (formulaire_id) REFERENCES formulaires_evaluation (id) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (evaluateur_id) REFERENCES personnels (id)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS reponses_evaluation (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        evaluation_id BIGINT UNSIGNED,
        question_id BIGINT UNSIGNED,
        reponse_texte TEXT DEFAULT NULL,
        FOREIGN KEY (evaluation_id) REFERENCES evaluations (id) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (question_id) REFERENCES questions_evaluation (id) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ##############################################################
-- 18. INSTANCES DE WORKFLOW & HISTORIQUE
-- ##############################################################
CREATE TABLE
    IF NOT EXISTS workflow_instance (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        micro_projet_id BIGINT UNSIGNED UNIQUE,
        workflow_version VARCHAR(50) NOT NULL,
        current_etape_code VARCHAR(50),
        next_etape_code VARCHAR(50),
        statut VARCHAR(20) NOT NULL DEFAULT 'EN_COURS' CHECK (
            statu IN ('EN_COURS', 'TERMINE', 'REJETE', 'ABANDONNE')
        ),
        started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        completed_at TIMESTAMP NULL,
        FOREIGN KEY (micro_projet_id) REFERENCES micro_projets (id) ON DELETE CASCADE,
        FOREIGN KEY (workflow_version) REFERENCES workflow_versions (code),
        FOREIGN KEY (current_etape_code) REFERENCES workflow_etapes (code),
        FOREIGN KEY (next_etape_code) REFERENCES workflow_etapes (code)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS workflow_instance_history (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        workflow_instance_id BIGINT UNSIGNED,
        etape_code VARCHAR(50) NOT NULL,
        role_code VARCHAR(50),
        acted_by BIGINT UNSIGNED,
        acted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        action TEXT,
        comment TEXT,
        FOREIGN KEY (workflow_instance_id) REFERENCES workflow_instance (id) ON DELETE CASCADE,
        FOREIGN KEY (etape_code) REFERENCES workflow_etapes (code),
        FOREIGN KEY (role_code) REFERENCES roles (code),
        FOREIGN KEY (acted_by) REFERENCES personnels (id)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS workflow_instance_deliverable (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        workflow_instance_id BIGINT UNSIGNED,
        deliverable_code VARCHAR(50) NOT NULL,
        file_path TEXT,
        file_name VARCHAR(255),
        file_size BIGINT,
        file_type VARCHAR(100),
        observations TEXT,
        produced_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        produced_by_id BIGINT UNSIGNED,
        FOREIGN KEY (workflow_instance_id) REFERENCES workflow_instance (id) ON DELETE CASCADE,
        FOREIGN KEY (deliverable_code) REFERENCES workflow_deliverables (code),
        FOREIGN KEY (produced_by_id) REFERENCES personnels (id)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS workflow_instance_comment (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        workflow_instance_id BIGINT UNSIGNED,
        etape_code VARCHAR(50) NOT NULL,
        commented_by_id BIGINT UNSIGNED,
        comment TEXT,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (workflow_instance_id) REFERENCES workflow_instance (id) ON DELETE CASCADE,
        FOREIGN KEY (etape_code) REFERENCES workflow_etapes (code),
        FOREIGN KEY (commented_by_id) REFERENCES personnels (id)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ##############################################################
-- 19. ANALYSE DYNAMIQUE DES DONNEES
-- ##############################################################
-- CATEGORIE D'ETAPES (SELECTION MULTIPLE D'ETAPES VALIDEES)
CREATE TABLE
    IF NOT EXISTS workflow_categories_etapes (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        etape_code VARCHAR(50) NOT NULL,
        comment TEXT,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (etape_code) REFERENCES workflow_etapes (code)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

SET
    FOREIGN_KEY_CHECKS = 1;

-- ##############################################################
-- 20. TRIGGERS
-- ##############################################################
DELIMITER / / CREATE TRIGGER before_insert_workflow_etape BEFORE INSERT ON workflow_etapes FOR EACH ROW BEGIN IF NEW.parent_etape_code IS NOT NULL
AND NEW.parent_etape_code = NEW.code THEN SIGNAL SQLSTATE '45000'
SET
    MESSAGE_TEXT = 'Une étape ne peut pas être son propre parent';

END IF;

END / / CREATE TRIGGER before_update_workflow_etape BEFORE
UPDATE ON workflow_etapes FOR EACH ROW BEGIN IF NEW.parent_etape_code IS NOT NULL
AND NEW.parent_etape_code = NEW.code THEN SIGNAL SQLSTATE '45000'
SET
    MESSAGE_TEXT = 'Une étape ne peut pas être son propre parent';

END IF;

END / / DELIMITER;