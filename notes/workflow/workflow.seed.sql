-- =====================================================================
-- SEED : MODÈLES DE WORKFLOW PAR DÉFAUT
-- =====================================================================
-- 1. Insertion des dispositifs (workflows)
INSERT INTO
    workflows (code, name, description, is_active)
VALUES
    (
        'AGR_CLASSIQUE',
        'PROCÉDURE AGR CLASSIQUES',
        'Procédure pour les Activités Génératrices de Revenus classiques',
        TRUE
    ),
    (
        'AGR_PLUS',
        'PROCÉDURE AGR PLUS',
        'Procédure pour les Activités Génératrices de Revenus renforcées',
        TRUE
    ),
    (
        'MPE',
        'Procédure d''Exécution des Micro et Petites Entreprises',
        'Procédure MPE',
        TRUE
    ),
    (
        'MEPS',
        'Procédure d''Exécution des Moyennes Entreprises et Projets Structurants',
        'Procédure MEPS',
        TRUE
    ),
    (
        'CAPITAL_INVEST',
        'PROCÉDURES D''EXECUTION DU CAPITAL INVESTISSEMENT',
        'Procédure Capital Investissement',
        TRUE
    ),
    (
        'MENTORAT',
        'PROCÉDURES D''EXECUTION DU MENTORAT SOLIDAIRE',
        'Procédure Mentorat Solidaire',
        TRUE
    ),
    (
        'PERMIS',
        'PROCÉDURE D''EXECUTION DES FORMATIONS AU PERMIS DE CONDUIRE',
        'Procédure Formation Permis',
        TRUE
    ),
    (
        'STARTUP_BOOST',
        'PROCÉDURES D''EXECUTION DES START-UP BOOST',
        'Procédure Start-Up Boost',
        TRUE
    );

-- 2. Insertion d'une version par défaut (2026) pour chaque workflow
INSERT INTO
    workflow_versions (
        workflow_id,
        name,
        description,
        version,
        is_active,
        is_default
    )
SELECT
    w.id,
    CONCAT (w.name, ' - 2026'),
    CONCAT ('Version 2026 de ', w.name),
    '2026',
    TRUE,
    TRUE
FROM
    workflows w;

-- 3. Définition des cycles communs (niveau 1) pour chaque version
-- On va créer pour chaque version une liste d'étapes (cycles) avec des codes et noms standardisés.
-- Les codes sont uniques par version (workflow_version_id, code) donc on peut utiliser 'PRCO_1' à 'PRCO_7'
-- On utilise une table temporaire pour stocker la correspondance version_id -> liste d'étapes
-- Mais on peut le faire en une seule requête avec UNION et sous-requêtes.
-- Pour chaque version, on insère les 7 étapes.
INSERT INTO
    workflow_etape (
        workflow_version_id,
        parent_etape_id,
        code,
        name,
        description,
        level,
        sequence_order,
        is_active
    )
SELECT
    v.id,
    NULL, -- pas de parent (cycle racine)
    CONCAT ('PRCO_', seq.num), -- code: PRCO_1, PRCO_2, ...
    CASE seq.num
        WHEN 1 THEN 'Travaux préparatoires'
        WHEN 2 THEN 'Information et sensibilisation'
        WHEN 3 THEN 'Soumission des candidatures'
        WHEN 4 THEN 'Sélection'
        WHEN 5 THEN 'Financement'
        WHEN 6 THEN 'Formation'
        WHEN 7 THEN 'Suivi des financements et remboursements'
    END,
    CASE seq.num
        WHEN 1 THEN 'Étape préparatoire : identification des bénéficiaires, diagnostic, etc.'
        WHEN 2 THEN 'Campagne d''information et de sensibilisation des candidats'
        WHEN 3 THEN 'Réception et enregistrement des dossiers de candidature'
        WHEN 4 THEN 'Évaluation et sélection des projets par le comité'
        WHEN 5 THEN 'Décaissement des fonds et conventionnement'
        WHEN 6 THEN 'Formation des bénéficiaires (gestion, technique, etc.)'
        WHEN 7 THEN 'Suivi des remboursements et de l''impact'
    END,
    1, -- niveau 1 (cycle)
    seq.num, -- ordre séquentiel
    TRUE
