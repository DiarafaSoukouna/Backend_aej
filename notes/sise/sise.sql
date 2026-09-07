-- ============================================================
-- 1. Niveau du cadre de résultat (Axe, Effet, Produit, etc.)
-- ============================================================
-- Référentiel des niveaux hiérarchiques du cadre de résultat (Axe, Effet, Produit...)
CREATE TABLE
    niveaux_cadre_resultat (
        id_nsc BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code_number_nsc VARCHAR(20) NOT NULL,
        libelle_nsc VARCHAR(100) NOT NULL, -- ex: "Axe", "Effet", "Produit"
        nombre_nsc INTEGER NOT NULL, -- ordre / profondeur du niveau
        programme INTEGER NULL REFERENCES programmes (id_programme) ON DELETE SET NULL,
        type_niveau VARCHAR(10) NOT NULL, -- ex: "1", "2", "3"
        CONSTRAINT uq_niveaux_cadre_resultat_code UNIQUE (code_number_nsc, programme)
    );

-- ============================================================
-- 2. Cadre de résultat (l'entité hiérarchique elle-même)
-- ============================================================
-- Éléments du cadre de résultat (axes, effets, produits...), organisés hiérarchiquement via parent_cs
CREATE TABLE
    cadres_resultat (
        id_cs BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        abgrege_cs VARCHAR(20) NOT NULL, -- ex: "OS1"
        code_cs VARCHAR(20) NOT NULL UNIQUE, -- ex: "S01"
        intutile_cs TEXT NOT NULL,
        date_enregistrement DATE NOT NULL DEFAULT CURRENT_DATE,
        date_modification DATE NULL,
        etat VARCHAR(30) NULL,
        niveau_cs INTEGER NOT NULL REFERENCES niveaux_cadre_resultat (id_nsc) ON DELETE RESTRICT,
        parent_cs INTEGER NULL REFERENCES cadres_resultat (id_cs) ON DELETE SET NULL, -- auto-référence (hiérarchie)
        partenaire_cs INTEGER NULL REFERENCES partenaires (id_partenaire) ON DELETE SET NULL
    );

-- ============================================================
-- 3. Indicateur du cadre de résultat
-- ============================================================
-- Indicateurs rattachés à un élément du cadre de résultat
CREATE TABLE
    indicateurs_cadre_resultat (
        id_indicateur_str BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code_indicateur_istr VARCHAR(30) NOT NULL UNIQUE, -- ex: "R002"
        intitule_indicateur_istr TEXT NOT NULL,
        description_istr TEXT NULL,
        code_istr INTEGER NOT NULL REFERENCES cadres_resultat (id_cs) ON DELETE CASCADE,
        niveau_istr INTEGER NULL, -- à confirmer : FK vers niveaux_cadre_resultat ou simple entier ?
        programme_istr INTEGER NULL REFERENCES programmes (id_programme) ON DELETE SET NULL,
        structure_istr INTEGER NULL REFERENCES structures (id_structure) ON DELETE SET NULL,
        periodicite_iop VARCHAR(30) NULL, -- ex: "Trimestriel"
        responsable_istr VARCHAR(100) NULL,
        source_istr VARCHAR(150) NULL
    );

-- ============================================================
-- 4. Cible de l'indicateur du cadre de résultat
-- ============================================================
-- Valeurs cibles annuelles fixées pour chaque indicateur, par programme et unité de gestion
CREATE TABLE
    cibles_indicateur_cadre_resultat (
        id_cible_indicateur_istr BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        annee DATE NOT NULL, -- ex: "2019-01-01"
        valeur_cible_indcateur_istr NUMERIC(15, 2) NOT NULL,
        code_indicateur_istr INTEGER NOT NULL REFERENCES indicateurs_cadre_resultat (id_indicateur_str) ON DELETE CASCADE,
        code_programme INTEGER NOT NULL REFERENCES programmes (code_programme) ON DELETE RESTRICT,
        code_ug INTEGER NULL REFERENCES unites_gestion (code_ug) ON DELETE SET NULL,
        CONSTRAINT uq_cible_indicateur_annee UNIQUE (
            code_indicateur_istr,
            code_programme,
            code_ug,
            annee
        )
    );

-- ============================================================
-- 5. (Inventée) Suivi / réalisations de l'indicateur du cadre de résultat
-- ============================================================
-- Suivi périodique des valeurs réalisées pour chaque indicateur du cadre de résultat, par programme et unité de gestion
CREATE TABLE
    suivis_indicateur_cadre_resultat (
        id_suivi_indicateur_istr BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code_indicateur_istr INTEGER NOT NULL REFERENCES indicateurs_cadre_resultat (id_indicateur_str) ON DELETE CASCADE,
        Date_suivi DATE NOT NULL,
        code_ug INTEGER NULL REFERENCES unites_gestion (code_ug) ON DELETE SET NULL,
        valeur_realisee_istr NUMERIC(15, 2) NOT NULL,
        commentaire_suivi_istr TEXT NULL,
        date_enregistrement TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        modifier_par VARCHAR(100) NULL,
        CONSTRAINT uq_suivi_indicateur_periode UNIQUE (
            code_indicateur_istr,
            code_programme,
            code_ug,
            periode_suivi
        )
    );

-- ============================================================
-- 6. Cadre analytique (niveau et éléments)
-- ============================================================
CREATE TABLE
    niveau_cadre_analytique (
        id_nca BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nombre_nca INT NOT NULL,
        libelle_nca VARCHAR(100) NOT NULL,
        code_number_nca VARCHAR(15) NOT NULL,
        type_niveau VARCHAR(100),
        programme_id VARCHAR(15)
    );

-- ============================================================
-- 6. Cadre analytique (niveau et éléments)
-- ============================================================
CREATE TABLE
    cadre_analytique (
        id_ca BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code_ca VARCHAR(100) NOT NULL,
        intutile_ca TEXT NOT NULL,
        abgrege_ca VARCHAR(30) NOT NULL,
        cout_axe DOUBLE PRECISION NOT NULL,
        date_enregistrement DATE NOT NULL,
        date_modification DATE NOT NULL,
        etat VARCHAR(100),
        parent_ca_id BIGINT,
        niveau_ca_id BIGINT,
        programme_ca_id BIGINT
    );
    
-- ============================================================
-- 7. Indicateurs de performance
-- ============================================================
CREATE TABLE
    indicateur_performance (
        id_indicateur_performance BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code_indicateur_performance VARCHAR(100) NOT NULL,
        intitule_indicateur_tache VARCHAR(200) NOT NULL,
        unite_indicateur_performance_id BIGINT,
        cadre_analytique_id BIGINT,
        type_ind INT NOT NULL
    );

-- ============================================================
-- 8. Cibles des indicateurs de performance
-- ============================================================
CREATE TABLE
    cible_indicateur_performance (
        id_cible_indicateur_performance BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        indicateur_performance_id BIGINT,
        valeur_cible_indcateur_performance VARCHAR(100) NOT NULL,
        code_projet_id VARCHAR(14),
        annee INT NOT NULL,
        budget_an double precision (15, 2),
    );

-- ============================================================
-- 9. PTBA
-- ============================================================
CREATE TABLE
    ptba (
        id_ptba BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code_activite_ptba VARCHAR(100) NOT NULL,
        intitule_activite_ptba VARCHAR(200) NOT NULL,
        statut_activite VARCHAR(100) NOT NULL,
        responsable_ptba_id BIGINT,
        version_ptba_id BIGINT,
        chronogramme VARCHAR(100),
        type_activite_id VARCHAR(15),
        code_programme_id VARCHAR(15),
        observation text,
        cout_ptba double precision,
        source_financement_ptba_id VARCHAR(14),
        ugl_ptba_id VARCHAR(10),
        cadre_analytique_id BIGINT,
        code_crp_id BIGINT
    );

-- ============================================================
-- 10. Paramétrages des unités d'indicateurs
-- ============================================================
CREATE TABLE
    unite_indicateur (
        id_unite BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        unite_ui VARCHAR(20) NOT NULL,
        definition_ui VARCHAR(300) NOT NULL
    );

-- ============================================================
-- 11. Suivi des activités et tâches du PTBA
-- ============================================================
CREATE TABLE
    tache_activite_ptba (
        id_groupe_tache BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        intutile_tache_gt VARCHAR(200) NOT NULL,
        proportion_gt VARCHAR(10) NOT NULL,
        code_tache_gt VARCHAR(200) NOT NULL,
        date_debut_gt DATE NOT NULL,
        date_fin_gt DATE NOT NULL,
        n_lot_gt INT NOT NULL,
        lot_realisee INT NULL,
        valide boolean NULL,
        date_reele DATE NULL,
        observation_suivi text,
        livrable_suivi VARCHAR(100),
        id_activite_id BIGINT,
        id_personnel_gt_id BIGINT,
        responsable_gt_id BIGINT
    );

-- ============================================================
-- 12. Suivi des indicateurs de tâches du PTBA
-- ============================================================
CREATE TABLE
    indicateur_tache_ptba (
        id_indicateur_tache BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        intitule_indicateur_tache VARCHAR(200) NOT NULL,
        code_indicateur_ptba VARCHAR(15) NOT NULL,
        trimestre_1 VARCHAR(100),
        trimestre_2 VARCHAR(100),
        trimestre_3 VARCHAR(100),
        trimestre_4 VARCHAR(100),
        id_activite_id BIGINT,
        responsable_ind_tache_id BIGINT,
        unite_ind_tache_id BIGINT,
        indicateur_cmr_id BIGINT
    );

-- ============================================================
-- 13. Suivi de l'avancement des contrats du PTBA
-- ============================================================
CREATE TABLE
    suivi_avancement_contrat (
        id_suivi BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        date_suivi DATE NOT NULL,
        code_suivi VARCHAR(100),
        etat_avancement text,
        statut_activite text,
        retard_accuse text,
        difficultes_rencontrees text,
        pistes_solutions text,
        observation text,
        documents text,
        date_enregistrement timestamp
        with
            time zone NOT NULL,
            etat text,
            modifier_le DATE NOT NULL,
            modifier_par text,
            activite_ptba_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_personnel_id BIGINT,
            sous_activite VARCHAR(100)
    );

-- ============================================================
-- 14. Suivi des indicateurs de tâches du PTBA
-- ============================================================
CREATE TABLE
    suivi_indicateur_tache (
        id_suivi_sit BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        valeur_suivi_sit INT NOT NULL,
        date_suivi_sit DATE NOT NULL,
        commune_sit_id BIGINT,
        indicateur_sit_id BIGINT,
        ugl_sit_id BIGINT,
        tache_suivi VARCHAR(100),
        personnel_sit_id BIGINT
    );

-- ============================================================
-- 15. Suivi des activités et tâches du PTBA
-- ============================================================
CREATE TABLE
    suivi_tache_activite (
        id_suivi_groupe_tache BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        lot_realisee double precision NOT NULL,
        valide boolean NOT NULL,
        date_reele DATE NOT NULL,
        observation_suivi text,
        livrable_suivi character varying(100),
        id_activite_ptba_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        id_groupe_tache_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        difficultes_rencontrees text,
        pistes_solutions text
    );

CREATE INDEX idx_cadres_resultat_niveau ON cadres_resultat (niveau_cs);

CREATE INDEX idx_cadres_resultat_parent ON cadres_resultat (parent_cs);

CREATE INDEX idx_cadres_resultat_partenaire ON cadres_resultat (partenaire_cs);

CREATE INDEX idx_cibles_indicateur_code_indicateur ON cibles_indicateur_cadre_resultat (code_indicateur_istr);

CREATE INDEX idx_cibles_indicateur_programme ON cibles_indicateur_cadre_resultat (code_programme);

CREATE INDEX idx_cibles_indicateur_ug ON cibles_indicateur_cadre_resultat (code_ug);

CREATE INDEX idx_suivis_indicateur_code_indicateur ON suivis_indicateur_cadre_resultat (code_indicateur_istr);

CREATE INDEX idx_indicateurs_cadre_resultat_code_istr ON indicateurs_cadre_resultat (code_istr);

CREATE INDEX idx_indicateurs_cadre_resultat_programme ON indicateurs_cadre_resultat (programme_istr);

CREATE INDEX idx_indicateurs_cadre_resultat_structure ON indicateurs_cadre_resultat (structure_istr);

CREATE INDEX idx_suivis_indicateur_programme ON suivis_indicateur_cadre_resultat (code_programme);

CREATE INDEX idx_suivis_indicateur_ug ON suivis_indicateur_cadre_resultat (code_ug);

CREATE INDEX idx_suivis_indicateur_periode ON suivis_indicateur_cadre_resultat (periode_suivi);