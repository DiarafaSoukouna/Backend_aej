-- ============================================================
-- 1. Niveau du cadre de résultat (Axe, Effet, Produit, etc.)
-- ============================================================
-- Référentiel des niveaux hiérarchiques du cadre de résultat (Axe, Effet, Produit...)
CREATE TABLE
    niveaux_cadre_resultat (
        id_nsc SERIAL PRIMARY KEY,
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
        id_cs SERIAL PRIMARY KEY,
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
        id_indicateur_str SERIAL PRIMARY KEY,
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
        id_cible_indicateur_istr SERIAL PRIMARY KEY,
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
        id_suivi_indicateur_istr SERIAL PRIMARY KEY,
        code_indicateur_istr INTEGER NOT NULL REFERENCES indicateurs_cadre_resultat (id_indicateur_str) ON DELETE CASCADE,
        Date_suivi date NOT NULL code_ug INTEGER NULL REFERENCES unites_gestion (code_ug) ON DELETE SET NULL,
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
        id_nca integer NOT NULL,
        nombre_nca integer NOT NULL,
        libelle_nca character varying(100) NOT NULL,
        code_number_nca character varying(15) NOT NULL,
        type_niveau character varying(100),
        programme_id character varying(15)
    );


CREATE TABLE
    cadre_analytique (
        id_ca integer NOT NULL,
        code_ca character varying NOT NULL,
        intutile_ca text NOT NULL,
        abgrege_ca character varying(30) NOT NULL,
        cout_axe double precision NOT NULL,
        date_enregistrement date NOT NULL,
        date_modification date NOT NULL,
        etat character varying(100),
        parent_ca_id integer,
        niveau_ca_id integer,
        programme_ca_id integer
    );
    
-- ============================================================
-- 7. Indicateurs de performance
-- ============================================================
CREATE TABLE
    indicateur_performance (
        id_indicateur_performance integer NOT NULL,
        code_indicateur_performance character varying(100) NOT NULL,
        intitule_indicateur_tache character varying(200) NOT NULL,
        unite_indicateur_performance_id integer,
        cadre_analytique_id integer,
        type_ind integer NOT NULL
    );

-- ============================================================
-- 8. Cibles des indicateurs de performance
-- ============================================================
CREATE TABLE
    cible_indicateur_performance (
        id_cible_indicateur_performance integer NOT NULL,
        indicateur_performance_id integer,
        valeur_cible_indcateur_performance character varying(100) NOT NULL,
        code_projet_id character varying(14),
        annee integer NOT NULL,
        budget_an integer
    );

-- ============================================================
-- 9. Suivi des indicateurs de performance
-- ============================================================
CREATE TABLE
    ptba (
        id_ptba integer NOT NULL,
        code_activite_ptba character varying(100) NOT NULL,
        intitule_activite_ptba character varying(200) NOT NULL,
        statut_activite character varying(100) NOT NULL,
        responsable_ptba_id integer,
        version_ptba_id integer,
        chronogramme character varying(100),
        type_activite_id character varying(15),
        code_programme_id character varying(15),
        observation text,
        cout_ptba double precision,
        source_financement_ptba_id character varying(14),
        ugl_ptba_id character varying(10),
        cadre_analytique_id integer,
        code_crp_id integer
    );

-- ============================================================
-- 10. Paramétrages des unités d'indicateurs
-- ============================================================
CREATE TABLE
    public.parametrages_uniteindicateur (
        id_unite integer NOT NULL,
        unite_ui character varying(20) NOT NULL,
        definition_ui character varying(300) NOT NULL
    );

-- ============================================================
-- 11. Suivi des activités et tâches du PTBA
-- ============================================================
CREATE TABLE
    tache_activite_ptba (
        id_groupe_tache integer NOT NULL,
        intutile_tache_gt character varying(200) NOT NULL,
        proportion_gt character varying(10) NOT NULL,
        code_tache_gt character varying(200) NOT NULL,
        date_debut_gt date NOT NULL,
        date_fin_gt date NOT NULL,
        n_lot_gt integer NOT NULL,
        lot_realisee integer NULL,
        valide boolean NULL,
        date_reele date NULL,
        observation_suivi text,
        livrable_suivi character varying(100),
        id_activite_id integer,
        id_personnel_gt_id integer,
        responsable_gt_id integer
    );

-- ============================================================
-- 12. Suivi des indicateurs de tâches du PTBA
-- ============================================================
CREATE TABLE
    indicateur_tache_ptba (
        id_indicateur_tache integer NOT NULL,
        intitule_indicateur_tache character varying(200) NOT NULL,
        code_indicateur_ptba character varying(15) NOT NULL,
        trimestre_1 character varying(100),
        trimestre_2 character varying(100),
        trimestre_3 character varying(100),
        trimestre_4 character varying(100),
        id_activite_id integer,
        responsable_ind_tache_id integer,
        unite_ind_tache_id integer,
        indicateur_cmr_id integer
    );

-- ============================================================
-- 13. Suivi de l'avancement des contrats du PTBA
-- ============================================================
CREATE TABLE
    suivi_avancement_contrat (
        id_suivi integer NOT NULL,
        date_suivi date NOT NULL,
        code_suivi character varying(100),
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
            modifier_le date NOT NULL,
            modifier_par text,
            activite_ptba_id integer NOT NULL,
            id_personnel_id integer,
            sous_activite character varying
    );

-- ============================================================
-- 14. Suivi des indicateurs de tâches du PTBA
-- ============================================================
CREATE TABLE
    suivi_indicateur_tache (
        id_suivi_sit integer NOT NULL,
        valeur_suivi_sit integer NOT NULL,
        date_suivi_sit date NOT NULL,
        commune_sit_id integer,
        indicateur_sit_id integer,
        ugl_sit_id integer,
        tache_suivi character varying,
        personnel_sit_id integer
    );

-- ============================================================
-- 15. Suivi des activités et tâches du PTBA
-- ============================================================
CREATE TABLE
    suivi_tache_activite (
        id_suivi_groupe_tache integer NOT NULL,
        lot_realisee double precision NOT NULL,
        valide boolean NOT NULL,
        date_reele date NOT NULL,
        observation_suivi text,
        livrable_suivi character varying(100),
        id_activite_ptba_id integer,
        id_groupe_tache_id integer,
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