FROM
    workflow_versions v
    JOIN (
        SELECT
            1 AS num
        UNION
        SELECT
            2
        UNION
        SELECT
            3
        UNION
        SELECT
            4
        UNION
        SELECT
            5
        UNION
        SELECT
            6
        UNION
        SELECT
            7
    ) seq
WHERE
    v.is_default = TRUE -- on ne traite que les versions par défaut
ORDER BY
    v.id,
    seq.num;

-- 4. Insertion des transitions séquentielles entre les cycles (ordre croissant)
-- Pour chaque version, on crée une transition de chaque étape i vers i+1
INSERT INTO
    workflow_etape_transition (
        workflow_version_id,
        from_etape_id,
        to_etape_id,
        transition_type,
        sequence_order,
        is_active
    )
SELECT
    v.id,
    from_etape.id,
    to_etape.id,
    'default',
    from_etape.sequence_order, -- on reprend l'ordre de l'étape source
    TRUE
FROM
    workflow_versions v
    JOIN workflow_etape from_etape ON from_etape.workflow_version_id = v.id
    JOIN workflow_etape to_etape ON to_etape.workflow_version_id = v.id
    AND to_etape.sequence_order = from_etape.sequence_order + 1
WHERE
    v.is_default = TRUE
    AND from_etape.level = 1
    AND to_etape.level = 1
ORDER BY
    v.id,
    from_etape.sequence_order;

-- 5. Ajout d'un point de décision sur l'étape "Sélection" (PRCO_4) pour chaque version
INSERT INTO
    decision_point (etape_id, name, description)
SELECT
    e.id,
    'Décision du comité de sélection',
    'Issue de l''évaluation du projet par le comité'
FROM
    workflow_etape e
    JOIN workflow_versions v ON e.workflow_version_id = v.id
WHERE
    v.is_default = TRUE
    AND e.code = 'PRCO_4';

-- 6. Issues possibles pour chaque point de décision
-- Pour chaque point de décision, on insère trois issues: ACCEPTE, REJETE, ATTENTE
-- ACCEPTE redirige vers l'étape "Financement" (PRCO_5)
-- REJETE ne redirige nulle part (fin du workflow)
-- ATTENTE redirige vers la même étape (PRCO_4) pour reprise
INSERT INTO
    decision_outcome (decision_point_id, code, label, next_etape_id)
SELECT
    dp.id,
    'ACCEPTE',
    'Projet accepté',
    (
        SELECT
            e2.id
        FROM
            workflow_etape e2
            JOIN workflow_versions v2 ON e2.workflow_version_id = v2.id
        WHERE
            v2.is_default = TRUE
            AND e2.code = 'PRCO_5'
            AND e2.workflow_version_id = (
                SELECT
                    workflow_version_id
                FROM
                    workflow_etape
                WHERE
                    id = dp.etape_id
            )
    )
FROM
    decision_point dp;

INSERT INTO
    decision_outcome (decision_point_id, code, label, next_etape_id)
SELECT
    dp.id,
    'REJETE',
    'Projet rejeté',
    NULL
FROM
    decision_point dp;

INSERT INTO
    decision_outcome (decision_point_id, code, label, next_etape_id)
SELECT
    dp.id,
    'ATTENTE',
    'Projet en liste d''attente',
    dp.etape_id -- retour à la même étape
FROM
    decision_point dp;

-- 7. Ajout de modèles de livrables pour chaque étape (exemple)
INSERT INTO
    deliverable_template (etape_id, name, description, is_mandatory)
SELECT
    e.id,
    CONCAT ('PV de l''étape ', e.name),
    CONCAT (
        'Procès-verbal attestant de la réalisation de l''étape ',
        e.name
    ),
    TRUE
FROM
    workflow_etape e
    JOIN workflow_versions v ON e.workflow_version_id = v.id
WHERE
    v.is_default = TRUE;

