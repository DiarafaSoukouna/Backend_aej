
-- Base de données AEJ

CREATE TABLE roles (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 code VARCHAR(50) NOT NULL UNIQUE,
 libelle VARCHAR(100) NOT NULL,
 description TEXT NULL,
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL
);

CREATE TABLE personnels (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 nom VARCHAR(100) NOT NULL,
 prenom VARCHAR(100) NOT NULL,
 email VARCHAR(180) NOT NULL UNIQUE,
 telephone VARCHAR(20),
 mot_de_passe VARCHAR(255) NOT NULL,
 role_id BIGINT NOT NULL,
 statut TINYINT(1) NOT NULL DEFAULT 1,
 remember_token VARCHAR(100),
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL
);

CREATE TABLE permissions (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 role_id BIGINT NOT NULL,
 module VARCHAR(100),
 autorise BOOLEAN,
 acces VARCHAR(255),
 full_access BOOLEAN,
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL
);

CREATE TABLE niveaux_localites (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 parent_id BIGINT NULL,
 code VARCHAR(10) NOT NULL UNIQUE,
 nom VARCHAR(100) NOT NULL,
 created_at DATETIME NOT NULL
);

CREATE TABLE localites (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 niveau_id BIGINT NOT NULL,
 code VARCHAR(20) NOT NULL UNIQUE,
 couche_carto VARCHAR(255),
 nom VARCHAR(150) NOT NULL,
 created_at DATETIME NOT NULL
);

CREATE TABLE types_entreprise (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 code VARCHAR(30) UNIQUE,
 libelle VARCHAR(100) NOT NULL

);

CREATE TABLE branches_activite (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 code VARCHAR(30) UNIQUE,
 libelle VARCHAR(150) NOT NULL
);

CREATE TABLE jeunes (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
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
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 raison_sociale VARCHAR(200) NOT NULL,
 sigle VARCHAR(30),
 rccm VARCHAR(50) UNIQUE,
 ninea VARCHAR(50) UNIQUE,
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL
);

CREATE TABLE organismes (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 nom VARCHAR(200) NOT NULL,
 sigle VARCHAR(30),
 type ENUM('banque','fonds','cooperation','etat','ong') NOT NULL,
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL
);

CREATE TABLE financements (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 code_financement VARCHAR(30) UNIQUE NOT NULL,
 jeune_id BIGINT,
 organisme_id BIGINT NOT NULL,
 montant_demande DECIMAL(15,2) NOT NULL,
 montant_octroye DECIMAL(15,2),
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL
);

CREATE TABLE projets (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 financement_id BIGINT NOT NULL UNIQUE,
 titre VARCHAR(200) NOT NULL,
 description TEXT NOT NULL,
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL
);

CREATE TABLE budgets_projets (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 projet_id BIGINT NOT NULL UNIQUE,
 budget_alloue DECIMAL(15,2) NOT NULL,
 devise VARCHAR(10) NOT NULL DEFAULT 'FCFA',
 date_debut_budget DATE,
 date_fin_budget DATE,
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL
);

CREATE TABLE depenses_projets (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 projet_id BIGINT NOT NULL,
 categorie ENUM('materiel','stock','salaires','charges','transport','autre') NOT NULL,
 libelle VARCHAR(200) NOT NULL,
 montant DECIMAL(15,2) NOT NULL,
 date_depense DATE NOT NULL,
 justificatif_path VARCHAR(255),
 saisi_par BIGINT,
 note TEXT,
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL
);

CREATE TABLE remboursements (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 financement_id BIGINT NOT NULL,
 jeune_id BIGINT NOT NULL,
 projet_id BIGINT NOT NULL,
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL
);

CREATE TABLE indicateurs (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 nom VARCHAR(150) NOT NULL UNIQUE,
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL
);

CREATE TABLE indicateurs_suivi (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 indicateur_id BIGINT NOT NULL,
 jeune_id BIGINT NOT NULL,
 valeur VARCHAR(255) NOT NULL,
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL
);

CREATE TABLE suivis (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 jeune_id BIGINT NOT NULL,
 intitule VARCHAR(200) NOT NULL,
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL
);

CREATE TABLE embauches (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 jeune_id BIGINT NOT NULL,
 entreprise_id BIGINT NOT NULL,
 poste VARCHAR(200) NOT NULL,
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL
);

CREATE TABLE notifications (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 personnel_id BIGINT NOT NULL,
 titre VARCHAR(200) NOT NULL,
 message TEXT NOT NULL,
 created_at DATETIME NOT NULL
);

CREATE TABLE configurations (
 id BIGINT AUTO_INCREMENT PRIMARY KEY,
 nom_systeme VARCHAR(255) NOT NULL,
 sigle_systeme VARCHAR(255) NOT NULL
);
