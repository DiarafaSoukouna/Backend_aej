-- Table des dispositifs opérationnels (ex. MPE, AGR, Capital Investissement)
CREATE TABLE IF NOT EXISTS workflows (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Version d’un dispositif (ex. MPE 2023) – une version définit un workflow complet
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

-- Étape (cycle ou sous-cycle) d’un workflow avec une structure arborescente
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

CREATE INDEX idx_etape_workflow_versions ON workflow_etape (workflow_version_id);
CREATE INDEX idx_etape_parent ON workflow_etape (parent_etape_id);

-- Règles de délai (SLA) applicables à une étape
CREATE TABLE IF NOT EXISTS workflow_etape_sla (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    etape_id BIGINT NOT NULL,
    duration_value INTEGER NOT NULL,
    duration_unit VARCHAR(20) NOT NULL DEFAULT 'jours',
    delay_type VARCHAR(20) NOT NULL DEFAULT 'fixe',
    description TEXT,
    CHECK (duration_unit IN ('heures', 'jours', 'semaines', 'mois')),
    CHECK (delay_type IN ('fixe', 'relatif')),
    FOREIGN KEY (etape_id) REFERENCES workflow_etape(id) ON DELETE CASCADE
);

-- Transition entre étapes – définit le graphe d’exécution du workflow
CREATE TABLE IF NOT EXISTS workflow_etape_transition (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    workflow_version_id BIGINT NOT NULL,
    from_etape_id BIGINT NOT NULL,
    to_etape_id BIGINT NOT NULL,
    transition_type VARCHAR(20) NOT NULL DEFAULT 'default',
    sequence_order INTEGER NOT NULL DEFAULT 1,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    CHECK (from_etape_id <> to_etape_id),
    CHECK (transition_type IN (
        'NEXT',
        'RETURN',
        'PARALLEL',
        'MERGE',
        'CANCEL',
        'END',
        )),
    FOREIGN KEY (workflow_version_id) REFERENCES workflow_versions(id) ON DELETE CASCADE,
    FOREIGN KEY (from_etape_id) REFERENCES workflow_etape(id) ON DELETE CASCADE,
    FOREIGN KEY (to_etape_id) REFERENCES workflow_etape(id) ON DELETE CASCADE,
    UNIQUE (workflow_version_id, from_etape_id, to_etape_id)
);

CREATE INDEX idx_transition_from ON workflow_etape_transition (from_etape_id);
CREATE INDEX idx_transition_to ON workflow_etape_transition (to_etape_id);

-- Point de décision à l’intérieur d’une étape (ex. comité de sélection)
CREATE TABLE IF NOT EXISTS decision_point (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    etape_id BIGINT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    FOREIGN KEY (etape_id) REFERENCES workflow_etape(id) ON DELETE CASCADE
);

-- Issues possibles d’un point de décision – chaque issue peut rediriger vers une autre étape
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

-- Modèle de livrable attendu pour une étape donnée
CREATE TABLE IF NOT EXISTS deliverable_template (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    etape_id BIGINT NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    is_mandatory BOOLEAN NOT NULL DEFAULT TRUE,
    FOREIGN KEY (etape_id) REFERENCES workflow_etape(id) ON DELETE CASCADE
);

-- Référentiel des rôles métier (CIP, CAR, DPF, etc.)
CREATE TABLE IF NOT EXISTS roles (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    libelle VARCHAR(100) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Utilisateurs du système (personnels)
CREATE TABLE IF NOT EXISTS personnels (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    telephone VARCHAR(20),
    mot_de_passe VARCHAR(255) NOT NULL,
    role_id BIGINT NOT NULL,
    statut TINYINT(1) NOT NULL DEFAULT 1,
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Association N‑N entre étapes et rôles (Acteur qui intervient sur quelle étape)
CREATE TABLE IF NOT EXISTS workflow_etape_roles (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    etape_id BIGINT NOT NULL,
    role_id BIGINT NOT NULL,
    responsibility TEXT,
    FOREIGN KEY (etape_id) REFERENCES workflow_etape(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    UNIQUE (etape_id, role_id)
);

-- Projets soumis, rattachés à un promoteur et à une version de workflow
CREATE TABLE IF NOT EXISTS projets (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    workflow_version_id BIGINT NOT NULL,
    promoteur_id BIGINT,
    secteur_id BIGINT,
    guichet_id BIGINT,
    agence_id BIGINT,
    titre VARCHAR(255) NOT NULL,
    type_projet ENUM('INDIVIDUEL','COLLECTIF') DEFAULT 'INDIVIDUEL',
    tranche_age ENUM('JEUNE','ADULTE','SENIOR'),
    nbre_beneficiaires INT DEFAULT 0,
    nbre_emplois INT DEFAULT 0,
    date_certification DATE,
    date_transmission_partenaire DATE,
    statut ENUM(
        'BROUILLON',
        'SOUMIS',
        'EN_COURS',
        'EN_ANALYSE',
        'EN_FORMATION',
        'EN_ATTENTE',
        'EN_CORRECTION',
        'VALIDE',
        'FINANCE',
        'DECAISSE',
        'EN_SUIVI',
        'EN_REMBOURSEMENT',
        'CONTENTIEUX',
        'TERMINE',
        'REJETE',
        'ABANDONNE'
        ) DEFAULT 'BROUILLON',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (workflow_version_id) REFERENCES workflow_versions(id)
);

-- Instance d’exécution d’un workflow pour un projet donné
CREATE TABLE IF NOT EXISTS workflow_instance (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    projet_id BIGINT NOT NULL,
    workflow_version_id BIGINT NOT NULL,
    current_etape_id BIGINT,
    status VARCHAR(20) NOT NULL DEFAULT 'en_cours' CHECK (status IN ('en_cours','termine','rejete','abandonne')),
    started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP,
    FOREIGN KEY (projet_id) REFERENCES projets(id),
    FOREIGN KEY (workflow_version_id) REFERENCES workflow_versions(id),
    FOREIGN KEY (current_etape_id) REFERENCES workflow_etape(id)
);

CREATE INDEX idx_workflow_instance_status ON workflow_instance (status);
CREATE INDEX idx_workflow_instance_current_etape ON workflow_instance (current_etape_id);

-- Historique des étapes parcourues par une instance
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

CREATE INDEX idx_history_instance ON workflow_instance_history (workflow_instance_id);
CREATE INDEX idx_history_etape ON workflow_instance_history (etape_id);

-- Documents produits pour une instance, en lien avec un modèle de livrable
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

CREATE INDEX idx_document_instance ON workflow_instance_document (workflow_instance_id);

-- Commentaires libres attachés à une étape pour une instance donnée
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


-- ##############################################################################
-- ##############################################################################
-- Trigger pour empêcher une étape d'être son propre parent
DELIMITER //
CREATE IF NOT EXISTS TRIGGER before_insert_workflow_etape
BEFORE INSERT ON workflow_etape
FOR EACH ROW
BEGIN
    IF NEW.parent_etape_id IS NOT NULL AND NEW.parent_etape_id = NEW.id THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Une étape ne peut pas être son propre parent';
    END IF;
END//
CREATE IF NOT EXISTS TRIGGER before_update_workflow_etape
BEFORE UPDATE ON workflow_etape
FOR EACH ROW
BEGIN
    IF NEW.parent_etape_id IS NOT NULL AND NEW.parent_etape_id = NEW.id THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Une étape ne peut pas être son propre parent';
    END IF;
END//
DELIMITER ;