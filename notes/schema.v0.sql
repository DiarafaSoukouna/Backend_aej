-- =====================================================================
-- SCRIPT COMPLET CORRIGÉ
-- =====================================================================

-- ##############################################################
-- 1. CONFIGURATION GLOBALE
-- ##############################################################
CREATE TABLE IF NOT EXISTS Configuration (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    systemSigle VARCHAR(50),
    systemName VARCHAR(255),
    systemLogo TEXT,
    systemLink TEXT,
    structureSigle VARCHAR(50),
    structureName VARCHAR(255),
    structureLogo TEXT,
    structureAddress VARCHAR(255),
    structureEmail VARCHAR(255),
    structureWhatsAppNumber VARCHAR(50),
    structurePhoneNumber VARCHAR(50),
    localCurrencySigle VARCHAR(10),
    mainCurrencySigle VARCHAR(10),
    mainCurrencyRate DECIMAL(15,4),
    isMaintenance BOOLEAN DEFAULT FALSE,
    maintenanceMessage TEXT,
    maintenanceStartDate DATETIME,
    maintenanceEndDate DATETIME,
    inactivityMinute INT DEFAULT 30,
    maxSessions INT DEFAULT 5,
    maxLoginAttempts INT DEFAULT 5,
    otpValidityMinute INT DEFAULT 5,
    passwordExpiryMonth INT DEFAULT 5,
    delayUpdateSecond INT DEFAULT 5,
    delayDeleteSecond INT DEFAULT 5,
    whatsAppInstance VARCHAR(50),
    whatsAppApiKey VARCHAR(255),
    whatsAppNumberId VARCHAR(100),
    notifEmailUser VARCHAR(255),
    notifEmailPassword VARCHAR(255),
    notifEmailFromName VARCHAR(255),
    notifEmailSmtpHost VARCHAR(255),
    notifEmailSmtpPort INT DEFAULT 587,
    notifEmailSmtpEncryption VARCHAR(10) DEFAULT 'tls',
    parentApiUrl VARCHAR(1000),
    parentApiKey VARCHAR(255),
    parentApiSecret VARCHAR(255),
    parentApiTimeoutSeconds INT DEFAULT 30,
    isDefault BOOLEAN DEFAULT TRUE,
    createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ##############################################################
-- 2. DONNÉES GÉOGRAPHIQUES
-- ##############################################################
CREATE TABLE IF NOT EXISTS regions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) UNIQUE,
    libelle VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS departements (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    region_id BIGINT NOT NULL,
    code VARCHAR(10) UNIQUE,
    libelle VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (region_id) REFERENCES regions(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS communes (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    departement_id BIGINT NOT NULL,
    code VARCHAR(10) UNIQUE,
    libelle VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (departement_id) REFERENCES departements(id) ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS villes (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    commune_id BIGINT NOT NULL,
    code VARCHAR(10) UNIQUE,
    libelle VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (commune_id) REFERENCES communes(id) ON DELETE CASCADE
);

-- CREATE TABLE IF NOT EXISTS niveaux_localites (
--     id BIGINT AUTO_INCREMENT PRIMARY KEY,
--     parent_id BIGINT NULL,
--     code VARCHAR(10) NOT NULL UNIQUE,
--     nom VARCHAR(100) NOT NULL,
--     created_at DATETIME NOT NULL
-- );

-- CREATE TABLE IF NOT EXISTS localites (
--     id BIGINT AUTO_INCREMENT PRIMARY KEY,
--     niveau_id BIGINT NOT NULL,
--     code VARCHAR(20) NOT NULL UNIQUE,
--     couche_carto VARCHAR(255),
--     nom VARCHAR(150) NOT NULL,
--     created_at DATETIME NOT NULL,
--     FOREIGN KEY (niveau_id) REFERENCES niveaux_localites(id)
-- );

-- ##############################################################
-- 3. RÉFÉRENTIELS MÉTIER
-- ##############################################################
CREATE TABLE IF NOT EXISTS secteurs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) UNIQUE,
    libelle VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE directions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nom VARCHAR(255) NOT NULL,
 code VARCHAR(255) NOT NULL UNIQUE,
 description TEXT NULL,
 created_at TIMESTAMP NULL,
 updated_at TIMESTAMP NULL
);

CREATE TABLE services (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nom VARCHAR(255) NOT NULL,
 code VARCHAR(255) NOT NULL UNIQUE,
 description TEXT NULL,
 direction_id BIGINT UNSIGNED NOT NULL,
 created_at TIMESTAMP NULL,
 updated_at TIMESTAMP NULL,
 CONSTRAINT services_direction_id_foreign
  FOREIGN KEY (direction_id) REFERENCES directions(id) ON DELETE CASCADE
);

CREATE TABLE fonctions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nom VARCHAR(255) NOT NULL,
 code VARCHAR(255) NOT NULL UNIQUE,
 description TEXT NULL,
 service_id BIGINT UNSIGNED NOT NULL,
 created_at TIMESTAMP NULL,
 updated_at TIMESTAMP NULL,
 CONSTRAINT fonctions_service_id_foreign
  FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

CREATE TABLE type_entreprises (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 code VARCHAR(255) NOT NULL UNIQUE,
 libelle VARCHAR(255) NOT NULL,
 created_at TIMESTAMP NULL,
 updated_at TIMESTAMP NULL
);

CREATE TABLE type_emplois (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 code VARCHAR(255) NOT NULL UNIQUE,
 libelle VARCHAR(255) NOT NULL,
 created_at TIMESTAMP NULL,
 updated_at TIMESTAMP NULL
);

CREATE TABLE IF NOT EXISTS guichets (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) UNIQUE,
    libelle VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pieces_identite (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) UNIQUE,
    libelle VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS entreprises (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(50) UNIQUE,
    raison_sociale VARCHAR(200) NOT NULL,
    adresse TEXT,
    contact VARCHAR(30),
    email VARCHAR(100),
    region_id BIGINT,
    commune_id BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (region_id) REFERENCES regions(id),
    FOREIGN KEY (commune_id) REFERENCES communes(id)
);

-- ##############################################################
-- 4. AGENCES (dépend des régions)
-- ##############################################################
CREATE TABLE IF NOT EXISTS agences (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) UNIQUE,
    libelle VARCHAR(100) NOT NULL,
    region_id BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (region_id) REFERENCES regions(id)
);

