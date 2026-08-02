CREATE TABLE
    IF NOT EXISTS workflows (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(20) NOT NULL UNIQUE,
        name VARCHAR(150) NOT NULL,
        description TEXT,
        is_active BOOLEAN NOT NULL DEFAULT TRUE,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS workflow_versions (
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
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS workflow_etapes (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        version_id BIGINT UNSIGNED NOT NULL,
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
        FOREIGN KEY (version_id) REFERENCES workflow_versions (id) ON DELETE CASCADE,
        FOREIGN KEY (parent_etape_id) REFERENCES workflow_etapes (id) ON DELETE CASCADE,
        UNIQUE (version_id, code)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS workflow_etapes_sla (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        etape_id BIGINT UNSIGNED NOT NULL,
        duration_value INTEGER NOT NULL,
        duration_unit VARCHAR(20) NOT NULL DEFAULT 'JOURS',
        delay_type VARCHAR(20) NOT NULL DEFAULT 'FIXE',
        description TEXT,
        CHECK (
            duration_unit IN ('HEURES', 'JOURS', 'SEMAINES', 'MOIS')
        ),
        CHECK (delay_type IN ('FIXE', 'RELATIF')),
        FOREIGN KEY (etape_id) REFERENCES workflow_etapes (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS workflow_etapes_deliverable (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        etape_id BIGINT UNSIGNED NOT NULL,
        name VARCHAR(200) NOT NULL,
        description TEXT,
        is_mandatory BOOLEAN NOT NULL DEFAULT TRUE,
        FOREIGN KEY (etape_id) REFERENCES workflow_etapes (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS workflow_etapes_roles (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        etape_id BIGINT UNSIGNED NOT NULL,
        role_id BIGINT UNSIGNED NOT NULL,
        responsibility TEXT,
        FOREIGN KEY (etape_id) REFERENCES workflow_etapes (id) ON DELETE CASCADE,
        FOREIGN KEY (role_id) REFERENCES roles (id) ON DELETE CASCADE,
        UNIQUE (etape_id, role_id)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS workflow_etapes_transition (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        version_id BIGINT UNSIGNED NOT NULL,
        from_etape_id BIGINT UNSIGNED NOT NULL,
        to_etape_id BIGINT UNSIGNED NOT NULL,
        transition_type VARCHAR(20) NOT NULL DEFAULT 'DEFAULT',
        sequence_order INTEGER NOT NULL DEFAULT 1,
        is_active BOOLEAN NOT NULL DEFAULT TRUE,
        CHECK (from_etape_id <> to_etape_id),
        CHECK (
            transition_type IN (
                'NEXT',
                'RETURN',
                'PARALLEL',
                'MERGE',
                'CANCEL',
                'END'
            )
        ),
        FOREIGN KEY (version_id) REFERENCES workflow_versions (id) ON DELETE CASCADE,
        FOREIGN KEY (from_etape_id) REFERENCES workflow_etapes (id) ON DELETE CASCADE,
        FOREIGN KEY (to_etape_id) REFERENCES workflow_etapes (id) ON DELETE CASCADE,
        UNIQUE (version_id, from_etape_id, to_etape_id)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS workflow_etapes_decision (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        etape_id BIGINT UNSIGNED NOT NULL,
        name VARCHAR(150) NOT NULL,
        description TEXT,
        FOREIGN KEY (etape_id) REFERENCES workflow_etapes (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    IF NOT EXISTS workflow_decision_outcome (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        decision_id BIGINT UNSIGNED NOT NULL,
        code VARCHAR(30) NOT NULL,
        label VARCHAR(150) NOT NULL,
        next_etape_id BIGINT UNSIGNED,
        FOREIGN KEY (decision_id) REFERENCES workflow_etapes_decision (id) ON DELETE CASCADE,
        FOREIGN KEY (next_etape_id) REFERENCES workflow_etapes (id),
        UNIQUE (decision_id, code)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE INDEX idx_etape_workflow_versions ON workflow_etapes (version_id);

CREATE INDEX idx_etape_parent ON workflow_etapes (parent_etape_id);

CREATE INDEX idx_transition_from ON workflow_etapes_transition (from_etape_id);

CREATE INDEX idx_transition_to ON workflow_etapes_transition (to_etape_id);

CREATE TABLE
    IF NOT EXISTS workflow_instance (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        micro_projet_id BIGINT UNSIGNED NOT NULL,
        version_id BIGINT UNSIGNED NOT NULL,
        current_etape_id BIGINT UNSIGNED,
        status VARCHAR(20) NOT NULL DEFAULT 'EN_COURS' CHECK (
            status IN ('EN_COURS', 'TERMINE', 'REJETE', 'ABANDONNE')
        ),
        started_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        completed_at TIMESTAMP NULL,
        FOREIGN KEY (micro_projet_id) REFERENCES micro_projets (id) ON DELETE CASCADE,
        FOREIGN KEY (version_id) REFERENCES workflow_versions (id),
        FOREIGN KEY (current_etape_id) REFERENCES workflow_etapes (id)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE INDEX idx_workflow_instance_status ON workflow_instance (status);

CREATE INDEX idx_workflow_instance_current_etape ON workflow_instance (current_etape_id);

CREATE TABLE
    IF NOT EXISTS workflow_instance_history (
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
        FOREIGN KEY (etape_id) REFERENCES workflow_etapes (id),
        FOREIGN KEY (role_id) REFERENCES roles (id),
        FOREIGN KEY (performed_by_id) REFERENCES personnels (id),
        FOREIGN KEY (decision_outcome_id) REFERENCES workflow_decision_outcome (id)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE INDEX idx_history_instance ON workflow_instance_history (workflow_instance_id);

CREATE INDEX idx_history_etape ON workflow_instance_history (etape_id);

CREATE TABLE
    IF NOT EXISTS workflow_instance_document (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        workflow_instance_id BIGINT UNSIGNED NOT NULL,
        deliverable_id BIGINT UNSIGNED,
        file_reference VARCHAR(500),
        produced_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        produced_by_id BIGINT UNSIGNED,
        FOREIGN KEY (workflow_instance_id) REFERENCES workflow_instance (id) ON DELETE CASCADE,
        FOREIGN KEY (deliverable_id) REFERENCES workflow_etapes_deliverable (id),
        FOREIGN KEY (produced_by_id) REFERENCES personnels (id)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE INDEX idx_document_instance ON workflow_instance_document (workflow_instance_id);

CREATE TABLE
    IF NOT EXISTS workflow_instance_comment (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        workflow_instance_id BIGINT UNSIGNED NOT NULL,
        etape_id BIGINT UNSIGNED NOT NULL,
        commented_by_id BIGINT UNSIGNED,
        comment TEXT,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (workflow_instance_id) REFERENCES workflow_instance (id) ON DELETE CASCADE,
        FOREIGN KEY (etape_id) REFERENCES workflow_etapes (id),
        FOREIGN KEY (commented_by_id) REFERENCES personnels (id)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

SET
    FOREIGN_KEY_CHECKS = 1;

-- ##############################################################
-- 19. TRIGGERS
-- ##############################################################
DELIMITER //

CREATE TRIGGER before_insert_workflow_etape
BEFORE INSERT ON workflow_etapes
FOR EACH ROW
BEGIN
    IF NEW.parent_etape_id IS NOT NULL AND NEW.parent_etape_id = NEW.id THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Une étape ne peut pas être son propre parent';
    END IF;
END//

CREATE TRIGGER before_update_workflow_etape
BEFORE UPDATE ON workflow_etapes
FOR EACH ROW
BEGIN
    IF NEW.parent_etape_id IS NOT NULL AND NEW.parent_etape_id = NEW.id THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Une étape ne peut pas être son propre parent';
    END IF;
END//

DELIMITER ;