<?php

namespace App\Constants;

class Workflow
{
    const WorkflowModels = [
        'AGR_CLASSIQUE' => [
            'code' => "AGR_CLASSIQUE",
            'name' => "PROCÉDURE AGR CLASSIQUES",
            'description' => "Procédure pour les Activités Génératrices de Revenus classiques",
            'is_active' => TRUE
        ],
        'AGR_PLUS' => [
            'code' => "AGR_PLUS",
            'name' => "PROCÉDURE AGR PLUS",
            'description' => "Procédure pour les Activités Génératrices de Revenus renforcées",
            'is_active' => TRUE
        ],
        'MPE' => [
            'code' => "MPE",
            'name' => "PROCÉDURE D'EXÉCUTION DES MICRO ET PETITES ENTREPRISES",
            'description' => "Procédure MPE",
            'is_active' => TRUE
        ],
        'MEPS' => [
            'code' => "MEPS",
            'name' => "PROCÉDURE D'EXÉCUTION DES MOYENNES ENTREPRISES ET PROJETS STRUCTURANTS",
            'description' => "Procédure MEPS",
            'is_active' => TRUE
        ],
        'CAPITAL_INVEST' => [
            'code' => "CAPITAL_INVEST",
            'name' => "PROCÉDURES D'EXECUTION DU CAPITAL INVESTISSEMENT",
            'description' => "Procédure Capital Investissement",
            'is_active' => TRUE
        ],
        'MENTORAT' => [
            'code' => "MENTORAT",
            'name' => "MENTORAT",
            'description' => "Procédure Mentorat",
            'is_active' => TRUE
        ],
        'PERMIS' => [
            'code' => "PERMIS",
            'name' => "PERMIS",
            'description' => "Procédure Permis",
            'is_active' => TRUE
        ],
        'STARTUP_BOOST' => [
            'code' => "STARTUP_BOOST",
            'name' => "STARTUP_BOOST",
            'description' => "Procédure Startup Boost",
            'is_active' => TRUE
        ],
    ];

    const WorkflowVersion = [
        'AGR_CLASSIQUE' => [
            'name' => "AGR classique v2026",
            'description' => "AGR classique version 2026",
            'version' => "2026",
            'is_active' => TRUE,
            'is_default' => TRUE,
            'workflow_code' => "AGR_CLASSIQUE"
        ],
        'AGR_PLUS' => [
            'name' => "AGR plus v2026",
            'description' => "AGR plus version 2026",
            'version' => "2026",
            'is_active' => TRUE,
            'is_default' => FALSE,
            'workflow_code' => "AGR_PLUS"
        ],
        'MPE' => [
            'name' => "MPE v2026",
            'description' => "MPE version 2026",
            'version' => "2026",
            'is_active' => TRUE,
            'is_default' => FALSE,
            'workflow_code' => "MPE"
        ],
        'MEPS' => [
            'name' => "MEPS v2026",
            'description' => "MEPS version 2026",
            'version' => "2026",
            'is_active' => TRUE,
            'is_default' => FALSE,
            'workflow_code' => "MEPS"
        ],
        'CAPITAL_INVEST' => [
            'name' => "CAPITAL_INVEST v2026",
            'description' => "CAPITAL_INVEST version 2026",
            'version' => "2026",
            'is_active' => TRUE,
            'is_default' => FALSE,
            'workflow_code' => "CAPITAL_INVEST"
        ],
        'MENTORAT' => [
            'name' => "MENTORAT v2026",
            'description' => "MENTORAT version 2026",
            'version' => "2026",
            'is_active' => TRUE,
            'is_default' => FALSE,
            'workflow_code' => "MENTORAT"
        ],
        'PERMIS' => [
            'name' => "PERMIS v2026",
            'description' => "PERMIS version 2026",
            'version' => "2026",
            'is_active' => TRUE,
            'is_default' => FALSE,
            'workflow_code' => "PERMIS"
        ],
        'STARTUP_BOOST' => [
            'name' => "STARTUP_BOOST v2026",
            'description' => "STARTUP_BOOST version 2026",
            'version' => "2026",
            'is_active' => TRUE,
            'is_default' => FALSE,
            'workflow_code' => "STARTUP_BOOST"
        ]
    ];