CREATE TABLE IF NOT EXISTS institutions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) UNIQUE NOT NULL,
    libelle VARCHAR(200) NOT NULL,
    type ENUM('BANQUE','FONDS','CO2CI','AUTRE') DEFAULT 'AUTRE',
    contact_nom VARCHAR(150),
    contact_email VARCHAR(180),
    contact_telephone VARCHAR(20),
    adresse TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ##############################################################
-- 5. RÔLES & PERMISSIONS
-- ##############################################################
CREATE TABLE IF NOT EXISTS roles (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    libelle VARCHAR(100) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS permissions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    role_id BIGINT NOT NULL,
    module VARCHAR(100),
    autorise BOOLEAN,
    acces VARCHAR(255),
    full_access BOOLEAN,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

-- ##############################################################
-- 6. PERSONNELS (avec agence_id)
-- ##############################################################
CREATE TABLE IF NOT EXISTS personnels (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    telephone VARCHAR(20),
    mot_de_passe VARCHAR(255) NOT NULL,
    type_utilisateur ENUM('interne','externe') DEFAULT 'interne',
    role_id BIGINT NOT NULL,
    agence_id BIGINT NULL,           -- rattachement à une agence
    institution_id BIGINT NULL,
    statut TINYINT(1) NOT NULL DEFAULT 1,
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (agence_id) REFERENCES agences(id),
    FOREIGN KEY (institution_id) REFERENCES institutions(id)
);

-- ##############################################################
-- 7. PROMOTEURS (porteurs de projet)
-- ##############################################################
CREATE TABLE IF NOT EXISTS promoteurs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    numero_aej VARCHAR(50) UNIQUE,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(150),
    genre ENUM('H','F') DEFAULT 'H',
    date_naissance DATE,
    contact1 VARCHAR(30),
    contact2 VARCHAR(30),
    raison_sociale VARCHAR(200),
    type_piece_identite_id BIGINT,
    numero_piece_identite VARCHAR(100),
    numero_cmu VARCHAR(100),
    numero_cnps VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (type_piece_identite_id) REFERENCES pieces_identite(id)
);

-- ##############################################################
-- 8. WORKFLOWS (dépend de rien)
-- ##############################################################
CREATE TABLE IF NOT EXISTS workflows (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS workflow_versions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    workflow_id BIGINT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    version VARCHAR(20) NOT NULL DEFAULT '2026',
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    is_default BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (workflow_id) REFERENCES workflows(id) ON DELETE CASCADE,
    UNIQUE (workflow_id, version)
);

