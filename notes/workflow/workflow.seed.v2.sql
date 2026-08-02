-- =====================================================================
-- SEED : MODÈLES DE WORKFLOW PAR DÉFAUT (VERSION COMPLÉTÉE)
-- =====================================================================
-- Cette version remplace la structure générique à 7 étapes par les
-- cycles réels décrits dans les manuels de procédures :
--   - AGR_CLASSIQUE : 8 cycles / 14 sous-cycles (cf. "Procédures
--     d'exécution AGR Classiques", DAICG, 1ère édition 2023)
--   - AGR_PLUS      : 8 cycles, pas de sous-cycles (cf. "Procédures
--     d'exécution AGR Plus", DAICG, 1ère édition 2023)
-- Les autres dispositifs (MPE, MEPS, CAPITAL_INVEST, MENTORAT, PERMIS,
-- STARTUP_BOOST) n'ont pas de manuel source fourni : ils conservent la
-- structure générique à 7 étapes (PRCO_1 à PRCO_7) comme trame par
-- défaut, à affiner ultérieurement quand leurs manuels seront fournis.
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

-- 3. Insertion des rôles (si absents)
-- Rôles historiques + rôles complémentaires identifiés dans les manuels
-- AGR Classique / AGR Plus (acteurs centraux et décentralisés).
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
    ),
    (
        'DESSE',
        'Direction/Sous-Direction des Etudes Statistiques et du Suivi Evaluation',
        'Rôle central'
    ),
    ('DOP', 'Direction des Opérations', 'Rôle central'),
    (
        'DIC',
        'Direction/Division de l''Information et de la Communication',
        'Rôle central'
    ),
    (
        'ADMIN',
        'Administrateur de l''Agence Emploi Jeunes',
        'Rôle central'
    ),
    (
        'MPJIPSC',
        'Ministère de la Promotion de la Jeunesse, de l''Insertion Professionnelle et du Service Civique',
        'Tutelle'
    ),
    (
        'PRESTA',
        'Prestataire de formation',
        'Partenaire externe'
    ),
    (
        'PARTF',
        'Partenaire Financier',
        'Partenaire externe'
    ),
    (
        'ORANGEBANK',
        'Orange Bank',
        'Partenaire externe (guichet AGR Plus)'
    ),
    (
        'COMPRESEL',
        'Comité de présélection communal',
        'Instance ad hoc'
    );

-- =====================================================================
-- 4. AGR CLASSIQUE : 8 CYCLES / 14 SOUS-CYCLES
-- =====================================================================
-- 4.1 Cycles (niveau 1)
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
    NULL,
    c.code,
    c.name,
    c.description,
    1,
    c.seq,
    TRUE
FROM
    workflow_versions v
    JOIN workflows w ON w.id = v.workflow_id
    AND w.code = 'AGR_CLASSIQUE'
    AND v.is_default = TRUE
    JOIN (
        SELECT
            1 seq,
            'AGRC_C1' code,
            'Les travaux préparatoires' name,
            'Identification de la zone, de la cible, des conditions de financement et répartition des objectifs' description
        UNION ALL
        SELECT
            2,
            'AGRC_C2',
            'L''information et la sensibilisation',
            'Elaboration et mise en œuvre du plan de communication du programme'
        UNION ALL
        SELECT
            3,
            'AGRC_C3',
            'L''enrôlement',
            'Mise en place de la cellule d''enrôlement et inscription des candidats'
        UNION ALL
        SELECT
            4,
            'AGRC_C4',
            'La présélection au niveau communal',
            'Présélection des candidats par le comité communal sur la base des quotas définis'
        UNION ALL
        SELECT
            5,
            'AGRC_C5',
            'La formation des promoteurs',
            'Validation puis exécution des modules de formation des promoteurs présélectionnés'
        UNION ALL
        SELECT
            6,
            'AGRC_C6',
            'Le financement',
            'Soumission des plans d''affaires puis analyse, déblocage, décaissement et diffusion des financements'
        UNION ALL
        SELECT
            7,
            'AGRC_C7',
            'Le suivi du financement et du reporting',
            'Suivi du financement par le partenaire financier puis par l''AEJ et les structures techniques'
        UNION ALL
        SELECT
            8,
            'AGRC_C8',
            'Le remboursement du crédit et l''évaluation de l''impact',
            'Recouvrement des crédits, puis évaluation et réalisation d''études d''impact'
    ) c ON TRUE;

-- 4.2 Sous-cycles (niveau 2)
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
    parent.id,
    s.code,
    s.name,
    CONCAT (
        s.description,
        ' | Livrable(s) : ',
        s.livrables,
        CASE
            WHEN s.delais IS NOT NULL THEN CONCAT (' | Délai : ', s.delais)
            ELSE ''
        END
    ),
    2,
    s.seq,
    TRUE
FROM
    workflow_versions v
    JOIN workflows w ON w.id = v.workflow_id
    AND w.code = 'AGR_CLASSIQUE'
    AND v.is_default = TRUE
    JOIN workflow_etape parent ON parent.workflow_version_id = v.id
    AND parent.level = 1
    AND parent.code = s_parent.code
    JOIN (
        -- Cycle 1 : Travaux préparatoires
        SELECT
            'AGRC_C1' parent_code,
            1 seq,
            'AGRC_C1_1' code,
            'La définition de la zone d''intervention du secteur d''activité' name,
            'Le DPF définit, sur note d''instruction de l''Administrateur, les zones et secteurs d''activité couverts (agriculture, élevage, pêche, artisanat, commerce, BTP, TIC/économie numérique, tourisme, culture, restauration, transport, services, énergie/environnement)' description,
            'Mode Opératoire' livrables,
            NULL delais
        UNION ALL
        SELECT
            'AGRC_C1',
            2,
            'AGRC_C1_2',
            'L''identification de la cible',
            'Le DPF définit les critères d''éligibilité des jeunes/groupements (nationalité ivoirienne, inscription plateforme AEJ, 18-40 ans, projet AGR, besoin réel, expérience minimum 2 mois, entretien-diagnostic, dossier complet)',
            'Mode Opératoire',
            NULL
        UNION ALL
        SELECT
            'AGRC_C1',
            3,
            'AGRC_C1_3',
            'La définition des conditions de financement',
            'Le DPF fixe les conditions de crédit : montant 100 000 à 1 000 000 F CFA (2 500 000 F CFA pour projets collectifs), taux 8% TTC/an, durée 24 mois max, remboursement selon activité',
            'Mode Opératoire',
            NULL
        UNION ALL
        SELECT
            'AGRC_C1',
            4,
            'AGRC_C1_4',
            'La répartition des objectifs',
            'L''Administrateur valide les objectifs proposés par la DESSE et la DPF ; la DESSE répartit les objectifs par Agence Régionale sur la base des orientations du Ministère de tutelle ; la DAICG s''assure de la prise en compte des orientations',
            'Tableau de répartition, Note de Service',
            'Trois (03) semaines après la notification budgétaire'
            -- Cycle 2 : Information et sensibilisation
        UNION ALL
        SELECT
            'AGRC_C2',
            1,
            'AGRC_C2_1',
            'L''élaboration du plan de communication',
            'Le Sous-Directeur de la communication, en lien avec les Chefs d''Agence Régionale, élabore le plan de communication deux semaines avant le lancement du programme',
            'Plan de communication',
            '14 jours avant le lancement du programme'
        UNION ALL
        SELECT
            'AGRC_C2',
            2,
            'AGRC_C2_2',
            'La mise en œuvre du plan de communication',
            'La DIC exécute le plan de communication (14 jours), informe et sensibilise par courriers et rencontres les autorités administratives, coutumières et religieuses ainsi que les jeunes via les canaux appropriés',
            'Plan de communication diffusé, courriers d''information',
            '14 jours pour la mise en œuvre'
            -- Cycle 3 : Enrôlement
        UNION ALL
        SELECT
            'AGRC_C3',
            1,
            'AGRC_C3_1',
            'La mise en place de la cellule d''enrôlement',
            'Le Chef d''Agence Régionale met en place une cellule d''enrôlement (CIP, informaticiens, assistants conseillers, stagiaires) au sein de son agence et des guichets emploi',
            'Liste des membres de la cellule',
            '24 heures'
        UNION ALL
        SELECT
            'AGRC_C3',
            2,
            'AGRC_C3_2',
            'L''enrôlement',
            'Le CIP/AC aide les jeunes à s''inscrire et soumettre leur candidature sur la plateforme AEJ, constitue et archive les dossiers ; le Chef d''Agence supervise ; extraction de la base des enrôlés en fin de période',
            'Dossier de candidature du jeune, base de données des enrôlés',
            '14 jours'
            -- Cycle 4 : Présélection (pas de sous-cycle documenté -> un seul niveau 2 miroir pour homogénéiser le moteur de workflow)
        UNION ALL
        SELECT
            'AGRC_C4',
            1,
            'AGRC_C4_1',
            'La présélection au niveau communal',
            'Le comité de présélection communal (Maire/Président du Conseil Régional, MPJIPSC, Agence Régionale/Guichet Emploi, partenaires financiers, Plateforme de Service, Conseil National de la Jeunesse) présélectionne les jeunes selon les quotas définis ; PV rédigé et validé, puis diffusion après autorisation',
            'PV de présélection, liste des présélectionnés, base de données des présélectionnés',
            '09 jours'
            -- Cycle 5 : Formation des promoteurs
        UNION ALL
        SELECT
            'AGRC_C5',
            1,
            'AGRC_C5_1',
            'La validation des modules de formation',
            'Le prestataire propose une matrice de modules de formation ; le DPF la valide et communique le planning de formation aux Agences Régionales',
            'Matrice des modules de formation validée, planning de formation',
            '72 h'
        UNION ALL
        SELECT
            'AGRC_C5',
            2,
            'AGRC_C5_2',
            'L''exécution des modules de formation',
            'Le prestataire forme les jeunes présélectionnés au montage de plan d''affaires, à l''éducation financière, à la culture entrepreneuriale, etc. ; transmission hebdomadaire des listes de présence et des jeunes formés ; visites inopinées du CIP/AC',
            'Plan d''affaires, liste de présence des jeunes formés, base de données des jeunes formés',
            'Un mois'
            -- Cycle 6 : Financement
        UNION ALL
        SELECT
            'AGRC_C6',
            1,
            'AGRC_C6_1',
            'La soumission des plans d''affaires',
            'Les jeunes déposent leur plan d''affaires auprès du formateur, qui le transmet aux agences du partenaire financier concerné, complété des pièces requises (individuel ou collectif)',
            'Plan d''affaires, complément des dossiers physiques',
            '24h'
        UNION ALL
        SELECT
            'AGRC_C6',
            2,
            'AGRC_C6_2',
            'L''analyse des demandes de crédit, le déblocage, le décaissement et la diffusion',
            'Les agences locales du partenaire financier analysent la viabilité des projets, effectuent des visites d''exploitation, débloquent les prêts et transmettent la liste des jeunes financés au DPF/DOP/Chef d''Agence puis à la DIC pour publication',
            'Rapport de visite, liste de présence aux rencontres, liste des jeunes financés',
            'Pendant toute la durée de financement'
            -- Cycle 7 : Suivi du financement et reporting
        UNION ALL
        SELECT
            'AGRC_C7',
            1,
            'AGRC_C7_1',
            'Le suivi du financement par le partenaire financier',
            'Le partenaire financier effectue des visites terrain mensuelles et produit une fiche de résultats mensuelle par région (financements accordés, encours, situation des crédits) et des rapports mensuels d''activité',
            'Rapport de visite, fiche de résultat',
            'Mensuel'
        UNION ALL
        SELECT
            'AGRC_C7',
            2,
            'AGRC_C7_2',
            'Le suivi du financement par l''AEJ et les structures techniques',
            'Les CIP/AC effectuent des visites terrain mensuelles pendant l''installation et l''exploitation, rédigent un rapport de visite validé par le Chef d''Agence et transmis à la DPF ; la DESSE, la DOP et la DPF supervisent',
            'Rapport de visite',
            'Mensuel'
            -- Cycle 8 : Remboursement et évaluation de l'impact
        UNION ALL
        SELECT
            'AGRC_C8',
            1,
            'AGRC_C8_1',
            'Le remboursement du crédit',
            'Les agences du partenaire financier renseignent la base des remboursements, partagée au DPF/DOP/DESSE/DAICG et au CIP ; le CIP met à jour la plateforme et sensibilise mensuellement les bénéficiaires',
            'Base de données actualisées des jeunes ayant remboursé',
            'Hebdomadaire (remontée BDD), mensuel (relances)'
        UNION ALL
        SELECT
            'AGRC_C8',
            2,
            'AGRC_C8_2',
            'L''évaluation et la réalisation d''études d''impact',
            'La DESSE propose un cadre de résultats et un plan de suivi des indicateurs, le partage aux intervenants et réalise une évaluation à mi-parcours ; des missions d''audit/contrôle/suivi-évaluation peuvent être mandatées',
            'Rapport d''évaluation, rapport de mission',
            'Selon les termes de référence'
    ) s ON TRUE
    JOIN (
        SELECT
            'AGRC_C1' code
        UNION ALL
        SELECT
            'AGRC_C2'
        UNION ALL
        SELECT
            'AGRC_C3'
        UNION ALL
        SELECT
            'AGRC_C4'
        UNION ALL
        SELECT
            'AGRC_C5'
        UNION ALL
        SELECT
            'AGRC_C6'
        UNION ALL
        SELECT
            'AGRC_C7'
        UNION ALL
        SELECT
            'AGRC_C8'
    ) s_parent ON s_parent.code = s.parent_code;

-- 4.3 Transitions macro (niveau 1, chaînage des 8 cycles)
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
    e1.id,
    e2.id,
    'default',
    e1.sequence_order,
    TRUE
FROM
    workflow_versions v
    JOIN workflows w ON w.id = v.workflow_id
    AND w.code = 'AGR_CLASSIQUE'
    AND v.is_default = TRUE
    JOIN workflow_etape e1 ON e1.workflow_version_id = v.id
    AND e1.level = 1
    JOIN workflow_etape e2 ON e2.workflow_version_id = v.id
    AND e2.level = 1
    AND e2.sequence_order = e1.sequence_order + 1;

-- 4.4 Transitions détaillées (niveau 2, chaînage réel des sous-cycles dans l'ordre du manuel)
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
    e_from.id,
    e_to.id,
    'default',
    t.ord,
    TRUE
FROM
    workflow_versions v
    JOIN workflows w ON w.id = v.workflow_id
    AND w.code = 'AGR_CLASSIQUE'
    AND v.is_default = TRUE
    JOIN (
        SELECT
            1 ord,
            'AGRC_C1_1' from_code,
            'AGRC_C1_2' to_code
        UNION ALL
        SELECT
            2,
            'AGRC_C1_2',
            'AGRC_C1_3'
        UNION ALL
        SELECT
            3,
            'AGRC_C1_3',
            'AGRC_C1_4'
        UNION ALL
        SELECT
            4,
            'AGRC_C1_4',
            'AGRC_C2_1'
        UNION ALL
        SELECT
            5,
            'AGRC_C2_1',
            'AGRC_C2_2'
        UNION ALL
        SELECT
            6,
            'AGRC_C2_2',
            'AGRC_C3_1'
        UNION ALL
        SELECT
            7,
            'AGRC_C3_1',
            'AGRC_C3_2'
        UNION ALL
        SELECT
            8,
            'AGRC_C3_2',
            'AGRC_C4_1'
        UNION ALL
        SELECT
            9,
            'AGRC_C4_1',
            'AGRC_C5_1' -- transition par défaut ; voir décision ci-dessous
        UNION ALL
        SELECT
            10,
            'AGRC_C5_1',
            'AGRC_C5_2'
        UNION ALL
        SELECT
            11,
            'AGRC_C5_2',
            'AGRC_C6_1'
        UNION ALL
        SELECT
            12,
            'AGRC_C6_1',
            'AGRC_C6_2'
        UNION ALL
        SELECT
            13,
            'AGRC_C6_2',
            'AGRC_C7_1'
        UNION ALL
        SELECT
            14,
            'AGRC_C7_1',
            'AGRC_C7_2'
        UNION ALL
        SELECT
            15,
            'AGRC_C7_2',
            'AGRC_C8_1'
        UNION ALL
        SELECT
            16,
            'AGRC_C8_1',
            'AGRC_C8_2'
    ) t ON TRUE
    JOIN workflow_etape e_from ON e_from.workflow_version_id = v.id
    AND e_from.code = t.from_code
    JOIN workflow_etape e_to ON e_to.workflow_version_id = v.id
    AND e_to.code = t.to_code;

-- 4.5 Point de décision sur la présélection (AGRC_C4_1)
INSERT INTO
    decision_point (etape_id, name, description)
SELECT
    e.id,
    'Décision du comité de présélection communal',
    'Issue de la présélection des candidatures par le comité communal'
FROM
    workflow_etape e
    JOIN workflow_versions v ON e.workflow_version_id = v.id
    JOIN workflows w ON w.id = v.workflow_id
    AND w.code = 'AGR_CLASSIQUE'
    AND v.is_default = TRUE
WHERE
    e.code = 'AGRC_C4_1';

INSERT INTO
    decision_outcome (decision_point_id, code, label, next_etape_id)
SELECT
    dp.id,
    'PRESELECTIONNE',
    'Candidat présélectionné',
    (
        SELECT
            e2.id
        FROM
            workflow_etape e2
        WHERE
            e2.code = 'AGRC_C5_1'
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
    decision_point dp
    JOIN workflow_etape e ON e.id = dp.etape_id
    AND e.code = 'AGRC_C4_1';

INSERT INTO
    decision_outcome (decision_point_id, code, label, next_etape_id)
SELECT
    dp.id,
    'NON_PRESELECTIONNE',
    'Candidat non présélectionné',
    NULL
FROM
    decision_point dp
    JOIN workflow_etape e ON e.id = dp.etape_id
    AND e.code = 'AGRC_C4_1';

INSERT INTO
    decision_outcome (decision_point_id, code, label, next_etape_id)
SELECT
    dp.id,
    'LISTE_ATTENTE',
    'Candidat en liste d''attente',
    dp.etape_id
FROM
    decision_point dp
    JOIN workflow_etape e ON e.id = dp.etape_id
    AND e.code = 'AGRC_C4_1';

-- 4.6 Livrables (deliverable_template) pour chaque sous-cycle AGR Classique
INSERT INTO
    deliverable_template (etape_id, name, description, is_mandatory)
SELECT
    e.id,
    d.deliverable_name,
    CONCAT ('Livrable produit à l''issue de : ', e.name),
    TRUE
FROM
    workflow_etape e
    JOIN workflow_versions v ON e.workflow_version_id = v.id
    JOIN workflows w ON w.id = v.workflow_id
    AND w.code = 'AGR_CLASSIQUE'
    AND v.is_default = TRUE
    JOIN (
        SELECT
            'AGRC_C1_1' code,
            'Mode Opératoire' deliverable_name
        UNION ALL
        SELECT
            'AGRC_C1_2',
            'Mode Opératoire'
        UNION ALL
        SELECT
            'AGRC_C1_3',
            'Mode Opératoire'
        UNION ALL
        SELECT
            'AGRC_C1_4',
            'Tableau de répartition des objectifs'
        UNION ALL
        SELECT
            'AGRC_C1_4',
            'Note de Service'
        UNION ALL
        SELECT
            'AGRC_C2_1',
            'Plan de communication'
        UNION ALL
        SELECT
            'AGRC_C2_2',
            'Plan de communication diffusé'
        UNION ALL
        SELECT
            'AGRC_C2_2',
            'Courriers d''information'
        UNION ALL
        SELECT
            'AGRC_C3_1',
            'Liste des membres de la cellule d''enrôlement'
        UNION ALL
        SELECT
            'AGRC_C3_2',
            'Dossier de candidature du jeune'
        UNION ALL
        SELECT
            'AGRC_C3_2',
            'Base de données des enrôlés'
        UNION ALL
        SELECT
            'AGRC_C4_1',
            'PV de présélection'
        UNION ALL
        SELECT
            'AGRC_C4_1',
            'Liste des jeunes présélectionnés'
        UNION ALL
        SELECT
            'AGRC_C5_1',
            'Matrice des modules de formation validée'
        UNION ALL
        SELECT
            'AGRC_C5_1',
            'Planning de formation'
        UNION ALL
        SELECT
            'AGRC_C5_2',
            'Liste de présence des jeunes formés'
        UNION ALL
        SELECT
            'AGRC_C5_2',
            'Base de données des jeunes formés'
        UNION ALL
        SELECT
            'AGRC_C6_1',
            'Plan d''affaires'
        UNION ALL
        SELECT
            'AGRC_C6_2',
            'Rapport de visite d''exploitation'
        UNION ALL
        SELECT
            'AGRC_C6_2',
            'Liste des jeunes financés'
        UNION ALL
        SELECT
            'AGRC_C7_1',
            'Fiche de résultat mensuelle'
        UNION ALL
        SELECT
            'AGRC_C7_2',
            'Rapport de visite de suivi'
        UNION ALL
        SELECT
            'AGRC_C8_1',
            'Base de données des jeunes ayant remboursé'
        UNION ALL
        SELECT
            'AGRC_C8_2',
            'Rapport d''évaluation'
        UNION ALL
        SELECT
            'AGRC_C8_2',
            'Rapport de mission'
    ) d ON d.code = e.code;

-- 4.7 Rattachement des rôles/acteurs aux étapes AGR Classique
INSERT INTO
    workflow_etape_roles (etape_id, role_id, responsibility)
SELECT
    e.id,
    r.id,
    CONCAT ('Acteur de l''étape ', e.name)
FROM
    workflow_etape e
    JOIN workflow_versions v ON e.workflow_version_id = v.id
    JOIN workflows w ON w.id = v.workflow_id
    AND w.code = 'AGR_CLASSIQUE'
    AND v.is_default = TRUE
    JOIN (
        SELECT
            'AGRC_C1_1' code,
            'DPF' role_code
        UNION ALL
        SELECT
            'AGRC_C1_2',
            'DPF'
        UNION ALL
        SELECT
            'AGRC_C1_3',
            'DPF'
        UNION ALL
        SELECT
            'AGRC_C1_4',
            'DESSE'
        UNION ALL
        SELECT
            'AGRC_C1_4',
            'DPF'
        UNION ALL
        SELECT
            'AGRC_C1_4',
            'DAICG'
        UNION ALL
        SELECT
            'AGRC_C1_4',
            'ADMIN'
        UNION ALL
        SELECT
            'AGRC_C2_1',
            'MPJIPSC'
        UNION ALL
        SELECT
            'AGRC_C2_1',
            'PARTF'
        UNION ALL
        SELECT
            'AGRC_C2_2',
            'MPJIPSC'
        UNION ALL
        SELECT
            'AGRC_C2_2',
            'DIC'
        UNION ALL
        SELECT
            'AGRC_C2_2',
            'PARTF'
        UNION ALL
        SELECT
            'AGRC_C3_1',
            'CAR'
        UNION ALL
        SELECT
            'AGRC_C3_2',
            'CIP'
        UNION ALL
        SELECT
            'AGRC_C3_2',
            'AC'
        UNION ALL
        SELECT
            'AGRC_C3_2',
            'CAR'
        UNION ALL
        SELECT
            'AGRC_C4_1',
            'COMPRESEL'
        UNION ALL
        SELECT
            'AGRC_C4_1',
            'CAR'
        UNION ALL
        SELECT
            'AGRC_C4_1',
            'DPF'
        UNION ALL
        SELECT
            'AGRC_C5_1',
            'PRESTA'
        UNION ALL
        SELECT
            'AGRC_C5_1',
            'DPF'
        UNION ALL
        SELECT
            'AGRC_C5_2',
            'PRESTA'
        UNION ALL
        SELECT
            'AGRC_C5_2',
            'CIP'
        UNION ALL
        SELECT
            'AGRC_C5_2',
            'AC'
        UNION ALL
        SELECT
            'AGRC_C5_2',
            'CAR'
        UNION ALL
        SELECT
            'AGRC_C6_2',
            'PARTF'
        UNION ALL
        SELECT
            'AGRC_C7_1',
            'PARTF'
        UNION ALL
        SELECT
            'AGRC_C7_2',
            'CAR'
        UNION ALL
        SELECT
            'AGRC_C7_2',
            'CIP'
        UNION ALL
        SELECT
            'AGRC_C7_2',
            'DESSE'
        UNION ALL
        SELECT
            'AGRC_C7_2',
            'DPF'
        UNION ALL
        SELECT
            'AGRC_C8_1',
            'PARTF'
        UNION ALL
        SELECT
            'AGRC_C8_1',
            'CAR'
        UNION ALL
        SELECT
            'AGRC_C8_1',
            'CIP'
        UNION ALL
        SELECT
            'AGRC_C8_2',
            'DESSE'
        UNION ALL
        SELECT
            'AGRC_C8_2',
            'DAICG'
    ) ra ON ra.code = e.code
    JOIN roles r ON r.code = ra.role_code;

-- =====================================================================
-- 5. AGR PLUS : 8 CYCLES (SANS SOUS-CYCLE)
-- =====================================================================
-- 5.1 Cycles (niveau 1)
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
    NULL,
    c.code,
    c.name,
    CONCAT (
        c.description,
        ' | Livrable(s) : ',
        c.livrables,
        CASE
            WHEN c.delais IS NOT NULL THEN CONCAT (' | Délai : ', c.delais)
            ELSE ''
        END
    ),
    1,
    c.seq,
    TRUE
FROM
    workflow_versions v
    JOIN workflows w ON w.id = v.workflow_id
    AND w.code = 'AGR_PLUS'
    AND v.is_default = TRUE
    JOIN (
        SELECT
            1 seq,
            'AGRP_C1' code,
            'La répartition des objectifs' name,
            'L''Administrateur valide les objectifs proposés par la DESSE et la DPF ; la DESSE répartit les objectifs par Agence Régionale ; la DAICG s''assure de la prise en compte des orientations du Ministère de tutelle' description,
            'Note de Service' livrables,
            'Trois (03) semaines après la notification budgétaire' delais
        UNION ALL
        SELECT
            2,
            'AGRP_C2',
            'Le profilage des bénéficiaires',
            'La DPF traite les bases de remboursement des prêts AGR Classique (18-40 ans, prêt totalement remboursé) et les transmet à Orange Bank, qui profile les jeunes ayant un compte TIK TAK sans impayé',
            'Base de données AGR classique, base de données des bénéficiaires éligibles',
            'Six (6) jours'
        UNION ALL
        SELECT
            3,
            'AGRP_C3',
            'La soumission des candidatures',
            'Le CIP/AC invite les jeunes éligibles à vérifier leur statut et à soumettre leur demande de financement sur la plateforme, avec vérification d''identité par OTP',
            'Base de données des demandeurs',
            'Quatorze (14) jours pour la soumission'
        UNION ALL
        SELECT
            4,
            'AGRP_C4',
            'Le traitement des candidatures et la demande de financement',
            'Le Chef d''Agence/CIP effectue une visite de l''activité du demandeur, produit un rapport de visite ; la DPF extrait et sélectionne les bénéficiaires ayant un avis favorable pour transmission à Orange Bank',
            'Rapport de visite, base de données des bénéficiaires sélectionnés',
            'Cinq (5) jours'
        UNION ALL
        SELECT
            5,
            'AGRP_C5',
            'Le déblocage du financement',
            'Orange Bank invite les bénéficiaires à souscrire à l''offre Tik Tak+ ; après souscription, le financement est débloqué sur le compte mobile money ; la DPF diffuse la base des jeunes financés',
            'Base de données des bénéficiaires financés',
            'Après souscription à l''offre Tik Tak+'
        UNION ALL
        SELECT
            6,
            'AGRP_C6',
            'Le suivi des financements',
            'Les CIP/Chef d''Agence effectuent des visites terrain trimestrielles, rédigent des rapports de visite ; Orange Bank produit des rapports mensuels d''activité (financements, encours, impayés)',
            'Rapport de visite, rapports mensuels d''activité',
            'Mensuel'
        UNION ALL
        SELECT
            7,
            'AGRP_C7',
            'Le remboursement du crédit',
            'Les promoteurs remboursent via mobile money ; Orange Bank met à jour la base de remboursement ; le CIP effectue un phoning mensuel de sensibilisation et rédige un rapport de suivi',
            'Situation des remboursements, rapport de suivi des remboursements',
            'Deux (2) jours après le phoning pour le rapport ; mensuel pour le phoning'
        UNION ALL
        SELECT
            8,
            'AGRP_C8',
            'L''évaluation',
            'La DESSE propose un cadre de résultats et un plan de suivi, réalise une évaluation à mi-parcours à la fin des premiers remboursements ; des missions d''audit/contrôle peuvent être mandatées par la DAICG',
            'Rapport de suivi-évaluation, rapport d''audit et contrôle, termes de référence',
            'A la fin des premiers remboursements (missions internes) ; 14 jours (missions externes)'
    ) c ON TRUE;

-- 5.2 Transitions (chaînage séquentiel des 8 cycles)
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
    e1.id,
    e2.id,
    'default',
    e1.sequence_order,
    TRUE
FROM
    workflow_versions v
    JOIN workflows w ON w.id = v.workflow_id
    AND w.code = 'AGR_PLUS'
    AND v.is_default = TRUE
    JOIN workflow_etape e1 ON e1.workflow_version_id = v.id
    AND e1.level = 1
    JOIN workflow_etape e2 ON e2.workflow_version_id = v.id
    AND e2.level = 1
    AND e2.sequence_order = e1.sequence_order + 1;

-- 5.3 Point de décision sur le déblocage (souscription à l'offre Tik Tak+)
INSERT INTO
    decision_point (etape_id, name, description)
SELECT
    e.id,
    'Décision de souscription à l''offre Tik Tak+',
    'Le déblocage du financement est conditionné à la souscription du bénéficiaire à l''offre Tik Tak+ d''Orange Bank'
FROM
    workflow_etape e
    JOIN workflow_versions v ON e.workflow_version_id = v.id
    JOIN workflows w ON w.id = v.workflow_id
    AND w.code = 'AGR_PLUS'
    AND v.is_default = TRUE
WHERE
    e.code = 'AGRP_C5';

INSERT INTO
    decision_outcome (decision_point_id, code, label, next_etape_id)
SELECT
    dp.id,
    'SOUSCRIT',
    'Souscription à l''offre Tik Tak+ effectuée',
    (
        SELECT
            e2.id
        FROM
            workflow_etape e2
        WHERE
            e2.code = 'AGRP_C6'
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
    decision_point dp
    JOIN workflow_etape e ON e.id = dp.etape_id
    AND e.code = 'AGRP_C5';

INSERT INTO
    decision_outcome (decision_point_id, code, label, next_etape_id)
SELECT
    dp.id,
    'NON_SOUSCRIT',
    'Souscription non effectuée : procédure suspendue',
    NULL
FROM
    decision_point dp
    JOIN workflow_etape e ON e.id = dp.etape_id
    AND e.code = 'AGRP_C5';

-- 5.4 Livrables (deliverable_template) pour chaque cycle AGR Plus
INSERT INTO
    deliverable_template (etape_id, name, description, is_mandatory)
SELECT
    e.id,
    d.deliverable_name,
    CONCAT ('Livrable produit à l''issue de : ', e.name),
    TRUE
FROM
    workflow_etape e
    JOIN workflow_versions v ON e.workflow_version_id = v.id
    JOIN workflows w ON w.id = v.workflow_id
    AND w.code = 'AGR_PLUS'
    AND v.is_default = TRUE
    JOIN (
        SELECT
            'AGRP_C1' code,
            'Note de Service' deliverable_name
        UNION ALL
        SELECT
            'AGRP_C2',
            'Base de données AGR classique'
        UNION ALL
        SELECT
            'AGRP_C2',
            'Base de données des bénéficiaires éligibles'
        UNION ALL
        SELECT
            'AGRP_C3',
            'Base de données des demandeurs'
        UNION ALL
        SELECT
            'AGRP_C4',
            'Rapport de visite'
        UNION ALL
        SELECT
            'AGRP_C4',
            'Base de données des bénéficiaires sélectionnés'
        UNION ALL
        SELECT
            'AGRP_C5',
            'Base de données des bénéficiaires financés'
        UNION ALL
        SELECT
            'AGRP_C6',
            'Rapport de visite'
        UNION ALL
        SELECT
            'AGRP_C6',
            'Rapports mensuels d''activité'
        UNION ALL
        SELECT
            'AGRP_C7',
            'Situation des remboursements'
        UNION ALL
        SELECT
            'AGRP_C7',
            'Rapport de suivi des remboursements'
        UNION ALL
        SELECT
            'AGRP_C8',
            'Rapport de suivi-évaluation'
        UNION ALL
        SELECT
            'AGRP_C8',
            'Rapport d''audit et contrôle'
    ) d ON d.code = e.code;

-- 5.5 Rattachement des rôles/acteurs aux étapes AGR Plus
INSERT INTO
    workflow_etape_roles (etape_id, role_id, responsibility)
SELECT
    e.id,
    r.id,
    CONCAT ('Acteur de l''étape ', e.name)
FROM
    workflow_etape e
    JOIN workflow_versions v ON e.workflow_version_id = v.id
    JOIN workflows w ON w.id = v.workflow_id
    AND w.code = 'AGR_PLUS'
    AND v.is_default = TRUE
    JOIN (
        SELECT
            'AGRP_C1' code,
            'DESSE' role_code
        UNION ALL
        SELECT
            'AGRP_C1',
            'DPF'
        UNION ALL
        SELECT
            'AGRP_C1',
            'DAICG'
        UNION ALL
        SELECT
            'AGRP_C1',
            'ADMIN'
        UNION ALL
        SELECT
            'AGRP_C2',
            'DPF'
        UNION ALL
        SELECT
            'AGRP_C2',
            'ORANGEBANK'
        UNION ALL
        SELECT
            'AGRP_C2',
            'DIC'
        UNION ALL
        SELECT
            'AGRP_C3',
            'CIP'
        UNION ALL
        SELECT
            'AGRP_C3',
            'AC'
        UNION ALL
        SELECT
            'AGRP_C4',
            'CAR'
        UNION ALL
        SELECT
            'AGRP_C4',
            'CIP'
        UNION ALL
        SELECT
            'AGRP_C4',
            'DPF'
        UNION ALL
        SELECT
            'AGRP_C5',
            'DPF'
        UNION ALL
        SELECT
            'AGRP_C5',
            'ORANGEBANK'
        UNION ALL
        SELECT
            'AGRP_C6',
            'CIP'
        UNION ALL
        SELECT
            'AGRP_C6',
            'CAR'
        UNION ALL
        SELECT
            'AGRP_C6',
            'ORANGEBANK'
        UNION ALL
        SELECT
            'AGRP_C7',
            'CIP'
        UNION ALL
        SELECT
            'AGRP_C7',
            'CAR'
        UNION ALL
        SELECT
            'AGRP_C7',
            'ORANGEBANK'
        UNION ALL
        SELECT
            'AGRP_C7',
            'DPF'
        UNION ALL
        SELECT
            'AGRP_C8',
            'DAICG'
        UNION ALL
        SELECT
            'AGRP_C8',
            'DESSE'
    ) ra ON ra.code = e.code
    JOIN roles r ON r.code = ra.role_code;

-- =====================================================================
-- 6. AUTRES DISPOSITIFS (MPE, MEPS, CAPITAL_INVEST, MENTORAT, PERMIS,
--    STARTUP_BOOST) : TRAME GÉNÉRIQUE PAR DÉFAUT (7 ÉTAPES)
--    -> à remplacer dès que leurs manuels de procédures seront fournis.
-- =====================================================================
-- 6.1 Cycles génériques (niveau 1)
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
    NULL,
    CONCAT ('PRCO_', seq.num),
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
    1,
    seq.num,
    TRUE
FROM
    workflow_versions v
    JOIN workflows w ON w.id = v.workflow_id
    AND w.code NOT IN ('AGR_CLASSIQUE', 'AGR_PLUS')
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
    v.is_default = TRUE
ORDER BY
    v.id,
    seq.num;

-- 6.2 Transitions séquentielles génériques
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
    from_etape.sequence_order,
    TRUE
FROM
    workflow_versions v
    JOIN workflows w ON w.id = v.workflow_id
    AND w.code NOT IN ('AGR_CLASSIQUE', 'AGR_PLUS')
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

-- 6.3 Point de décision générique sur l'étape "Sélection" (PRCO_4)
INSERT INTO
    decision_point (etape_id, name, description)
SELECT
    e.id,
    'Décision du comité de sélection',
    'Issue de l''évaluation du projet par le comité'
FROM
    workflow_etape e
    JOIN workflow_versions v ON e.workflow_version_id = v.id
    JOIN workflows w ON w.id = v.workflow_id
    AND w.code NOT IN ('AGR_CLASSIQUE', 'AGR_PLUS')
WHERE
    v.is_default = TRUE
    AND e.code = 'PRCO_4';

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
        WHERE
            e2.code = 'PRCO_5'
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
    decision_point dp
    JOIN workflow_etape e ON e.id = dp.etape_id
    AND e.code = 'PRCO_4';

INSERT INTO
    decision_outcome (decision_point_id, code, label, next_etape_id)
SELECT
    dp.id,
    'REJETE',
    'Projet rejeté',
    NULL
FROM
    decision_point dp
    JOIN workflow_etape e ON e.id = dp.etape_id
    AND e.code = 'PRCO_4';

INSERT INTO
    decision_outcome (decision_point_id, code, label, next_etape_id)
SELECT
    dp.id,
    'ATTENTE',
    'Projet en liste d''attente',
    dp.etape_id
FROM
    decision_point dp
    JOIN workflow_etape e ON e.id = dp.etape_id
    AND e.code = 'PRCO_4';

-- 6.4 Livrables génériques par étape
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
    JOIN workflows w ON w.id = v.workflow_id
    AND w.code NOT IN ('AGR_CLASSIQUE', 'AGR_PLUS')
WHERE
    v.is_default = TRUE;

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
    JOIN workflows w ON w.id = v.workflow_id
    AND w.code NOT IN ('AGR_CLASSIQUE', 'AGR_PLUS')
WHERE
    v.is_default = TRUE
    AND e.code = 'PRCO_4';

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
    JOIN workflows w ON w.id = v.workflow_id
    AND w.code NOT IN ('AGR_CLASSIQUE', 'AGR_PLUS')
WHERE
    v.is_default = TRUE
    AND e.code = 'PRCO_5';

-- 6.5 Rôles génériques par étape
INSERT INTO
    workflow_etape_roles (etape_id, role_id, responsibility)
SELECT
    e.id,
    r.id,
    CONCAT ('Responsable de l''étape ', e.name)
FROM
    workflow_etape e
    JOIN workflow_versions v ON e.workflow_version_id = v.id
    JOIN workflows w ON w.id = v.workflow_id
    AND w.code NOT IN ('AGR_CLASSIQUE', 'AGR_PLUS')
    JOIN roles r ON r.code = 'CIP'
WHERE
    v.is_default = TRUE;

INSERT INTO
    workflow_etape_roles (etape_id, role_id, responsibility)
SELECT
    e.id,
    r.id,
    'Préside le comité de sélection'
FROM
    workflow_etape e
    JOIN workflow_versions v ON e.workflow_version_id = v.id
    JOIN workflows w ON w.id = v.workflow_id
    AND w.code NOT IN ('AGR_CLASSIQUE', 'AGR_PLUS')
    JOIN roles r ON r.code = 'CAR'
WHERE
    v.is_default = TRUE
    AND e.code = 'PRCO_4';

INSERT INTO
    workflow_etape_roles (etape_id, role_id, responsibility)
SELECT
    e.id,
    r.id,
    'Valide les décaissements et conventions'
FROM
    workflow_etape e
    JOIN workflow_versions v ON e.workflow_version_id = v.id
    JOIN workflows w ON w.id = v.workflow_id
    AND w.code NOT IN ('AGR_CLASSIQUE', 'AGR_PLUS')
    JOIN roles r ON r.code = 'DPF'
WHERE
    v.is_default = TRUE
    AND e.code = 'PRCO_5';

INSERT INTO
    workflow_etape_roles (etape_id, role_id, responsibility)
SELECT
    e.id,
    r.id,
    'Supervise le suivi et le contrôle'
FROM
    workflow_etape e
    JOIN workflow_versions v ON e.workflow_version_id = v.id
    JOIN workflows w ON w.id = v.workflow_id
    AND w.code NOT IN ('AGR_CLASSIQUE', 'AGR_PLUS')
    JOIN roles r ON r.code = 'DAICG'
WHERE
    v.is_default = TRUE
    AND e.code = 'PRCO_7';

-- =====================================================================
-- FIN DU SEED
-- =====================================================================