-- Pour l'étape sélection, ajout d'un livrable spécifique
INSERT INTO
    deliverable_template (etape_id, name, description, is_mandatory)
SELECT
    e.id,
    'PV de sélection',
    'Procès-verbal du comité de sélection',
    TRUE
FROM
    workflow_etape e
    JOIN workflow_versions v ON e.workflow_version_id = v.id
WHERE
    v.is_default = TRUE
    AND e.code = 'PRCO_4';

-- Pour l'étape financement, ajout d'un livrable "Convention de prêt"
INSERT INTO
    deliverable_template (etape_id, name, description, is_mandatory)
SELECT
    e.id,
    'Convention de financement',
    'Convention signée entre le bénéficiaire et l''institution',
    TRUE
FROM
    workflow_etape e
    JOIN workflow_versions v ON e.workflow_version_id = v.id
WHERE
    v.is_default = TRUE
    AND e.code = 'PRCO_5';

-- 8. Insertion des rôles (si absents)
INSERT IGNORE INTO roles (code, libelle, description)
VALUES
    (
        'CIP',
        'Conseiller en Insertion Professionnelle',
        'Rôle décentralisé'
    ),
    ('AC', 'Assistant Conseiller', 'Rôle décentralisé'),
    (
        'CAR',
        'Chef d''Agence Régionale',
        'Rôle décentralisé'
    ),
    (
        'DPF',
        'Direction du Partenariat et du Financement',
        'Rôle central'
    ),
    (
        'DAICG',
        'Direction de l''Audit Interne et du Contrôle de Gestion',
        'Rôle central'
    );

-- 9. Association des rôles aux étapes (workflow_etape_roles)
-- On attribue par défaut le rôle CIP à toutes les étapes, et CAR pour les étapes de sélection et financement.
-- On peut aussi ajouter DPF pour financement.
INSERT INTO
    workflow_etape_roles (etape_id, role_id, responsibility)
SELECT
    e.id,
    r.id,
    CONCAT ('Responsable de l''étape ', e.name)
FROM
    workflow_etape e
    JOIN workflow_versions v ON e.workflow_version_id = v.id
    JOIN roles r ON r.code = 'CIP'
WHERE
    v.is_default = TRUE;

-- Pour l'étape sélection (PRCO_4), on ajoute également le rôle CAR
INSERT INTO
    workflow_etape_roles (etape_id, role_id, responsibility)
SELECT
    e.id,
    r.id,
    'Préside le comité de sélection'
FROM
    workflow_etape e
    JOIN workflow_versions v ON e.workflow_version_id = v.id
    JOIN roles r ON r.code = 'CAR'
WHERE
    v.is_default = TRUE
    AND e.code = 'PRCO_4';

-- Pour l'étape financement (PRCO_5), on ajoute DPF
INSERT INTO
    workflow_etape_roles (etape_id, role_id, responsibility)
SELECT
    e.id,
    r.id,
    'Valide les décaissements et conventions'
FROM
    workflow_etape e
    JOIN workflow_versions v ON e.workflow_version_id = v.id
    JOIN roles r ON r.code = 'DPF'
WHERE
    v.is_default = TRUE
    AND e.code = 'PRCO_5';

-- Pour l'étape suivi (PRCO_7), on ajoute DAICG
INSERT INTO
    workflow_etape_roles (etape_id, role_id, responsibility)
SELECT
    e.id,
    r.id,
    'Supervise le suivi et le contrôle'
FROM
    workflow_etape e
    JOIN workflow_versions v ON e.workflow_version_id = v.id
    JOIN roles r ON r.code = 'DAICG'
WHERE
    v.is_default = TRUE
    AND e.code = 'PRCO_7';

-- 10. (Optionnel) Ajout de sous-cycles pour certains dispositifs
-- Par exemple, pour le MPE, on peut ajouter un sous-cycle "Évaluation technique" et "Évaluation financière" sous la sélection.
-- On peut le faire si nécessaire, mais pour l'instant on laisse simplifié.
-- =====================================================================
-- FIN DU SEED
-- =====================================================================