CREATE TABLE IF NOT EXISTS workflow_etape (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    workflow_version_id BIGINT NOT NULL,
    parent_etape_id BIGINT,
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
    FOREIGN KEY (workflow_version_id) REFERENCES workflow_versions(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_etape_id) REFERENCES workflow_etape(id) ON DELETE CASCADE,
    UNIQUE (workflow_version_id, code)
);

CREATE TABLE IF NOT EXISTS workflow_etape_sla (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    etape_id BIGINT NOT NULL,
    duration_value INTEGER NOT NULL,
    duration_unit VARCHAR(20) NOT NULL DEFAULT 'jours',
    delay_type VARCHAR(20) NOT NULL DEFAULT 'fixe',
    description TEXT,
    CHECK (duration_unit IN ('heures','jours','semaines','mois')),
    CHECK (delay_type IN ('fixe','relatif')),
    FOREIGN KEY (etape_id) REFERENCES workflow_etape(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS workflow_etape_transition (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    workflow_version_id BIGINT NOT NULL,
    from_etape_id BIGINT NOT NULL,
    to_etape_id BIGINT NOT NULL,
    transition_type VARCHAR(20) NOT NULL DEFAULT 'default',
    sequence_order INTEGER NOT NULL DEFAULT 1,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    CHECK (from_etape_id <> to_etape_id),
    CHECK (transition_type IN ('default','conditional')),
    FOREIGN KEY (workflow_version_id) REFERENCES workflow_versions(id) ON DELETE CASCADE,
    FOREIGN KEY (from_etape_id) REFERENCES workflow_etape(id) ON DELETE CASCADE,
    FOREIGN KEY (to_etape_id) REFERENCES workflow_etape(id) ON DELETE CASCADE,
    UNIQUE (workflow_version_id, from_etape_id, to_etape_id)
);

CREATE TABLE IF NOT EXISTS decision_point (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    etape_id BIGINT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    FOREIGN KEY (etape_id) REFERENCES workflow_etape(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS decision_outcome (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    decision_point_id BIGINT NOT NULL,
    code VARCHAR(30) NOT NULL,
    label VARCHAR(150) NOT NULL,
    next_etape_id BIGINT,
    FOREIGN KEY (decision_point_id) REFERENCES decision_point(id) ON DELETE CASCADE,
    FOREIGN KEY (next_etape_id) REFERENCES workflow_etape(id),
    UNIQUE (decision_point_id, code)
);

CREATE TABLE IF NOT EXISTS deliverable_template (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    etape_id BIGINT NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    is_mandatory BOOLEAN NOT NULL DEFAULT TRUE,
    FOREIGN KEY (etape_id) REFERENCES workflow_etape(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS workflow_etape_roles (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    etape_id BIGINT NOT NULL,
    role_id BIGINT NOT NULL,
    responsibility TEXT,
    FOREIGN KEY (etape_id) REFERENCES workflow_etape(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    UNIQUE (etape_id, role_id)
);

-- Index sur workflow_etape
CREATE INDEX idx_etape_workflow_versions ON workflow_etape(workflow_version_id);
CREATE INDEX idx_etape_parent ON workflow_etape(parent_etape_id);
CREATE INDEX idx_transition_from ON workflow_etape_transition(from_etape_id);
CREATE INDEX idx_transition_to ON workflow_etape_transition(to_etape_id);

-- ##############################################################
-- 9. PROJETS (dépend de promoteurs, workflow_versions, agences, etc.)
-- ##############################################################
CREATE TABLE IF NOT EXISTS projets (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    workflow_version_id BIGINT NOT NULL,
    promoteur_id BIGINT NOT NULL,
    secteur_id BIGINT,
    guichet_id BIGINT,
    agence_id BIGINT,
    titre VARCHAR(255) NOT NULL,
    type_projet ENUM('INDIVIDUEL','COLLECTIF') DEFAULT 'INDIVIDUEL',
    tranche_age ENUM('18_40','PLUS_40'),
    nbre_beneficiaires INT DEFAULT 0,
    nbre_emplois INT DEFAULT 0,
    date_certification DATE,
    date_transmission_partenaire DATE,
    statut ENUM('BROUILLON','A_VALIDER','APPROUVE','NON_APPROUVE','EN_DECAISSEMENT','EN_EXPLOITATION','EN_REMBOURSEMENT','TERMINE','ANNULE') DEFAULT 'BROUILLON',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (workflow_version_id) REFERENCES workflow_versions(id),
    FOREIGN KEY (promoteur_id) REFERENCES promoteurs(id),
    FOREIGN KEY (secteur_id) REFERENCES secteurs(id),
    FOREIGN KEY (guichet_id) REFERENCES guichets(id),
    FOREIGN KEY (agence_id) REFERENCES agences(id)
);

-- Zones d'intervention
CREATE TABLE IF NOT EXISTS zones_intervention (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    projet_id BIGINT NOT NULL,
    region_id BIGINT,
    departement_id BIGINT,
    commune_id BIGINT,
    adresse TEXT,
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projet_id) REFERENCES projets(id) ON DELETE CASCADE,
    FOREIGN KEY (region_id) REFERENCES regions(id),
    FOREIGN KEY (departement_id) REFERENCES departements(id),
    FOREIGN KEY (commune_id) REFERENCES communes(id)
);

-- Observations sur projet
CREATE TABLE IF NOT EXISTS observations (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    projet_id BIGINT NOT NULL,
    auteur_id BIGINT,
    observation TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projet_id) REFERENCES projets(id) ON DELETE CASCADE,
    FOREIGN KEY (auteur_id) REFERENCES personnels(id)
);

-- Documents joints
CREATE TABLE IF NOT EXISTS documents (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    projet_id BIGINT NOT NULL,
    type_document VARCHAR(100),
    fichier VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projet_id) REFERENCES projets(id) ON DELETE CASCADE
);

-- ##############################################################
-- 10. FINANCEMENTS, LOTS, DECAISSEMENTS, REMBOURSEMENTS
-- ##############################################################
CREATE TABLE IF NOT EXISTS financements (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    projet_id BIGINT NOT NULL,
    institution_id BIGINT NOT NULL,
    montant_demande DECIMAL(18,2),
    montant_accorde DECIMAL(18,2),
    statut ENUM('EN_ATTENTE','APPROUVE','NON_APPROUVE') DEFAULT 'EN_ATTENTE',
    date_validation DATE,
    observations TEXT,
    valide_par BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projet_id) REFERENCES projets(id) ON DELETE CASCADE,
    FOREIGN KEY (institution_id) REFERENCES institutions(id),
    FOREIGN KEY (valide_par) REFERENCES personnels(id)
);

CREATE TABLE IF NOT EXISTS lots_financements (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    projet_id BIGINT NOT NULL,
    code VARCHAR(50) NOT NULL,
    libelle VARCHAR(200) NOT NULL,
    montant_planifie DECIMAL(18,2) NOT NULL,
    date_prevue DATE,
    date_reelle DATE,
    est_dernier_lot BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (projet_id) REFERENCES projets(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS decaissements (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    projet_id BIGINT NOT NULL,
    lot_id BIGINT,
    numero_tranche INT,
    montant DECIMAL(18,2),
    date_decaissement DATE,
    reference_banque VARCHAR(100),
    statut ENUM('EN_ATTENTE','VALIDE','NON_VALIDE') DEFAULT 'EN_ATTENTE',
    observations TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projet_id) REFERENCES projets(id) ON DELETE CASCADE,
    FOREIGN KEY (lot_id) REFERENCES lots_financements(id)
);

CREATE TABLE IF NOT EXISTS exploitations (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    projet_id BIGINT NOT NULL,
    date_debut_visite DATE,
    date_fin_visite DATE,
    agent_id BIGINT,
    statut_projet VARCHAR(100),
    nbre_emplois INT,
    chiffre_affaires DECIMAL(18,2),
    difficultes TEXT,
    recommandations TEXT,
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    observations TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projet_id) REFERENCES projets(id) ON DELETE CASCADE,
    FOREIGN KEY (agent_id) REFERENCES personnels(id)
);

CREATE TABLE IF NOT EXISTS visite_photos (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    exploitation_id BIGINT NOT NULL,
    photo_url VARCHAR(500) NOT NULL,
    description VARCHAR(255),
    prise_le TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    prise_par_id BIGINT,
    FOREIGN KEY (exploitation_id) REFERENCES exploitations(id) ON DELETE CASCADE,
    FOREIGN KEY (prise_par_id) REFERENCES personnels(id)
);

CREATE TABLE IF NOT EXISTS remboursements (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    projet_id BIGINT NOT NULL,
    numero_echeance INT,
    date_echeance DATE,
    date_paiement DATE,
    montant_prevu DECIMAL(18,2),
    montant_paye DECIMAL(18,2),
    capital_amorti DECIMAL(18,2) DEFAULT 0,
    interets DECIMAL(18,2) DEFAULT 0,
    penalites DECIMAL(18,2) DEFAULT 0,
    reste DECIMAL(18,2),
    statut ENUM('EN_ATTENTE','PAYE','PARTIEL','NON_PAYE') DEFAULT 'NON_PAYE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projet_id) REFERENCES projets(id) ON DELETE CASCADE
);

-- ##############################################################
-- 11. INDICATEURS
-- ##############################################################
CREATE TABLE IF NOT EXISTS indicateurs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS indicateurs_suivi (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    indicateur_id BIGINT NOT NULL,
    projet_id BIGINT NOT NULL,    -- on suit par projet (ou promoteur)
    valeur VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (indicateur_id) REFERENCES indicateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (projet_id) REFERENCES projets(id) ON DELETE CASCADE
);

-- ##############################################################
-- 12. INSTANCES DE WORKFLOW & HISTORIQUE
-- ##############################################################
CREATE TABLE IF NOT EXISTS workflow_instance (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    projet_id BIGINT NOT NULL,
    workflow_version_id BIGINT NOT NULL,
    current_etape_id BIGINT,
    status VARCHAR(20) NOT NULL DEFAULT 'en_cours' CHECK (status IN ('en_cours','termine','rejete','abandonne')),
    started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP,
    FOREIGN KEY (projet_id) REFERENCES projets(id) ON DELETE CASCADE,
    FOREIGN KEY (workflow_version_id) REFERENCES workflow_versions(id),
    FOREIGN KEY (current_etape_id) REFERENCES workflow_etape(id)
);

CREATE INDEX idx_workflow_instance_status ON workflow_instance(status);
CREATE INDEX idx_workflow_instance_current_etape ON workflow_instance(current_etape_id);

CREATE TABLE IF NOT EXISTS workflow_instance_history (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    workflow_instance_id BIGINT NOT NULL,
    etape_id BIGINT NOT NULL,
    role_id BIGINT,
    performed_by_id BIGINT,
    entered_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    exited_at TIMESTAMP,
    decision_outcome_id BIGINT,
    comments TEXT,
    FOREIGN KEY (workflow_instance_id) REFERENCES workflow_instance(id) ON DELETE CASCADE,
    FOREIGN KEY (etape_id) REFERENCES workflow_etape(id),
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (performed_by_id) REFERENCES personnels(id),
    FOREIGN KEY (decision_outcome_id) REFERENCES decision_outcome(id)
);

CREATE INDEX idx_history_instance ON workflow_instance_history(workflow_instance_id);
CREATE INDEX idx_history_etape ON workflow_instance_history(etape_id);

CREATE TABLE IF NOT EXISTS workflow_instance_document (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    workflow_instance_id BIGINT NOT NULL,
    deliverable_template_id BIGINT,
    file_reference VARCHAR(500),
    produced_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    produced_by_id BIGINT,
    FOREIGN KEY (workflow_instance_id) REFERENCES workflow_instance(id) ON DELETE CASCADE,
    FOREIGN KEY (deliverable_template_id) REFERENCES deliverable_template(id),
    FOREIGN KEY (produced_by_id) REFERENCES personnels(id)
);

CREATE INDEX idx_document_instance ON workflow_instance_document(workflow_instance_id);

CREATE TABLE IF NOT EXISTS workflow_instance_etape_comment (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    workflow_instance_id BIGINT NOT NULL,
    etape_id BIGINT NOT NULL,
    commented_by_id BIGINT,
    comment TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (workflow_instance_id) REFERENCES workflow_instance(id) ON DELETE CASCADE,
    FOREIGN KEY (etape_id) REFERENCES workflow_etape(id),
    FOREIGN KEY (commented_by_id) REFERENCES personnels(id)
);

-- ##############################################################
-- 13. TRIGGERS (empêcher auto‑référencement dans workflow_etape)
-- ##############################################################
DELIMITER //

CREATE TRIGGER before_insert_workflow_etape
BEFORE INSERT ON workflow_etape
FOR EACH ROW
BEGIN
    IF NEW.parent_etape_id IS NOT NULL AND NEW.parent_etape_id = NEW.id THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Une étape ne peut pas être son propre parent';
    END IF;
END //

CREATE TRIGGER before_update_workflow_etape
BEFORE UPDATE ON workflow_etape
FOR EACH ROW
BEGIN
    IF NEW.parent_etape_id IS NOT NULL AND NEW.parent_etape_id = NEW.id THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Une étape ne peut pas être son propre parent';
    END IF;
END //

DELIMITER ;