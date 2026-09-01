-- Base de donnees AEJ
-- Tables alignees sur les migrations Laravel disponibles.


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

CREATE TABLE niveau_localites (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nom VARCHAR(255) NOT NULL,
 code VARCHAR(255) NOT NULL UNIQUE,
 parent_id BIGINT UNSIGNED NULL,
 created_at TIMESTAMP NULL,
 updated_at TIMESTAMP NULL,
 CONSTRAINT niveau_localites_parent_id_foreign
  FOREIGN KEY (parent_id) REFERENCES niveau_localites(id) ON DELETE SET NULL
);

CREATE TABLE localites (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nom VARCHAR(255) NOT NULL,
 code VARCHAR(255) NOT NULL UNIQUE,
 niveau_localite_id BIGINT UNSIGNED NOT NULL,
 couche_cartographique VARCHAR(255) NULL,
 created_at TIMESTAMP NULL,
 updated_at TIMESTAMP NULL,
 CONSTRAINT localites_niveau_localite_id_foreign
  FOREIGN KEY (niveau_localite_id) REFERENCES niveau_localites(id) ON DELETE CASCADE
);

CREATE TABLE configurations (
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
 taux_devise_principale DECIMAL(10,2) NOT NULL,
 mise_en_maintenance TINYINT(1) NOT NULL DEFAULT 0,
 delai_inactivite_minutes INT NOT NULL,
 nombre_session_possible INT NOT NULL,
 nombre_tentatives_connexion INT NOT NULL,
 delai_code_otp_minutes INT NOT NULL,
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

CREATE TABLE roles (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 code VARCHAR(255) NOT NULL UNIQUE,
 libelle VARCHAR(255) NOT NULL UNIQUE,
 description TEXT NULL,
 created_at TIMESTAMP NULL,
 updated_at TIMESTAMP NULL
);

CREATE TABLE permissions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 role_id BIGINT UNSIGNED NOT NULL,
 module VARCHAR(100) NOT NULL,
 autorise TINYINT(1) NOT NULL DEFAULT 0,
 acces VARCHAR(255) NULL,
 full_access TINYINT(1) NOT NULL DEFAULT 0,
 created_at TIMESTAMP NULL,
 updated_at TIMESTAMP NULL,
 CONSTRAINT permissions_role_id_foreign
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

CREATE TABLE personnels (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nom VARCHAR(255) NOT NULL,
 prenom VARCHAR(255) NOT NULL,
 email VARCHAR(255) NOT NULL UNIQUE,
 telephone VARCHAR(255) NULL,
 adresse VARCHAR(255) NULL,
 mot_de_passe VARCHAR(255) NOT NULL,
 role_id BIGINT UNSIGNED NOT NULL,
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 fonction_id BIGINT UNSIGNED NOT NULL,
 remember_token VARCHAR(255) NULL,
 created_at TIMESTAMP NULL,
 updated_at TIMESTAMP NULL,
 CONSTRAINT personnels_role_id_foreign
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
 CONSTRAINT personnels_fonction_id_foreign
  FOREIGN KEY (fonction_id) REFERENCES fonctions(id) ON DELETE CASCADE
);

CREATE TABLE personal_access_tokens (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 tokenable_type VARCHAR(255) NOT NULL,
 tokenable_id BIGINT UNSIGNED NOT NULL,
 name TEXT NOT NULL,
 token VARCHAR(64) NOT NULL UNIQUE,
 abilities TEXT NULL,
 last_used_at TIMESTAMP NULL,
 expires_at TIMESTAMP NULL,
 created_at TIMESTAMP NULL,
 updated_at TIMESTAMP NULL,
 INDEX personal_access_tokens_tokenable_type_tokenable_id_index (tokenable_type, tokenable_id),
 INDEX personal_access_tokens_expires_at_index (expires_at)
);

CREATE TABLE type_organismes (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 code VARCHAR(255) NOT NULL UNIQUE,
 libelle VARCHAR(255) NOT NULL,
 created_at TIMESTAMP NULL,
 updated_at TIMESTAMP NULL
);

CREATE TABLE organismes (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nom VARCHAR(200) NOT NULL,
 sigle VARCHAR(50) NOT NULL,
 type BIGINT UNSIGNED NOT NULL,
 site_web VARCHAR(255) NULL,
 description TEXT NULL,
 adresse VARCHAR(255) NULL,
 telephone VARCHAR(20) NULL,
 email VARCHAR(100) NULL,
 created_at TIMESTAMP NULL,
 updated_at TIMESTAMP NULL,
 CONSTRAINT organismes_type_foreign
  FOREIGN KEY (type) REFERENCES type_organismes(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE indicateurs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nom VARCHAR(255) NOT NULL UNIQUE,
 description TEXT NULL,
 type_valeur VARCHAR(255) NULL,
 unite VARCHAR(255) NULL,
 statut TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP NULL,
 updated_at TIMESTAMP NULL
);

-- Tables presentes dans base.sql sans migration correspondante.

CREATE TABLE branches_activite (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 code VARCHAR(30) UNIQUE,
 libelle VARCHAR(150) NOT NULL
);

CREATE TABLE jeunes (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nom VARCHAR(100) NOT NULL,
 prenom VARCHAR(100) NOT NULL,
 sexe ENUM('masculin','feminin') NOT NULL,
 date_naissance DATE NOT NULL,
 lieu_naissance VARCHAR(150),
 numero_piece_identite VARCHAR(50) NOT NULL UNIQUE,
 photo_path VARCHAR(255),
 telephone VARCHAR(20) NOT NULL UNIQUE,
 email VARCHAR(180) UNIQUE,
 mot_de_passe VARCHAR(255) NOT NULL,
 statut TINYINT(1) DEFAULT 1,
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL
);

CREATE TABLE entreprises (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 raison_sociale VARCHAR(200) NOT NULL,
 sigle VARCHAR(30),
 rccm VARCHAR(50) UNIQUE,
 ninea VARCHAR(50) UNIQUE,
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL
);

CREATE TABLE financements (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 code_financement VARCHAR(30) UNIQUE NOT NULL,
 jeune_id BIGINT UNSIGNED,
 organisme_id BIGINT UNSIGNED NOT NULL,
 montant_demande DECIMAL(15,2) NOT NULL,
 montant_octroye DECIMAL(15,2),
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL
);

CREATE TABLE projets (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 financement_id BIGINT UNSIGNED NOT NULL UNIQUE,
 titre VARCHAR(200) NOT NULL,
 description TEXT NOT NULL,
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL
);

CREATE TABLE budgets_projets (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 projet_id BIGINT UNSIGNED NOT NULL UNIQUE,
 budget_alloue DECIMAL(15,2) NOT NULL,
 devise VARCHAR(10) NOT NULL DEFAULT 'FCFA',
 date_debut_budget DATE,
 date_fin_budget DATE,
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL
);

CREATE TABLE depenses_projets (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 projet_id BIGINT UNSIGNED NOT NULL,
 categorie ENUM('materiel','stock','salaires','charges','transport','autre') NOT NULL,
 libelle VARCHAR(200) NOT NULL,
 montant DECIMAL(15,2) NOT NULL,
 date_depense DATE NOT NULL,
 justificatif_path VARCHAR(255),
 saisi_par BIGINT UNSIGNED,
 note TEXT,
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL
);

CREATE TABLE remboursements (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 financement_id BIGINT UNSIGNED NOT NULL,
 jeune_id BIGINT UNSIGNED NOT NULL,
 projet_id BIGINT UNSIGNED NOT NULL,
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL
);

CREATE TABLE indicateurs_suivi (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 indicateur_id BIGINT UNSIGNED NOT NULL,
 jeune_id BIGINT UNSIGNED NOT NULL,
 valeur VARCHAR(255) NOT NULL,
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL
);

CREATE TABLE suivis (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 jeune_id BIGINT UNSIGNED NOT NULL,
 intitule VARCHAR(200) NOT NULL,
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL
);

CREATE TABLE embauches (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 jeune_id BIGINT UNSIGNED NOT NULL,
 entreprise_id BIGINT UNSIGNED NOT NULL,
 poste VARCHAR(200) NOT NULL,
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL
);

CREATE TABLE notifications (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 personnel_id BIGINT UNSIGNED NOT NULL,
 titre VARCHAR(200) NOT NULL,
 message TEXT NOT NULL,
 created_at DATETIME NOT NULL
);

CREATE TABLE formulaires_evaluation (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 code VARCHAR(50) NOT NULL UNIQUE,
 libelle VARCHAR(200) NOT NULL,
 public_cible VARCHAR(50) NOT NULL,
 actif TINYINT(1) NOT NULL DEFAULT 1,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE questions_evaluation (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 formulaire_id BIGINT UNSIGNED NOT NULL,
 code VARCHAR(50) NOT NULL,
 libelle TEXT NOT NULL,
 type_question VARCHAR(50) NOT NULL,
 options JSON DEFAULT NULL,
 ordre SMALLINT NOT NULL DEFAULT 0,
 affichage BOOLEAN DEFAULT NULL,
 obligatoire TINYINT(1) NOT NULL DEFAULT 1,
 CONSTRAINT fk_questions_formulaire
  FOREIGN KEY (formulaire_id) REFERENCES formulaires_evaluation(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE evaluations (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 formulaire_id BIGINT UNSIGNED NOT NULL,
 cible_type VARCHAR(50) NOT NULL,
 evaluateur_id BIGINT UNSIGNED NOT NULL,
 date_evaluation DATETIME NOT NULL,
 score_global DECIMAL(5,2) DEFAULT NULL,
 commentaire TEXT DEFAULT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_evaluations_formulaire
  FOREIGN KEY (formulaire_id) REFERENCES formulaires_evaluation(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE reponses_evaluation (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 evaluation_id BIGINT UNSIGNED NOT NULL,
 question_id BIGINT UNSIGNED NOT NULL,
 reponse_texte TEXT DEFAULT NULL,
 INDEX idx_reponses_evaluation (evaluation_id),
 CONSTRAINT fk_reponses_evaluation
  FOREIGN KEY (evaluation_id) REFERENCES evaluations(id) ON DELETE CASCADE ON UPDATE CASCADE,
 CONSTRAINT fk_reponses_question
  FOREIGN KEY (question_id) REFERENCES questions_evaluation(id) ON DELETE CASCADE ON UPDATE CASCADE
);