    const WorkflowEtapes = [
        'AGR_CLASSIQUE' => [
            [
                'code' => "AGRC_PRCO_6",
                'name' => "FINANCEMENT",
                'description' => "Le financement",
                'impact' => '',
                'version' => "2026",
                'is_active' => true,
                'sequence_order' => 6,
                'parent_etape_code' => "",
                'children' => [
                    [
                        'code' => "AGRC_PRCO_6_1",
                        'name' => "Soumission des plans d’affaires",
                        'description' => "La soumission des plans d’affaires",
                        'impact' => 'EN_FINANCEMENT',
                        'version' => "2026",
                        'is_active' => true,
                        'sequence_order' => 1,
                        'parent_etape_code' => "AGRC_PRCO_6",
                    ],
                    [
                        'code' => "AGRC_PRCO_6_2",
                        'name' => "Analyse des demandes de crédit, déblocage, décaissement, et diffusion",
                        'description' => "L'analyse des demandes de crédit, déblocage, décaissement, et diffusion",
                        'impact' => 'EN_DECAISSEMENT',
                        'version' => "2026",
                        'is_active' => true,
                        'sequence_order' => 2,
                        'parent_etape_code' => "AGRC_PRCO_6",
                    ]
                ]
            ],
            [
                'code' => "AGRC_PRCO_7",
                'name' => "SUIVI DU FINANCEMENT ET DU REPORTING",
                'description' => "Le suivi et le reporting",
                'impact' => '',
                'version' => "2026",
                'is_active' => true,
                'sequence_order' => 7,
                'parent_etape_code' => "",
                'children' => [
                    [
                        'code' => "AGRC_PRCO_7_1",
                        'name' => "Suivi du financement par le partenaire financier",
                        'description' => "Le suivi du financement par le partenaire financier",
                        'impact' => 'EN_SUIVI',
                        'version' => "2026",
                        'is_active' => true,
                        'sequence_order' => 1,
                        'parent_etape_code' => "AGRC_PRCO_7",
                    ],
                    [
                        'code' => "AGRC_PRCO_7_2",
                        'name' => "Suivi du financement par L’AEJ et les structures techniques",
                        'description' => "Le suivi du financement par L’AEJ et les structures techniques",
                        'impact' => 'EN_SUIVI',
                        'version' => "2026",
                        'is_active' => true,
                        'sequence_order' => 2,
                        'parent_etape_code' => "AGRC_PRCO_7",
                    ]
                ]
            ],
            [
                'code' => "AGRC_PRCO_8",
                'name' => "REMBOURSEMENT DU CREDIT ET L’EVALUATION DE L’IMPACT",
                'description' => "Le remboursement du crédit et l'évaluation de l'impact",
                'impact' => '',
                'version' => "2026",
                'is_active' => true,
                'sequence_order' => 1,
                'parent_etape_code' => "",
                'children' => [
                    [
                        'code' => "AGRC_PRCO_8_1",
                        'name' => "Remboursement du crédit",
                        'description' => "Le remboursement du crédit",
                        'impact' => 'EN_REMBOURSEMENT',
                        'version' => "2026",
                        'is_active' => true,
                        'sequence_order' => 1,
                        'parent_etape_code' => "AGRC_PRCO_8",
                    ],
                    [
                        'code' => "AGRC_PRCO_8_2",
                        'name' => "Evaluation et la réalisation d’études d’impact",
                        'description' => "L’évaluation et la réalisation d’études d’impact",
                        'impact' => 'EN_EVALUATION',
                        'version' => "2026",
                        'is_active' => true,
                        'sequence_order' => 2,
                        'parent_etape_code' => "AGRC_PRCO_8",
                    ]
                ]
            ]

        ],
        'AGR_PLUS' => [
            [
                'code' => "AGRP_PRCO_5",
                'name' => "DEBLOCAGE DU FINANCEMENT",
                'description' => "Le déblocage du financement",
                'impact' => 'EN_DECAISSEMENT',
                'version' => "2026",
                'is_active' => true,
                'sequence_order' => 1,
                'parent_etape_code' => "",
                'children' => []
            ],
            [
                'code' => "AGRP_PRCO_6",
                'name' => "SUIVI DES FINANCEMENTS",
                'description' => "Le suivi des financements",
                'impact' => 'EN_SUIVI',
                'version' => "2026",
                'is_active' => true,
                'sequence_order' => 2,
                'parent_etape_code' => "",
                'children' => []
            ],
            [
                'code' => "AGRP_PRCO_7",
                'name' => "REMBOURSEMENT DU CREDIT",
                'description' => "Le remboursement du crédit",
                'impact' => 'EN_REMBOURSEMENT',
                'version' => "2026",
                'is_active' => true,
                'sequence_order' => 3,
                'parent_etape_code' => "",
                'children' => []
            ],
            [
                'code' => "AGRP_PRCO_8",
                'name' => "EVALUATION",
                'description' => "L'évaluation",
                'impact' => 'EN_EVALUATION',
                'version' => "2026",
                'is_active' => true,
                'sequence_order' => 4,
                'parent_etape_code' => "",
                'children' => []
            ]
        ],
        'MPE' => [
            [
                'code' => "MPE_PRCO_5",
                'name' => "FINANCEMENT DES PROJETS",
                'description' => "Le financement des projets",
                'impact' => '',
                'version' => "2026",
                'is_active' => true,
                'sequence_order' => 1,
                'parent_etape_code' => "",
                'children' => [
                    [
                        'code' => "MPE_PRCO_1_1",
                        'name' => "Analyse des projets par l’institution financière",
                        'description' => "L’analyse des projets par l’institution financière",
                        'impact' => 'EN_FINANCEMENT',
                        'version' => "2026",
                        'is_active' => true,
                        'sequence_order' => 1,
                        'parent_etape_code' => "MPE_PRCO_1",
                    ],
                    [
                        'code' => "MPE_PRCO_1_2",
                        'name' => "Mise en place du financement",
                        'description' => "La mise en place du financement",
                        'impact' => 'EN_DECAISSEMENT',
                        'version' => "2026",
                        'is_active' => true,
                        'sequence_order' => 2,
                        'parent_etape_code' => "MPE_PRCO_1",
                    ]
                ]
            ],
            [
                'code' => "MPE_PRCO_6",
                'name' => "FORMATION",
                'description' => "La formation",
                'impact' => 'EN_FORMATION',
                'version' => "2026",
                'is_active' => true,
                'sequence_order' => 2,
                'parent_etape_code' => "",
                'children' => []
            ],
            [
                'code' => "MPE_PRCO_7",
                'name' => "LE SUIVI DES FINANCEMENTS ET LES REMBOURSEMENTS DES CREDITS",
                'description' => "Le suivi des financements et les remboursements des crédits",
                'impact' => '',
                'version' => "2026",
                'is_active' => true,
                'sequence_order' => 3,
                'parent_etape_code' => "",
                'children' => [
                    [
                        'code' => "MPE_PRCO_7_1",
                        'name' => "Suivi des financements",
                        'description' => "Le suivi des financements",
                        'impact' => 'EN_SUIVI',
                        'version' => "2026",
                        'is_active' => true,
                        'sequence_order' => 1,
                        'parent_etape_code' => "MPE_PRCO_7",
                    ],
                    [
                        'code' => "MPE_PRCO_7_2",
                        'name' => "Remboursement des crédits",
                        'description' => "Le remboursement des crédits",
                        'impact' => 'EN_REMBOURSEMENT',
                        'version' => "2026",
                        'is_active' => true,
                        'sequence_order' => 2,
                        'parent_etape_code' => "MPE_PRCO_7",
                    ],
                    [
                        'code' => "MPE_PRCO_7_3",
                        'name' => "Evaluation",
                        'description' => "L’évaluation",
                        'impact' => 'EN_EVALUATION',
                        'version' => "2026",
                        'is_active' => true,
                        'sequence_order' => 3,
                        'parent_etape_code' => "MPE_PRCO_7",
                    ]
                ]
            ]

        ],
        'MEPS' => [
            [
                'code' => "MEPS_PRCO_5",
                'name' => "FINANCEMENT DES PROJETS",
                'description' => "Le financement des projets",
                'impact' => '',
                'version' => "2026",
                'is_active' => true,
                'sequence_order' => 1,
                'parent_etape_code' => "",
                'children' => [
                    [
                        'code' => "MEPS_PRCO_5_1",
                        'name' => "Validation des projets et mise en place des financements",
                        'description' => "La validation des projets et mise en place des financements",
                        'impact' => 'EN_FINANCEMENT',
                        'version' => "2026",
                        'is_active' => true,
                        'sequence_order' => 1,
                        'parent_etape_code' => "MEPS_PRCO_5",
                    ],
                    [
                        'code' => "MEPS_PRCO_5_2",
                        'name' => "Elaboration et la validation du plan de décaissement",
                        'description' => "L’élaboration et la validation du plan de décaissement",
                        'impact' => 'EN_DECAISSEMENT',
                        'version' => "2026",
                        'is_active' => true,
                        'sequence_order' => 2,
                        'parent_etape_code' => "MEPS_PRCO_5",
                    ],
                    [
                        'code' => "MEPS_PRCO_5_3",
                        'name' => "Procédure de décaissement",
                        'description' => "La procédure de décaissement",
                        'impact' => 'EN_DECAISSEMENT',
                        'version' => "2026",
                        'is_active' => true,
                        'sequence_order' => 3,
                        'parent_etape_code' => "MEPS_PRCO_5",
                    ]
                ]
            ],
            [
                'code' => "MEPS_PRCO_6",
                'name' => "SUIVI DE L’EXPLOITATION",
                'description' => "Le suivi de l’exploitation",
                'impact' => 'EN_SUIVI',
                'version' => "2026",
                'is_active' => true,
                'sequence_order' => 2,
                'parent_etape_code' => "",
                'children' => []
            ],
            [
                'code' => "MEPS_PRCO_7",
                'name' => "SUIVI DU REMBOURSEMENT ET REPORTING",
                'description' => "Le suivi du remboursement et reporting",
                'impact' => 'EN_REMBOURSEMENT',
                'version' => "2026",
                'is_active' => true,
                'sequence_order' => 3,
                'parent_etape_code' => "",
                'children' => []
            ]
        ],
        'CAPITAL_INVEST' => [
            [
                'code' => "CAPINV_PRCO_5",
                'name' => "FINANCEMENT DES PROJETS",
                'description' => "Le financement des projets",
                'impact' => 'EN_FINANCEMENT',
                'version' => "2026",
                'is_active' => true,
                'sequence_order' => 1,
                'parent_etape_code' => "",
                'children' => []
            ],
            [
                'code' => "CAPINV_PRCO_6",
                'name' => "SUIVI DE L’EXPLOITATIONS",
                'description' => "Le suivi de l'exploitation",
                'impact' => 'EN_SUIVI',
                'version' => "2026",
                'is_active' => true,
                'sequence_order' => 2,
                'parent_etape_code' => "",
                'children' => []
            ],
            [
                'code' => "CAPINV_PRCO_7",
                'name' => "SUIVI DU REMBOURSEMENT",
                'description' => "Le suivi du remboursement",
                'impact' => 'EN_REMBOURSEMENT',
                'version' => "2026",
                'is_active' => true,
                'sequence_order' => 3,
                'parent_etape_code' => "",
                'children' => []
            ]
        ],
        'MENTORAT' => [
            [
                'code' => "MENT_PRCO_6",
                'name' => "LA MISE EN ŒUVRE DE L’ACCOMPAGNEMENT ",
                'description' => "La mise en œuvre de l'accompagnement",
                'impact' => 'EN_FINANCEMENT',
                'version' => "2026",
                'is_active' => true,
                'sequence_order' => 1,
                'parent_etape_code' => "",
                'children' => []
            ],
            [
                'code' => "MENT_PRCO_7",
                'name' => "SUIVI DE L’ACCOMPAGNEMENT",
                'description' => "Le suivi de l'accompagnement",
                'impact' => 'EN_SUIVI',
                'version' => "2026",
                'is_active' => true,
                'sequence_order' => 2,
                'parent_etape_code' => "",
                'children' => []
            ]
        ],
        'PERMIS' => [
            [
                'code' => "PERM_PRCO_1",
                'name' => "VISITE MEDICALE",
                'description' => "La visite médicale",
                'impact' => 'EN_SUIVI',
                'version' => "2026",
                'is_active' => true,
                'sequence_order' => 1,
                'parent_etape_code' => "",
                'children' => []
            ],
            [
                'code' => "PERM_PRCO_2",
                'name' => "FORMATION ET LE SUIVI DECENTRALISE DE LA MISE EN OEUVRE ",
                'description' => "La formation et le suivi décentralisé de la mise en œuvre",
                'impact' => 'EN_FORMATION',
                'version' => "2026",
                'is_active' => true,
                'sequence_order' => 2,
                'parent_etape_code' => "",
                'children' => []
            ]
        ],
        'STARTUP_BOOST' => [
            [
                'code' => "START_PRCO_1",
                'name' => "LES ACTIVITES PREALABLES AU FINANCEMENT ET LE FINANCEMENT DES PROJETS",
                'description' => "Les activités préalables au financement et le financement des projets",
                'impact' => 'EN_FINANCEMENT',
                'version' => "2026",
                'is_active' => true,
                'sequence_order' => 1,
                'parent_etape_code' => "",
                'children' => []
            ],
            [
                'code' => "START_PRCO_2",
                'name' => "SUIVI DES EXPLOITATIONS",
                'description' => "Le suivi des exploitations",
                'impact' => 'EN_SUIVI',
                'version' => "2026",
                'is_active' => true,
                'sequence_order' => 2,
                'parent_etape_code' => "",
                'children' => []
            ],
            [
                'code' => "START_PRCO_3",
                'name' => "LE SUIVI DU REMBOURSEMENT",
                'description' => "Le suivi du remboursement",
                'impact' => 'EN_REMBOURSEMENT',
                'version' => "2026",
                'is_active' => true,
                'sequence_order' => 3,
                'parent_etape_code' => "",
                'children' => []
            ]
        ]
    ];

    const WorkflowEtapesDeliverable = [

    ];

    const WorkflowEtapesRoles = [

    ];

    const WorkflowEtapesDecision = [

    ];

    const WorkflowDecisionOutcome = [

    ];

    // const WorkflowEtapesSla = [
        
    // ];
    
    // const WorkflowEtapesTransition = [
        
    // ];
}
