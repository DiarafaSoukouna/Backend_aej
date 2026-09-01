<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class WorkflowFactory extends Factory
{
    protected $model = \App\Models\Workflow::class;

    public function definition()
    {
        return [
            'code' => $this->faker->unique()->randomElement(['AGR_CLASSIQUE', 'AGR_PLUS', 'MPE', 'MEPS', 'CAPITAL_INVEST', 'MENTORAT', 'PERMIS', 'STARTUP_BOOST']),
            'name' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'is_active' => true,
        ];
    }

    // Modèles de workflow disponibles
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
            'description' => "Procédure des projets structurants / Start-up",
            'is_active' => TRUE
        ],
    ];

    // Versions de workflow disponibles
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

    // Etapes du workflow
    const WorkflowEtapes = [
        'AGR_CLASSIQUE' => [
            [
                'code' => "AGRC_PRCO_1",
                'name' => "RÉCUPÉRATION DES PROJETS SÉLECTIONNÉS",
                'description' => "Récupération de la liste des projets sélectionnés et pour lesquels les promoteurs ont été formés. Le rattachement à l'agence régionale est récupéré via l'API mise à disposition, ou à défaut déduit de la localisation du projet par région.",
                'impact' => 'DOSSIER_RECUPERE',
                'version' => "2026",
                'is_active' => true,
                'order' => 1,
                'parent_etape_code' => "",
                'champs_dossier' => ["N° de dossier", "Nom", "Prénom", "CIN", "Téléphone du promoteur", "Localisation", "Adresse", "Informations sur le projet", "Statut de la sélection", "Statut de la formation", "Date de formation", "Prestataire"],
                'children' => []
            ],
            [
                'code' => "AGRC_PRCO_2",
                'name' => "AJOUT DES PLANS D’AFFAIRES PAR LES AGENCES RÉGIONALES",
                'description' => "Les chefs d'agences régionales, conseillers en insertion pro. ou leurs assistants ajoutent le fichier du plan d'affaires pour chaque projet, puis transmettent la liste et les fichiers au chef de service développement des ressources de financement.",
                'impact' => 'PLAN_AFFAIRES_AJOUTE',
                'version' => "2026",
                'is_active' => true,
                'order' => 2,
                'parent_etape_code' => "",
                'children' => [
                    [
                        'code' => "AGRC_PRCO_2_1",
                        'name' => "VALIDATION DES PLANS D'AFFAIRES",
                        'description' => "Le chef de service développement des ressources de financement (ou un agent du service) valide les plans d'affaires ajoutés par les agences régionales.",
                        'impact' => 'PLAN_AFFAIRES_VALIDE',
                        'version' => "2026",
                        'is_active' => true,
                        'order' => 1,
                        'parent_etape_code' => "AGRC_PRCO_2",
                        'children' => []
                    ]
                ]
            ],
            [
                'code' => "AGRC_PRCO_3",
                'name' => "TRANSMISSION AU PARTENAIRE FINANCIER",
                'description' => "Le chef de service développement des ressources de financement (ou un agent du service) transmet les dossiers par lot via un fichier Excel qui répartit les projets entre partenaires. Le système transfère la liste sur l'interface du partenaire à partir du N° de dossier. Le courrier de transmission est joint et certaines de ses informations (référence, titre, date, taux de couverture, durée du différé, durée du remboursement, réf. de la convention) sont extraites et affichées sur chaque ligne de projet.",
                'impact' => 'TRANSMIS_PARTENAIRE',
                'version' => "2026",
                'is_active' => true,
                'order' => 3,
                'parent_etape_code' => "",
                'children' => []
            ],
            [
                'code' => "AGRC_PRCO_4",
                'name' => "TRAITEMENT DES DOSSIERS PAR LE PARTENAIRE FINANCIER",
                'description' => "Le partenaire financier renseigne, par saisie directe ou import Excel, les informations du lot de dossiers (date d'ouverture du compte, approbation ou non, montant du crédit, durée du prêt, taux d'intérêt, durée du remboursement), ajoute le tableau d'amortissement (import possible sur modèle fourni) et une copie du contrat/convention de prêt en pièce jointe. Les dossiers approuvés et les dossiers rejetés sont collectés dans deux listes distinctes puis renvoyés au chef de service développement des ressources de financement.",
                'impact' => 'EN_ANALYSE_PARTENAIRE',
                'version' => "2026",
                'is_active' => true,
                'order' => 4,
                'parent_etape_code' => "",
                'champs_dossier' => ["Date d'ouverture du compte", "Approbation ou non du dossier", "Montant du crédit", "Durée du prêt", "Taux d'intérêt", "Durée du remboursement"],
                'children' => []
            ],
            [
                'code' => "AGRC_PRCO_5",
                'name' => "DÉCAISSEMENT / REMBOURSEMENT",
                'description' => "Décaissement et suivi des remboursements des bénéficiaires par le partenaire financier.",
                'impact' => 'EN_FINANCEMENT',
                'version' => "2026",
                'is_active' => true,
                'order' => 5,
                'parent_etape_code' => "",
                'children' => [
                    [
                        'code' => "AGRC_PRCO_5_1",
                        'name' => "Décaissement",
                        'description' => "Le partenaire financier saisit les lignes de décaissement (N° décaissement, date, montant, bénéficiaires, etc.) avec les justificatifs.",
                        'impact' => 'EN_DECAISSEMENT',
                        'version' => "2026",
                        'is_active' => true,
                        'order' => 1,
                        'parent_etape_code' => "AGRC_PRCO_5",
                    ],
                    [
                        'code' => "AGRC_PRCO_5_2",
                        'name' => "Remboursement",
                        'description' => "Le partenaire financier saisit les remboursements des bénéficiaires (date de début, date de fin, montant remboursé, total remboursé, total restant, nombre de jours de retard), importés avec comme clé le N° de dossier. Suivi via 3 listes : à jour, moins de trois échéances d'impayés, plus de trois échéances d'impayés. Jusqu'au 3ème impayé : stratégie de recouvrement amiable du service monitoring (appel de rappel, courrier de rappel, décharge). Au-delà du 3ème impayé : sortie du portefeuille et transmission à l'avocat de l'AEJ pour recours en justice.",
                        'impact' => 'EN_REMBOURSEMENT',
                        'version' => "2026",
                        'is_active' => true,
                        'order' => 2,
                        'parent_etape_code' => "AGRC_PRCO_5",
                    ]
                ]
            ],
            [
                'code' => "AGRC_PRCO_6",
                'name' => "SUIVI",
                'description' => "Une fois le premier décaissement effectué, les agences régionales et la DPF réalisent des visites de suivi (date de la visite, état du projet, difficultés, recommandations, position GPS, photo du lieu), avec possibilité de joindre plusieurs fichiers (rapport de visite, etc.).",
                'impact' => 'EN_SUIVI',
                'version' => "2026",
                'is_active' => true,
                'order' => 6,
                'parent_etape_code' => "",
                'children' => []
            ],
        ],

        'AGR_PLUS' => [
            [
                'code' => "AGRP_PRCO_5",
                'name' => "DEBLOCAGE DU FINANCEMENT",
                'description' => "Le déblocage du financement",
                'impact' => 'EN_DECAISSEMENT',
                'version' => "2026",
                'is_active' => true,
                'order' => 1,
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
                'order' => 2,
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
                'order' => 3,
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
                'order' => 4,
                'parent_etape_code' => "",
                'children' => []
            ]
        ],

        'MPE' => [
            [
                'code' => "MPE_PRCO_1",
                'name' => "RÉCUPÉRATION DE LA LISTE DES PROJETS",
                'description' => "Récupération de la liste des projets sélectionnés et pour lesquels les promoteurs ont été formés. Liste disponible pour tous les profils de l'AEJ. Le rattachement à l'agence régionale est récupéré via l'API mise à disposition, ou à défaut déduit de la localisation du projet par région.",
                'impact' => 'DOSSIER_RECUPERE',
                'version' => "2026",
                'is_active' => true,
                'order' => 1,
                'parent_etape_code' => "",
                'champs_dossier' => ["N° de dossier", "Nom", "Prénom", "CIN", "Téléphone du promoteur", "Localisation", "Adresse", "Informations sur le projet", "Statut de la sélection", "Statut de la formation", "Date de formation", "Prestataire", "Plan d'affaires"],
                'children' => []
            ],
            [
                'code' => "MPE_PRCO_2",
                'name' => "TRANSMISSION AU PARTENAIRE FINANCIER",
                'description' => "Le chef de service développement des ressources de financement (ou un agent du service) ajoute le plan d'affaires puis transmet les dossiers par lot via un fichier Excel qui répartit les projets entre partenaires. Le courrier de transmission est joint et certaines de ses informations (référence, titre, date, taux de couverture, durée du différé, durée du remboursement, réf. de la convention) sont extraites et affichées sur chaque ligne de projet.",
                'impact' => 'TRANSMIS_PARTENAIRE',
                'version' => "2026",
                'is_active' => true,
                'order' => 2,
                'parent_etape_code' => "",
                'children' => []
            ],
            [
                'code' => "MPE_PRCO_3",
                'name' => "IMPUTATION DES DOSSIERS APPROUVÉS AUX AGENCES RÉGIONALES",
                'description' => "Sur la base des dossiers approuvés par le partenaire financier, le chef de service financement et monitoring impute certains dossiers aux agences régionales selon leur localisation. Les dossiers restants non imputés sont gérés directement par la Direction pour la saisie des plans de décaissement.",
                'impact' => 'IMPUTE_AGENCE',
                'version' => "2026",
                'is_active' => true,
                'order' => 3,
                'parent_etape_code' => "",
                'children' => []
            ],
            [
                'code' => "MPE_PRCO_4",
                'name' => "SAISIE DES PLANS DE DÉCAISSEMENT PAR LES CIP DES AGENCES RÉGIONALES",
                'description' => "Le conseiller en insertion professionnelle (ou un agent de la direction pour les dossiers non imputés) remplit les plans de décaissement des bénéficiaires, avec possibilité d'ajouter une note ou observation, et joint le fichier PDF du plan de décaissement signé par le bénéficiaire. Le plan de décaissement est partagé au bénéficiaire qui le valide via l'application mobile.",
                'impact' => 'PLAN_DECAISSEMENT_SAISI',
                'version' => "2026",
                'is_active' => true,
                'order' => 4,
                'parent_etape_code' => "",
                'children' => []
            ],
            [
                'code' => "MPE_PRCO_5",
                'name' => "TRANSMISSION DES DOSSIERS AVEC LES PLANS DE DÉCAISSEMENT SAISIS",
                'description' => "Chaîne de validation interne du plan de décaissement avant transmission au partenaire financier.",
                'impact' => 'EN_VALIDATION_INTERNE',
                'version' => "2026",
                'is_active' => true,
                'order' => 5,
                'parent_etape_code' => "",
                'children' => [
                    [
                        'code' => "MPE_PRCO_5_1",
                        'name' => "Cas des agences régionales (CAR)",
                        'description' => "Le CIP envoie les dossiers au chef d'agence qui valide ou ajourne les plans de décaissement saisis, puis transmet les dossiers validés au chef de service développement des ressources de financement qui vérifie et soumet au sous-directeur de l'évaluation financière, qui valide et soumet au sous-directeur du partenariat et financement, qui valide et soumet au Directeur du partenariat et financement, qui valide et transmet au partenaire financier.",
                        'impact' => 'EN_VALIDATION_INTERNE',
                        'version' => "2026",
                        'is_active' => true,
                        'order' => 1,
                        'parent_etape_code' => "MPE_PRCO_5",
                    ],
                    [
                        'code' => "MPE_PRCO_5_2",
                        'name' => "Cas de l'agent de la direction (SDRF)",
                        'description' => "L'agent envoie les dossiers avec les plans de décaissement saisis a direction de l'evaluation financière",
                        'impact' => 'EN_VALIDATION_INTERNE',
                        'version' => "2026",
                        'is_active' => true,
                        'order' => 2,
                        'parent_etape_code' => "MPE_PRCO_5",
                    ],
                    [
                        'code' => "MPE_PRCO_5_3",
                        'name' => "Cas de l'agent de direction de l'évaluation financière (SDEF)",
                        'description' => "L'agent envoie les dossiers avec les plans de décaissement saisis a la direction du partenariat et financement",
                        'impact' => 'EN_VALIDATION_INTERNE',
                        'version' => "2026",
                        'is_active' => true,
                        'order' => 3,
                        'parent_etape_code' => "MPE_PRCO_5",
                    ],
                    [
                        'code' => "MPE_PRCO_5_4",
                        'name' => "Cas de l'agent de la direction du partenariat et financement (SDPF)",
                        'description' => "L'agent envoie les dossiers avec les plans de décaissement saisis au directeur du partenariat et financement",
                        'impact' => 'EN_VALIDATION_INTERNE',
                        'version' => "2026",
                        'is_active' => true,
                        'order' => 4,
                        'parent_etape_code' => "MPE_PRCO_5",
                    ],
                    [
                        'code' => "MPE_PRCO_5_5",
                        'name' => "Cas du directeur du partenariat et financement (DPF)",
                        'description' => "Le directeur envoie les dossiers avec les plans de décaissement saisis au partenaire financier pour traitement",
                        'impact' => 'EN_VALIDATION_INTERNE',
                        'version' => "2026",
                        'is_active' => true,
                        'order' => 5,
                        'parent_etape_code' => "MPE_PRCO_5",
                    ],
                ]
            ],
            [
                'code' => "MPE_PRCO_6",
                'name' => "TRAITEMENT DES DOSSIERS PAR LE PARTENAIRE",
                'description' => "Le partenaire financier renseigne, par saisie directe ou import Excel, les informations du lot de dossiers (date d'ouverture du compte, approbation ou non, montant du crédit, durée du prêt, taux d'intérêt, durée du remboursement), ajoute le tableau d'amortissement (import possible) et une copie du contrat/convention de prêt en pièce jointe. Les dossiers approuvés et rejetés sont collectés dans deux listes distinctes puis renvoyés au chef de service développement des ressources de financement. Un outil de recherche permet à toute agence ou agent de l'AEJ de retrouver un dossier par nom, téléphone ou N° de dossier du bénéficiaire.",
                'impact' => 'EN_ANALYSE_PARTENAIRE',
                'version' => "2026",
                'is_active' => true,
                'order' => 6,
                'parent_etape_code' => "",
                'champs_dossier' => ["Date d'ouverture du compte", "Approbation ou non du dossier", "Montant du crédit", "Durée du prêt", "Taux d'intérêt", "Durée du remboursement"],
                'children' => []
            ],
            [
                'code' => "MPE_PRCO_7",
                'name' => "DÉCAISSEMENT / REMBOURSEMENT",
                'description' => "Décaissement autorisé par le partenaire puis exécuté par les agences régionales, et suivi des remboursements.",
                'impact' => 'EN_FINANCEMENT',
                'version' => "2026",
                'is_active' => true,
                'order' => 7,
                'parent_etape_code' => "",
                'children' => [
                    [
                        'code' => "MPE_PRCO_7_1",
                        'name' => "Décaissement",
                        'description' => "Conformément au plan de décaissement saisi et validé, le partenaire financier « Autorise » chaque ligne de décaissement (date, justificatifs). Chaque ligne autorisée doit être « Exécutée » par les agences régionales (date, justificatifs). Une fois la première ligne exécutée, le partenaire peut autoriser la ligne suivante, et ainsi de suite.",
                        'impact' => 'EN_DECAISSEMENT',
                        'version' => "2026",
                        'is_active' => true,
                        'order' => 1,
                        'parent_etape_code' => "MPE_PRCO_7",
                    ],
                    [
                        'code' => "MPE_PRCO_7_2",
                        'name' => "Remboursement",
                        'description' => "Le partenaire financier saisit les remboursements des bénéficiaires (date de début, date de fin, montant remboursé, total remboursé, total restant, jours de retard), importés avec comme clé le N° de dossier. Suivi via 3 listes : à jour, moins de trois échéances d'impayés, plus de trois échéances d'impayés. Les recouvrements sont opérés par la société CO2CI qui reverse au nom du bénéficiaire. Jusqu'au 3ème impayé : recouvrement amiable par le service monitoring. Au-delà du 3ème impayé : sortie du portefeuille, transmission à l'avocat de l'AEJ pour recours en justice, et rappel de la garantie par le partenaire financier (montant de la garantie, date du rappel).",
                        'impact' => 'EN_REMBOURSEMENT',
                        'version' => "2026",
                        'is_active' => true,
                        'order' => 2,
                        'parent_etape_code' => "MPE_PRCO_7",
                    ]
                ]
            ],
            [
                'code' => "MPE_PRCO_8",
                'name' => "SUIVI",
                'description' => "Une fois le premier décaissement effectué, les agences régionales et la DPF réalisent des visites de suivi (date de la visite, état du projet, difficultés, recommandations, position GPS, photo du lieu), avec possibilité de joindre plusieurs fichiers (rapport de visite, etc.).",
                'impact' => 'EN_SUIVI',
                'version' => "2026",
                'is_active' => true,
                'order' => 8,
                'parent_etape_code' => "",
                'children' => []
            ],
        ],

        'MEPS' => [
            [
                'code' => "MEPS_PRCO_1",
                'name' => "RÉCUPÉRATION DE LA LISTE DES PROJETS",
                'description' => "Récupération de la liste des projets sélectionnés et pour lesquels les promoteurs ont été formés. Liste disponible pour tous les profils de l'AEJ. Le rattachement à l'agence régionale est récupéré via l'API mise à disposition, ou à défaut déduit de la localisation du projet par région.",
                'impact' => 'DOSSIER_RECUPERE',
                'version' => "2026",
                'is_active' => true,
                'order' => 1,
                'parent_etape_code' => "",
                'champs_dossier' => ["N° de dossier", "Nom", "Prénom", "CIN", "Téléphone du promoteur", "Localisation", "Adresse", "Informations sur le projet", "Statut de la sélection", "Statut de la formation", "Date de formation", "Prestataire", "Plan d'affaires"],
                'children' => []
            ],
            [
                'code' => "MEPS_PRCO_2",
                'name' => "TRANSMISSION AU PARTENAIRE FINANCIER",
                'description' => "Le chef de service développement des ressources de financement (ou un agent du service) ajoute le plan d'affaires puis transmet les dossiers par lot via un fichier Excel qui répartit les projets entre partenaires. Le courrier de transmission est joint et certaines de ses informations (référence, titre, date, taux de couverture, durée du différé, durée du remboursement, réf. de la convention) sont extraites et affichées sur chaque ligne de projet.",
                'impact' => 'TRANSMIS_PARTENAIRE',
                'version' => "2026",
                'is_active' => true,
                'order' => 2,
                'parent_etape_code' => "",
                'children' => []
            ],
            [
                'code' => "MEPS_PRCO_3",
                'name' => "IMPUTATION DES DOSSIERS APPROUVÉS AUX AGENCES RÉGIONALES",
                'description' => "Sur la base des dossiers approuvés par le partenaire financier, le chef de service financement et monitoring impute certains dossiers aux agences régionales selon leur localisation. Les dossiers restants non imputés sont gérés directement par la Direction pour la saisie des plans de décaissement.",
                'impact' => 'IMPUTE_AGENCE',
                'version' => "2026",
                'is_active' => true,
                'order' => 3,
                'parent_etape_code' => "",
                'children' => []
            ],
            [
                'code' => "MEPS_PRCO_4",
                'name' => "SAISIE DES PLANS DE DÉCAISSEMENT PAR LES CIP DES AGENCES RÉGIONALES",
                'description' => "Le conseiller en insertion professionnelle (ou un agent de la direction pour les dossiers non imputés) remplit les plans de décaissement des bénéficiaires, avec possibilité d'ajouter une note ou observation, et joint le fichier PDF du plan de décaissement signé par le bénéficiaire. Le plan de décaissement est partagé au bénéficiaire qui le valide via l'application mobile.",
                'impact' => 'PLAN_DECAISSEMENT_SAISI',
                'version' => "2026",
                'is_active' => true,
                'order' => 4,
                'parent_etape_code' => "",
                'children' => []
            ],
            [
                'code' => "MEPS_PRCO_5",
                'name' => "TRANSMISSION DES DOSSIERS AVEC LES PLANS DE DÉCAISSEMENT SAISIS",
                'description' => "Chaîne de validation interne du plan de décaissement avant transmission au partenaire financier.",
                'impact' => 'EN_VALIDATION_INTERNE',
                'version' => "2026",
                'is_active' => true,
                'order' => 5,
                'parent_etape_code' => "",
                'children' => [
                    [
                        'code' => "MEPS_PRCO_5_1",
                        'name' => "Cas des agences régionales",
                        'description' => "Le CIP envoie les dossiers au chef d'agence qui valide ou ajourne les plans de décaissement saisis, puis transmet les dossiers validés au chef de service développement des ressources de financement qui vérifie et soumet au sous-directeur de l'évaluation financière, qui valide et soumet au sous-directeur du partenariat et financement, qui valide et soumet au Directeur du partenariat et financement, qui valide et transmet au partenaire financier.",
                        'impact' => 'EN_VALIDATION_INTERNE',
                        'version' => "2026",
                        'is_active' => true,
                        'order' => 1,
                        'parent_etape_code' => "MEPS_PRCO_5",
                    ],
                    [
                        'code' => "MEPS_PRCO_5_2",
                        'name' => "Cas de l'agent de la direction",
                        'description' => "L'agent envoie les dossiers avec les plans de décaissement saisis au chef de service financement et monitoring, puis transmet les dossiers validés au chef de service développement des ressources de financement qui vérifie et soumet au sous-directeur de l'évaluation financière, qui valide et soumet au sous-directeur du partenariat et financement, qui valide et soumet au Directeur du partenariat et financement, qui valide et transmet au partenaire financier.",
                        'impact' => 'EN_VALIDATION_INTERNE',
                        'version' => "2026",
                        'is_active' => true,
                        'order' => 2,
                        'parent_etape_code' => "MEPS_PRCO_5",
                    ]
                ]
            ],
            [
                'code' => "MEPS_PRCO_6",
                'name' => "TRAITEMENT DES DOSSIERS PAR LE PARTENAIRE",
                'description' => "Le partenaire financier renseigne, par saisie directe ou import Excel, les informations du lot de dossiers (date d'ouverture du compte, approbation ou non, montant du crédit, durée du prêt, taux d'intérêt, durée du remboursement), ajoute le tableau d'amortissement (import possible) et une copie du contrat/convention de prêt en pièce jointe. Les dossiers approuvés et rejetés sont collectés dans deux listes distinctes puis renvoyés au chef de service développement des ressources de financement. Un outil de recherche permet à toute agence ou agent de l'AEJ de retrouver un dossier par nom, téléphone ou N° de dossier du bénéficiaire.",
                'impact' => 'EN_ANALYSE_PARTENAIRE',
                'version' => "2026",
                'is_active' => true,
                'order' => 6,
                'parent_etape_code' => "",
                'champs_dossier' => ["Date d'ouverture du compte", "Approbation ou non du dossier", "Montant du crédit", "Durée du prêt", "Taux d'intérêt", "Durée du remboursement"],
                'children' => []
            ],
            [
                'code' => "MEPS_PRCO_7",
                'name' => "DÉCAISSEMENT / REMBOURSEMENT",
                'description' => "Décaissement autorisé par le partenaire puis exécuté par les agences régionales, et suivi des remboursements.",
                'impact' => 'EN_FINANCEMENT',
                'version' => "2026",
                'is_active' => true,
                'order' => 7,
                'parent_etape_code' => "",
                'children' => [
                    [
                        'code' => "MEPS_PRCO_7_1",
                        'name' => "Décaissement",
                        'description' => "Conformément au plan de décaissement saisi et validé, le partenaire financier « Autorise » chaque ligne de décaissement (date, justificatifs). Chaque ligne autorisée doit être « Exécutée » par les agences régionales (date, justificatifs). Une fois la première ligne exécutée, le partenaire peut autoriser la ligne suivante, et ainsi de suite.",
                        'impact' => 'EN_DECAISSEMENT',
                        'version' => "2026",
                        'is_active' => true,
                        'order' => 1,
                        'parent_etape_code' => "MEPS_PRCO_7",
                    ],
                    [
                        'code' => "MEPS_PRCO_7_2",
                        'name' => "Remboursement",
                        'description' => "Le partenaire financier saisit les remboursements des bénéficiaires (date de début, date de fin, montant remboursé, total remboursé, total restant, jours de retard), importés avec comme clé le N° de dossier. Suivi via 3 listes : à jour, moins de trois échéances d'impayés, plus de trois échéances d'impayés. Les recouvrements sont opérés par la société CO2CI qui reverse au nom du bénéficiaire. Jusqu'au 3ème impayé : recouvrement amiable par le service monitoring. Au-delà du 3ème impayé : sortie du portefeuille, transmission à l'avocat de l'AEJ pour recours en justice, et rappel de la garantie par le partenaire financier (montant de la garantie, date du rappel).",
                        'impact' => 'EN_REMBOURSEMENT',
                        'version' => "2026",
                        'is_active' => true,
                        'order' => 2,
                        'parent_etape_code' => "MEPS_PRCO_7",
                    ]
                ]
            ],
            [
                'code' => "MEPS_PRCO_8",
                'name' => "SUIVI",
                'description' => "Une fois le premier décaissement effectué, les agences régionales et la DPF réalisent des visites de suivi (date de la visite, état du projet, difficultés, recommandations, position GPS, photo du lieu), avec possibilité de joindre plusieurs fichiers (rapport de visite, etc.).",
                'impact' => 'EN_SUIVI',
                'version' => "2026",
                'is_active' => true,
                'order' => 8,
                'parent_etape_code' => "",
                'children' => []
            ],
        ],

        'CAPITAL_INVEST' => [
            [
                'code' => "CAPINV_PRCO_5",
                'name' => "FINANCEMENT DES PROJETS",
                'description' => "Le financement des projets",
                'impact' => 'EN_FINANCEMENT',
                'version' => "2026",
                'is_active' => true,
                'order' => 1,
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
                'order' => 2,
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
                'order' => 3,
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
                'order' => 1,
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
                'order' => 2,
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
                'order' => 1,
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
                'order' => 2,
                'parent_etape_code' => "",
                'children' => []
            ]
        ],

        'STARTUP_BOOST' => [
            [
                'code' => "START_PRCO_1",
                'name' => "RÉCUPÉRATION DE LA LISTE DES PROJETS CERTIFIÉS",
                'description' => "Récupération de la liste des projets certifiés. Liste disponible pour tous les profils de l'AEJ. Le rattachement à l'agence régionale est récupéré via l'API mise à disposition, ou à défaut déduit de la localisation du projet par région.",
                'impact' => 'DOSSIER_RECUPERE',
                'version' => "2026",
                'is_active' => true,
                'order' => 1,
                'parent_etape_code' => "",
                'champs_dossier' => ["N° de dossier", "Nom", "Prénom", "CIN", "Téléphone du promoteur", "Localisation", "Adresse", "Informations sur le projet", "Statut de la sélection", "Statut de la formation", "Date de formation", "Prestataire", "Fichier rapport d'analyse", "Fichier dossier de crédit"],
                'children' => []
            ],
            [
                'code' => "START_PRCO_2",
                'name' => "TRANSMISSION AU PARTENAIRE FINANCIER",
                'description' => "Le chef de service développement des ressources de financement (ou un agent du service) ajoute le nouveau plan d'affaires puis transmet les dossiers par lot via un fichier Excel qui répartit les projets entre partenaires. Le courrier de transmission est joint et certaines de ses informations (référence, titre, date, taux de couverture, durée du différé, durée du remboursement, réf. de la convention) sont extraites et affichées sur chaque ligne de projet.",
                'impact' => 'TRANSMIS_PARTENAIRE',
                'version' => "2026",
                'is_active' => true,
                'order' => 2,
                'parent_etape_code' => "",
                'children' => []
            ],
            [
                'code' => "START_PRCO_3",
                'name' => "SAISIE DU PLAN DE DÉCAISSEMENT ET AJOUT DE LA CONVENTION DE PRÊT",
                'description' => "Deux actions menées en parallèle une fois les dossiers approuvés par le partenaire financier.",
                'impact' => 'EN_FINANCEMENT',
                'version' => "2026",
                'is_active' => true,
                'order' => 3,
                'parent_etape_code' => "",
                'children' => [
                    [
                        'code' => "START_PRCO_3_1",
                        'name' => "Saisie des plans de décaissement par les CIP des agences régionales",
                        'description' => "Le conseiller en insertion professionnel remplit les plans de décaissement des bénéficiaires, avec possibilité d'ajouter une note ou observation, et joint le fichier PDF du plan de décaissement signé par le bénéficiaire. Le plan de décaissement est partagé au bénéficiaire qui le valide via l'application mobile.",
                        'impact' => 'PLAN_DECAISSEMENT_SAISI',
                        'version' => "2026",
                        'is_active' => true,
                        'order' => 1,
                        'parent_etape_code' => "START_PRCO_3",
                    ],
                    [
                        'code' => "START_PRCO_3_2",
                        'name' => "Ajout des conventions de prêt par le service garantie et contentieux",
                        'description' => "En parallèle de la saisie des plans de décaissement, le chef de service garantie et contentieux ajoute, pour chaque dossier, la convention de prêt signée. Cette information est notifiée à l'ensemble des utilisateurs.",
                        'impact' => 'CONVENTION_PRET_AJOUTEE',
                        'version' => "2026",
                        'is_active' => true,
                        'order' => 2,
                        'parent_etape_code' => "START_PRCO_3",
                    ]
                ]
            ],
            [
                'code' => "START_PRCO_4",
                'name' => "TRANSMISSION DES DOSSIERS AVEC LES PLANS DE DÉCAISSEMENT SAISIS",
                'description' => "Le CIP envoie les dossiers au chef d'agence qui valide ou ajourne les plans de décaissement saisis, puis transmet les dossiers validés au chef de service développement des ressources de financement qui vérifie et soumet au sous-directeur de l'évaluation financière, qui valide et soumet au sous-directeur du partenariat et financement, qui valide et soumet au Directeur du partenariat et financement, qui valide et transmet au partenaire financier.",
                'impact' => 'EN_VALIDATION_INTERNE',
                'version' => "2026",
                'is_active' => true,
                'order' => 4,
                'parent_etape_code' => "",
                'children' => []
            ],
            [
                'code' => "START_PRCO_5",
                'name' => "TRAITEMENT DES DOSSIERS PAR LE PARTENAIRE",
                'description' => "Le partenaire financier renseigne, par saisie directe ou import Excel, les informations du lot de dossiers (date d'ouverture du compte, approbation ou non, montant du crédit, durée du prêt, taux d'intérêt, durée du remboursement), ajoute le tableau d'amortissement (import possible) et une copie du contrat/convention de prêt en pièce jointe. Les dossiers approuvés et rejetés sont collectés dans deux listes distinctes puis renvoyés au chef de service développement des ressources de financement.",
                'impact' => 'EN_ANALYSE_PARTENAIRE',
                'version' => "2026",
                'is_active' => true,
                'order' => 5,
                'parent_etape_code' => "",
                'champs_dossier' => ["Date d'ouverture du compte", "Approbation ou non du dossier", "Montant du crédit", "Durée du prêt", "Taux d'intérêt", "Durée du remboursement"],
                'children' => []
            ],
            [
                'code' => "START_PRCO_6",
                'name' => "DÉCAISSEMENT / REMBOURSEMENT",
                'description' => "Décaissement autorisé par le partenaire puis exécuté par les agences régionales, et suivi des remboursements.",
                'impact' => 'EN_FINANCEMENT',
                'version' => "2026",
                'is_active' => true,
                'order' => 6,
                'parent_etape_code' => "",
                'children' => [
                    [
                        'code' => "START_PRCO_6_1",
                        'name' => "Décaissement",
                        'description' => "Conformément au plan de décaissement saisi et validé, le partenaire financier « Autorise » chaque ligne de décaissement (date, justificatifs). Chaque ligne autorisée doit être « Exécutée » par les agences régionales (date, justificatifs). Une fois la première ligne exécutée, le partenaire peut autoriser la ligne suivante, et ainsi de suite.",
                        'impact' => 'EN_DECAISSEMENT',
                        'version' => "2026",
                        'is_active' => true,
                        'order' => 1,
                        'parent_etape_code' => "START_PRCO_6",
                    ],
                    [
                        'code' => "START_PRCO_6_2",
                        'name' => "Remboursement",
                        'description' => "Le partenaire financier saisit les remboursements des bénéficiaires (date de début, date de fin, montant remboursé, total remboursé, total restant, jours de retard), importés avec comme clé le N° de dossier. Suivi via 3 listes : à jour, moins de trois échéances d'impayés, plus de trois échéances d'impayés. Les recouvrements sont opérés par la société CO2CI qui reverse au nom du bénéficiaire. Jusqu'au 3ème impayé : recouvrement amiable par le service monitoring. Au-delà du 3ème impayé : sortie du portefeuille, transmission à l'avocat de l'AEJ pour recours en justice, et rappel de la garantie par le partenaire financier (montant de la garantie, date du rappel).",
                        'impact' => 'EN_REMBOURSEMENT',
                        'version' => "2026",
                        'is_active' => true,
                        'order' => 2,
                        'parent_etape_code' => "START_PRCO_6",
                    ]
                ]
            ],
            [
                'code' => "START_PRCO_7",
                'name' => "SUIVI",
                'description' => "Une fois le premier décaissement effectué, les agences régionales et la DPF réalisent des visites de suivi (date de la visite, état du projet, difficultés, recommandations, position GPS, photo du lieu), avec possibilité de joindre plusieurs fichiers (rapport de visite, etc.).",
                'impact' => 'EN_SUIVI',
                'version' => "2026",
                'is_active' => true,
                'order' => 7,
                'parent_etape_code' => "",
                'children' => []
            ],
        ],
    ];

    // Référentiel des rôles/profils intervenant dans les workflows
    const WorkflowRoles = [
        'CIP' => [
            'code' => "CIP",
            'name' => "Conseiller en Insertion Professionnelle",
            'description' => "Agent rattaché à l'agence régionale, saisit les plans de décaissement des bénéficiaires",
            'is_active' => TRUE
        ],
        'CAR' => [
            'code' => "CAR",
            'name' => "Chef d'agence régionale",
            'description' => "Ajoute les plans d'affaires, valide ou ajourne les plans de décaissement saisis par le CIP",
            'is_active' => TRUE
        ],
        'AGENT_AR' => [
            'code' => "AGENT_AR",
            'name' => "Agent de l'agence régionale",
            'description' => "Profils rattachés à l'agence régionale (chef d'agence, CIP, assistant) selon la zone du dossier ; exécute les lignes de décaissement autorisées et réalise les visites de suivi",
            'is_active' => TRUE
        ],
        'SDRF' => [
            'code' => "SDRF",
            'name' => "Chef de service développement des ressources de financement",
            'description' => "Transmet les dossiers/plans d'affaires aux partenaires financiers, vérifie les dossiers avant soumission ; titulaire du bouton \"annulation du prêt\"",
            'is_active' => TRUE
        ],
        'AGENT_DRF' => [
            'code' => "AGENT_DRF",
            'name' => "Agent du service développement des ressources de financement",
            'description' => "Peut effectuer, par délégation, la transmission aux partenaires financiers",
            'is_active' => TRUE
        ],
        'AGENT_DIR' => [
            'code' => "AGENT_DIR",
            'name' => "Agent de la direction",
            'description' => "Remplit les plans de décaissement pour les dossiers non imputés à une agence régionale (cas MPE/MEPS)",
            'is_active' => TRUE
        ],
        'CSFM' => [
            'code' => "CSFM",
            'name' => "Chef de service financement et monitoring",
            'description' => "Impute les dossiers approuvés aux agences régionales selon la localisation (MPE/MEPS)",
            'is_active' => TRUE
        ],
        'SERVICE_MONITORING' => [
            'code' => "SERVICE_MONITORING",
            'name' => "Service monitoring",
            'description' => "Pilote la stratégie de recouvrement amiable jusqu'au 3ème impayé (appels, courriers de rappel, décharge)",
            'is_active' => TRUE
        ],
        'CSRGC' => [
            'code' => "CSRGC",
            'name' => "Service garantie et contentieux",
            'description' => "Ajoute les conventions de prêts signées (projets structurants/Start Up)",
            'is_active' => TRUE
        ],
        'SDEF' => [
            'code' => "SDEF",
            'name' => "Sous-directeur de l'évaluation financière",
            'description' => "Valide les dossiers avant soumission au sous-directeur du partenariat et financement",
            'is_active' => TRUE
        ],
        'SDPF' => [
            'code' => "SDPF",
            'name' => "Sous-directeur du partenariat et financement",
            'description' => "Valide les dossiers avant soumission au Directeur du partenariat et financement",
            'is_active' => TRUE
        ],
        'DPF' => [
            'code' => "DPF",
            'name' => "Directeur du partenariat et financement",
            'description' => "Valide et transmet le dossier final au partenaire financier ; pilote, avec les agences régionales, les visites de suivi",
            'is_active' => TRUE
        ],
        'PF' => [
            'code' => "PF",
            'name' => "Partenaire financier",
            'description' => "Analyse, approuve/rejette les dossiers, autorise les décaissements, saisit les remboursements et le rappel de garantie",
            'is_active' => TRUE
        ],
        'AF' => [
            'code' => "AF",
            'name' => "Analyste financier",
            'description' => "Accès en consultation uniquement, pour les dossiers des guichets MPE, MEPS et Projets structurants/Start Up",
            'is_active' => TRUE
        ],
        'AVOCAT_AEJ' => [
            'code' => "AVOCAT_AEJ",
            'name' => "Avocat de l'AEJ",
            'description' => "Prend en charge le recours en justice au-delà du 3ème impayé",
            'is_active' => TRUE
        ],
        'CO2CI' => [
            'code' => "CO2CI",
            'name' => "Société de recouvrement",
            'description' => "Assure le recouvrement et le versement au nom du bénéficiaire via son propre outil (MPE/MEPS et Projets structurants/Start Up)",
            'is_active' => TRUE
        ],
        'BENEF' => [
            'code' => "BENEF",
            'name' => "Bénéficiaire / promoteur",
            'description' => "Valide le plan de décaissement via l'application mobile",
            'is_active' => TRUE
        ],
    ];

    // Référentiel des issues de décision possibles, référencé par WorkflowEtapesDecision.outcomes
    const WorkflowDecisionOutcome = [
        'APPROUVE' => "Approuvé",
        'REJETE' => "Rejeté",
        'VALIDE' => "Validé",
        'AJOURNE' => "Ajourné",
        'AUTORISE' => "Autorisé",
        'EN_ATTENTE' => "En attente",
        'MAINTIEN_AMIABLE' => "Maintien en phase de recouvrement amiable",
        'TRANSMIS_AVOCAT' => "Transmis à l'avocat de l'AEJ pour recours en justice",
        'RAPPEL_GARANTIE' => "Rappel de la garantie effectué",
        'PRET_ANNULE' => "Prêt annulé",
    ];

    // Référentiel des pièces/livrables cités dans le document
    const WorkflowDeliverables = [
        'PLAN_AFFAIRES' => [
            'code' => "PLAN_AFFAIRES",
            'name' => "Plan d'affaires",
            'description' => "Ajouté par l'agence régionale pour chaque projet sélectionné (AGR) ou par le chef de service DRF (MPE/MEPS, Projets structurants/Start Up)",
            'is_active' => TRUE
        ],
        'COURRIER_TRANSMISSION' => [
            'code' => "COURRIER_TRANSMISSION",
            'name' => "Courrier de transmission au partenaire financier",
            'description' => "Pièce jointe envoyée avec chaque lot de dossiers (réf. courrier, titre, date de transmission, taux de couverture, durée du différé, durée du remboursement, réf. de la convention à extraire)",
            'is_active' => TRUE
        ],
        'FICHIER_REPARTITION_EXCEL' => [
            'code' => "FICHIER_REPARTITION_EXCEL",
            'name' => "Fichier Excel de répartition des projets entre partenaires",
            'description' => "Fichier utilisé pour transmettre les dossiers par lot à chaque partenaire financier",
            'is_active' => TRUE
        ],
        'CONTRAT_CONVENTION_PRET' => [
            'code' => "CONTRAT_CONVENTION_PRET",
            'name' => "Contrat / convention de prêt",
            'description' => "Copie du contrat ou de la convention de prêt : ajoutée par le partenaire financier (AGR, MPE/MEPS) ou par le service garantie et contentieux (Projets structurants/Start Up)",
            'is_active' => TRUE
        ],
        'TABLEAU_AMORTISSEMENT' => [
            'code' => "TABLEAU_AMORTISSEMENT",
            'name' => "Tableau d'amortissement",
            'description' => "Ajouté par le partenaire financier, importable sur un modèle standard mis à disposition",
            'is_active' => TRUE
        ],
        'PLAN_DECAISSEMENT' => [
            'code' => "PLAN_DECAISSEMENT",
            'name' => "Plan de décaissement",
            'description' => "Fichier PDF du plan de décaissement signé par le bénéficiaire, saisi par le CIP (MPE/MEPS, Projets structurants/Start Up)",
            'is_active' => TRUE
        ],
        'JUSTIFICATIF_DECAISSEMENT' => [
            'code' => "JUSTIFICATIF_DECAISSEMENT",
            'name' => "Justificatif de décaissement",
            'description' => "Pièce jointe à chaque ligne de décaissement",
            'is_active' => TRUE
        ],
        'JUSTIFICATIF_REMBOURSEMENT' => [
            'code' => "JUSTIFICATIF_REMBOURSEMENT",
            'name' => "Justificatif de remboursement",
            'description' => "Pièces associées à la saisie des remboursements",
            'is_active' => TRUE
        ],
        'RAPPORT_VISITE_SUIVI' => [
            'code' => "RAPPORT_VISITE_SUIVI",
            'name' => "Rapport de visite de suivi",
            'description' => "Date, état du projet, difficultés, recommandations, position GPS, photos, fichiers multiples",
            'is_active' => TRUE
        ],
        'FICHE_SYNOPTIQUE' => [
            'code' => "FICHE_SYNOPTIQUE",
            'name' => "Fiche synoptique du dossier",
            'description' => "Générée pour chaque dossier (fonctionnalité transversale citée dans la section AGR)",
            'is_active' => TRUE
        ],
        'RAPPORT_ANALYSE' => [
            'code' => "RAPPORT_ANALYSE",
            'name' => "Rapport d'analyse du projet",
            'description' => "Récupéré dès l'étape 1 pour les Projets structurants/Start Up",
            'is_active' => TRUE
        ],
        'DOSSIER_CREDIT' => [
            'code' => "DOSSIER_CREDIT",
            'name' => "Dossier de crédit",
            'description' => "Récupéré dès l'étape 1 pour les Projets structurants/Start Up",
            'is_active' => TRUE
        ],
    ];

    // Rattachement des rôles/profils aux étapes de workflow, par code de workflow.
    const WorkflowEtapesRoles = [
        'AGR_CLASSIQUE' => [
            ['etape_code' => "AGRC_PRCO_2", 'role_code' => "CAR", 'action' => "AJOUT_PLAN_AFFAIRES"],
            ['etape_code' => "AGRC_PRCO_3", 'role_code' => "SDRF", 'action' => "TRANSMISSION"],
            ['etape_code' => "AGRC_PRCO_3", 'role_code' => "AGENT_DRF", 'action' => "TRANSMISSION"],
            ['etape_code' => "AGRC_PRCO_4", 'role_code' => "PF", 'action' => "TRAITEMENT"],
            ['etape_code' => "AGRC_PRCO_5_1", 'role_code' => "PF", 'action' => "SAISIE_DECAISSEMENT"],
            ['etape_code' => "AGRC_PRCO_5_2", 'role_code' => "PF", 'action' => "SAISIE_REMBOURSEMENT"],
            ['etape_code' => "AGRC_PRCO_5_2", 'role_code' => "SERVICE_MONITORING", 'action' => "RECOUVREMENT_AMIABLE"],
            ['etape_code' => "AGRC_PRCO_5_2", 'role_code' => "AVOCAT_AEJ", 'action' => "CONTENTIEUX"],
            ['etape_code' => "AGRC_PRCO_6", 'role_code' => "AGENT_AR", 'action' => "VISITE_SUIVI"],
            ['etape_code' => "AGRC_PRCO_6", 'role_code' => "DPF", 'action' => "VISITE_SUIVI"],
        ],
        'MPE' => [
            ['etape_code' => "MPE_PRCO_2", 'role_code' => "SDRF", 'action' => "AJOUT_PLAN_AFFAIRES_TRANSMISSION"],
            ['etape_code' => "MPE_PRCO_2", 'role_code' => "AGENT_DRF", 'action' => "TRANSMISSION"],
            ['etape_code' => "MPE_PRCO_3", 'role_code' => "CSFM", 'action' => "IMPUTATION"],
            ['etape_code' => "MPE_PRCO_4", 'role_code' => "CIP", 'action' => "SAISIE_PLAN_DECAISSEMENT"],
            ['etape_code' => "MPE_PRCO_4", 'role_code' => "AGENT_DIR", 'action' => "SAISIE_PLAN_DECAISSEMENT"],
            ['etape_code' => "MPE_PRCO_4", 'role_code' => "BENEF", 'action' => "VALIDATION_MOBILE"],
            ['etape_code' => "MPE_PRCO_5_1", 'role_code' => "CIP", 'action' => "ENVOI"],
            ['etape_code' => "MPE_PRCO_5_1", 'role_code' => "CAR", 'action' => "VALIDATION"],
            ['etape_code' => "MPE_PRCO_5_2", 'role_code' => "AGENT_DIR", 'action' => "ENVOI"],
            ['etape_code' => "MPE_PRCO_5_2", 'role_code' => "CSFM", 'action' => "RECEPTION"],
            ['etape_code' => "MPE_PRCO_5", 'role_code' => "SDRF", 'action' => "VERIFICATION"],
            ['etape_code' => "MPE_PRCO_5", 'role_code' => "SDEF", 'action' => "VALIDATION"],
            ['etape_code' => "MPE_PRCO_5", 'role_code' => "SDPF", 'action' => "VALIDATION"],
            ['etape_code' => "MPE_PRCO_5", 'role_code' => "DPF", 'action' => "VALIDATION_FINALE"],
            ['etape_code' => "MPE_PRCO_6", 'role_code' => "PF", 'action' => "TRAITEMENT"],
            ['etape_code' => "MPE_PRCO_7_1", 'role_code' => "PF", 'action' => "AUTORISATION"],
            ['etape_code' => "MPE_PRCO_7_1", 'role_code' => "AGENT_AR", 'action' => "EXECUTION"],
            ['etape_code' => "MPE_PRCO_7_2", 'role_code' => "PF", 'action' => "SAISIE_REMBOURSEMENT"],
            ['etape_code' => "MPE_PRCO_7_2", 'role_code' => "CO2CI", 'action' => "RECOUVREMENT"],
            ['etape_code' => "MPE_PRCO_7_2", 'role_code' => "SERVICE_MONITORING", 'action' => "RECOUVREMENT_AMIABLE"],
            ['etape_code' => "MPE_PRCO_7_2", 'role_code' => "AVOCAT_AEJ", 'action' => "CONTENTIEUX"],
            ['etape_code' => "MPE_PRCO_7_2", 'role_code' => "PF", 'action' => "RAPPEL_GARANTIE"],
            ['etape_code' => "MPE_PRCO_8", 'role_code' => "AGENT_AR", 'action' => "VISITE_SUIVI"],
            ['etape_code' => "MPE_PRCO_8", 'role_code' => "DPF", 'action' => "VISITE_SUIVI"],
            ['etape_code' => "MPE_PRCO_6", 'role_code' => "AF", 'action' => "CONSULTATION"],
        ],
        'MEPS' => [
            ['etape_code' => "MEPS_PRCO_2", 'role_code' => "SDRF", 'action' => "AJOUT_PLAN_AFFAIRES_TRANSMISSION"],
            ['etape_code' => "MEPS_PRCO_2", 'role_code' => "AGENT_DRF", 'action' => "TRANSMISSION"],
            ['etape_code' => "MEPS_PRCO_3", 'role_code' => "CSFM", 'action' => "IMPUTATION"],
            ['etape_code' => "MEPS_PRCO_4", 'role_code' => "CIP", 'action' => "SAISIE_PLAN_DECAISSEMENT"],
            ['etape_code' => "MEPS_PRCO_4", 'role_code' => "AGENT_DIR", 'action' => "SAISIE_PLAN_DECAISSEMENT"],
            ['etape_code' => "MEPS_PRCO_4", 'role_code' => "BENEF", 'action' => "VALIDATION_MOBILE"],
            ['etape_code' => "MEPS_PRCO_5_1", 'role_code' => "CIP", 'action' => "ENVOI"],
            ['etape_code' => "MEPS_PRCO_5_1", 'role_code' => "CAR", 'action' => "VALIDATION"],
            ['etape_code' => "MEPS_PRCO_5_2", 'role_code' => "AGENT_DIR", 'action' => "ENVOI"],
            ['etape_code' => "MEPS_PRCO_5_2", 'role_code' => "CSFM", 'action' => "RECEPTION"],
            ['etape_code' => "MEPS_PRCO_5", 'role_code' => "SDRF", 'action' => "VERIFICATION"],
            ['etape_code' => "MEPS_PRCO_5", 'role_code' => "SDEF", 'action' => "VALIDATION"],
            ['etape_code' => "MEPS_PRCO_5", 'role_code' => "SDPF", 'action' => "VALIDATION"],
            ['etape_code' => "MEPS_PRCO_5", 'role_code' => "DPF", 'action' => "VALIDATION_FINALE"],
            ['etape_code' => "MEPS_PRCO_6", 'role_code' => "PF", 'action' => "TRAITEMENT"],
            ['etape_code' => "MEPS_PRCO_7_1", 'role_code' => "PF", 'action' => "AUTORISATION"],
            ['etape_code' => "MEPS_PRCO_7_1", 'role_code' => "AGENT_AR", 'action' => "EXECUTION"],
            ['etape_code' => "MEPS_PRCO_7_2", 'role_code' => "PF", 'action' => "SAISIE_REMBOURSEMENT"],
            ['etape_code' => "MEPS_PRCO_7_2", 'role_code' => "CO2CI", 'action' => "RECOUVREMENT"],
            ['etape_code' => "MEPS_PRCO_7_2", 'role_code' => "SERVICE_MONITORING", 'action' => "RECOUVREMENT_AMIABLE"],
            ['etape_code' => "MEPS_PRCO_7_2", 'role_code' => "AVOCAT_AEJ", 'action' => "CONTENTIEUX"],
            ['etape_code' => "MEPS_PRCO_7_2", 'role_code' => "PF", 'action' => "RAPPEL_GARANTIE"],
            ['etape_code' => "MEPS_PRCO_8", 'role_code' => "AGENT_AR", 'action' => "VISITE_SUIVI"],
            ['etape_code' => "MEPS_PRCO_8", 'role_code' => "DPF", 'action' => "VISITE_SUIVI"],
            ['etape_code' => "MEPS_PRCO_6", 'role_code' => "AF", 'action' => "CONSULTATION"],
        ],
        'STARTUP_BOOST' => [
            ['etape_code' => "START_PRCO_2", 'role_code' => "SDRF", 'action' => "AJOUT_PLAN_AFFAIRES_TRANSMISSION"],
            ['etape_code' => "START_PRCO_3_1", 'role_code' => "CIP", 'action' => "SAISIE_PLAN_DECAISSEMENT"],
            ['etape_code' => "START_PRCO_3_1", 'role_code' => "BENEF", 'action' => "VALIDATION_MOBILE"],
            ['etape_code' => "START_PRCO_3_2", 'role_code' => "CSRGC", 'action' => "AJOUT_CONVENTION"],
            ['etape_code' => "START_PRCO_4", 'role_code' => "CIP", 'action' => "ENVOI"],
            ['etape_code' => "START_PRCO_4", 'role_code' => "CAR", 'action' => "VALIDATION"],
            ['etape_code' => "START_PRCO_4", 'role_code' => "SDRF", 'action' => "VERIFICATION"],
            ['etape_code' => "START_PRCO_4", 'role_code' => "SDEF", 'action' => "VALIDATION"],
            ['etape_code' => "START_PRCO_4", 'role_code' => "SDPF", 'action' => "VALIDATION"],
            ['etape_code' => "START_PRCO_4", 'role_code' => "DPF", 'action' => "VALIDATION_FINALE"],
            ['etape_code' => "START_PRCO_5", 'role_code' => "PF", 'action' => "TRAITEMENT"],
            ['etape_code' => "START_PRCO_5", 'role_code' => "AF", 'action' => "CONSULTATION"],
            ['etape_code' => "START_PRCO_6_1", 'role_code' => "PF", 'action' => "AUTORISATION"],
            ['etape_code' => "START_PRCO_6_1", 'role_code' => "AGENT_AR", 'action' => "EXECUTION"],
            ['etape_code' => "START_PRCO_6_2", 'role_code' => "PF", 'action' => "SAISIE_REMBOURSEMENT"],
            ['etape_code' => "START_PRCO_6_2", 'role_code' => "CO2CI", 'action' => "RECOUVREMENT"],
            ['etape_code' => "START_PRCO_6_2", 'role_code' => "SERVICE_MONITORING", 'action' => "RECOUVREMENT_AMIABLE"],
            ['etape_code' => "START_PRCO_6_2", 'role_code' => "AVOCAT_AEJ", 'action' => "CONTENTIEUX"],
            ['etape_code' => "START_PRCO_6_2", 'role_code' => "PF", 'action' => "RAPPEL_GARANTIE"],
            ['etape_code' => "START_PRCO_7", 'role_code' => "AGENT_AR", 'action' => "VISITE_SUIVI"],
            ['etape_code' => "START_PRCO_7", 'role_code' => "DPF", 'action' => "VISITE_SUIVI"],
        ],
        // AGR_PLUS, CAPITAL_INVEST, MENTORAT, PERMIS : non détaillés dans le document source.
    ];

    // Points de décision par étape (déclenchent une notification + orientation du dossier),
    const WorkflowEtapesDecision = [
        'AGR_CLASSIQUE' => [
            [
                'etape_code' => "AGRC_PRCO_4",
                'code' => "AGRC_DEC_APPROBATION_PARTENAIRE",
                'name' => "Approbation ou non du dossier par le partenaire financier",
                'outcomes' => ["APPROUVE", "REJETE"],
            ],
            [
                'etape_code' => "AGRC_PRCO_5_2",
                'code' => "AGRC_DEC_ESCALADE_CONTENTIEUX",
                'name' => "Passage en contentieux au-delà du 3ème impayé",
                'outcomes' => ["MAINTIEN_AMIABLE", "TRANSMIS_AVOCAT"],
            ],
        ],
        'MPE' => [
            [
                'etape_code' => "MPE_PRCO_5_1",
                'code' => "MPE_DEC_VALIDATION_CHEF_AGENCE",
                'name' => "Validation du plan de décaissement par le chef d'agence",
                'outcomes' => ["VALIDE", "AJOURNE"],
            ],
            [
                'etape_code' => "MPE_PRCO_5",
                'code' => "MPE_DEC_VALIDATION_SOUS_DIR_EVAL",
                'name' => "Validation par le sous-directeur de l'évaluation financière",
                'outcomes' => ["VALIDE", "REJETE"],
            ],
            [
                'etape_code' => "MPE_PRCO_5",
                'code' => "MPE_DEC_VALIDATION_SOUS_DIR_PARTENARIAT",
                'name' => "Validation par le sous-directeur du partenariat et financement",
                'outcomes' => ["VALIDE", "REJETE"],
            ],
            [
                'etape_code' => "MPE_PRCO_5",
                'code' => "MPE_DEC_VALIDATION_DIRECTEUR",
                'name' => "Validation finale par le Directeur du partenariat et financement",
                'outcomes' => ["VALIDE", "REJETE"],
            ],
            [
                'etape_code' => "MPE_PRCO_6",
                'code' => "MPE_DEC_APPROBATION_PARTENAIRE",
                'name' => "Approbation ou non du dossier par le partenaire financier",
                'outcomes' => ["APPROUVE", "REJETE"],
            ],
            [
                'etape_code' => "MPE_PRCO_7_1",
                'code' => "MPE_DEC_AUTORISATION_DECAISSEMENT",
                'name' => "Autorisation ligne par ligne des décaissements par le partenaire financier",
                'outcomes' => ["AUTORISE", "EN_ATTENTE"],
            ],
            [
                'etape_code' => "MPE_PRCO_7_2",
                'code' => "MPE_DEC_ESCALADE_CONTENTIEUX",
                'name' => "Passage en contentieux au-delà du 3ème impayé + rappel de garantie",
                'outcomes' => ["MAINTIEN_AMIABLE", "TRANSMIS_AVOCAT", "RAPPEL_GARANTIE"],
            ],
        ],
        'MEPS' => [
            [
                'etape_code' => "MEPS_PRCO_5_1",
                'code' => "MEPS_DEC_VALIDATION_CHEF_AGENCE",
                'name' => "Validation du plan de décaissement par le chef d'agence",
                'role_code' => "CAR",
                'outcomes' => ["VALIDE", "AJOURNE"],
            ],
            [
                'etape_code' => "MEPS_PRCO_5",
                'code' => "MEPS_DEC_VALIDATION_SOUS_DIR_EVAL",
                'name' => "Validation par le sous-directeur de l'évaluation financière",
                'role_code' => "SDEF",
                'outcomes' => ["VALIDE", "REJETE"],
            ],
            [
                'etape_code' => "MEPS_PRCO_5",
                'code' => "MEPS_DEC_VALIDATION_SOUS_DIR_PARTENARIAT",
                'name' => "Validation par le sous-directeur du partenariat et financement",
                'role_code' => "SDPF",
                'outcomes' => ["VALIDE", "REJETE"],
            ],
            [
                'etape_code' => "MEPS_PRCO_5",
                'code' => "MEPS_DEC_VALIDATION_DIRECTEUR",
                'name' => "Validation finale par le Directeur du partenariat et financement",
                'role_code' => "DPF",
                'outcomes' => ["VALIDE", "REJETE"],
            ],
            [
                'etape_code' => "MEPS_PRCO_6",
                'code' => "MEPS_DEC_APPROBATION_PARTENAIRE",
                'name' => "Approbation ou non du dossier par le partenaire financier",
                'role_code' => "PF",
                'outcomes' => ["APPROUVE", "REJETE"],
            ],
            [
                'etape_code' => "MEPS_PRCO_7_1",
                'code' => "MEPS_DEC_AUTORISATION_DECAISSEMENT",
                'name' => "Autorisation ligne par ligne des décaissements par le partenaire financier",
                'role_code' => "PF",
                'outcomes' => ["AUTORISE", "EN_ATTENTE"],
            ],
            [
                'etape_code' => "MEPS_PRCO_7_2",
                'code' => "MEPS_DEC_ESCALADE_CONTENTIEUX",
                'name' => "Passage en contentieux au-delà du 3ème impayé + rappel de garantie",
                'role_code' => "SERVICE_MONITORING",
                'outcomes' => ["MAINTIEN_AMIABLE", "TRANSMIS_AVOCAT", "RAPPEL_GARANTIE"],
            ],
        ],
        'STARTUP_BOOST' => [
            [
                'etape_code' => "START_PRCO_4",
                'code' => "START_DEC_VALIDATION_CHEF_AGENCE",
                'name' => "Validation du plan de décaissement par le chef d'agence",
                'role_code' => "CAR",
                'outcomes' => ["VALIDE", "AJOURNE"],
            ],
            [
                'etape_code' => "START_PRCO_4",
                'code' => "START_DEC_VALIDATION_SOUS_DIR_EVAL",
                'name' => "Validation par le sous-directeur de l'évaluation financière",
                'role_code' => "SDEF",
                'outcomes' => ["VALIDE", "REJETE"],
            ],
            [
                'etape_code' => "START_PRCO_4",
                'code' => "START_DEC_VALIDATION_SOUS_DIR_PARTENARIAT",
                'name' => "Validation par le sous-directeur du partenariat et financement",
                'role_code' => "SDPF",
                'outcomes' => ["VALIDE", "REJETE"],
            ],
            [
                'etape_code' => "START_PRCO_4",
                'code' => "START_DEC_VALIDATION_DIRECTEUR",
                'name' => "Validation finale par le Directeur du partenariat et financement",
                'role_code' => "DPF",
                'outcomes' => ["VALIDE", "REJETE"],
            ],
            [
                'etape_code' => "START_PRCO_5",
                'code' => "START_DEC_APPROBATION_PARTENAIRE",
                'name' => "Approbation ou non du dossier par le partenaire financier",
                'role_code' => "PF",
                'outcomes' => ["APPROUVE", "REJETE"],
            ],
            [
                'etape_code' => "START_PRCO_6_1",
                'code' => "START_DEC_AUTORISATION_DECAISSEMENT",
                'name' => "Autorisation ligne par ligne des décaissements par le partenaire financier",
                'role_code' => "PF",
                'outcomes' => ["AUTORISE", "EN_ATTENTE"],
            ],
            [
                'etape_code' => "START_PRCO_6_2",
                'code' => "START_DEC_ESCALADE_CONTENTIEUX",
                'name' => "Passage en contentieux au-delà du 3ème impayé + rappel de garantie",
                'role_code' => "SERVICE_MONITORING",
                'outcomes' => ["MAINTIEN_AMIABLE", "TRANSMIS_AVOCAT", "RAPPEL_GARANTIE"],
            ],
        ],
        // Décision transversale citée dans les "Autres fonctionnalités" du document, disponible
        // pour tous les guichets (bouton "annulation du prêt")
        '_GLOBAL' => [
            [
                'etape_code' => "",
                'code' => "GLOBAL_DEC_ANNULATION_PRET",
                'name' => "Annulation du prêt",
                'description' => "Bouton disponible sur l'interface du chef de service développement et réseaux. Arrête la procédure et gèle toute action pour le dossier ; une notification est envoyée à tous les utilisateurs, y compris le partenaire financier, principal intéressé.",
                'role_code' => "SDRF",
                'outcomes' => ["PRET_ANNULE"],
            ],
        ],
    ];

    // Rattachement des livrables aux étapes de workflow, par code de workflow.
    const WorkflowEtapesDeliverable = [
        'AGR_CLASSIQUE' => [
            ['etape_code' => "AGRC_PRCO_2", 'deliverable_code' => "PLAN_AFFAIRES", 'is_required' => TRUE],
            ['etape_code' => "AGRC_PRCO_3", 'deliverable_code' => "FICHIER_REPARTITION_EXCEL", 'is_required' => TRUE],
            ['etape_code' => "AGRC_PRCO_3", 'deliverable_code' => "COURRIER_TRANSMISSION", 'is_required' => TRUE],
            ['etape_code' => "AGRC_PRCO_4", 'deliverable_code' => "TABLEAU_AMORTISSEMENT", 'is_required' => TRUE],
            ['etape_code' => "AGRC_PRCO_4", 'deliverable_code' => "CONTRAT_CONVENTION_PRET", 'is_required' => TRUE],
            ['etape_code' => "AGRC_PRCO_5_1", 'deliverable_code' => "JUSTIFICATIF_DECAISSEMENT", 'is_required' => TRUE],
            ['etape_code' => "AGRC_PRCO_5_2", 'deliverable_code' => "JUSTIFICATIF_REMBOURSEMENT", 'is_required' => TRUE],
            ['etape_code' => "AGRC_PRCO_6", 'deliverable_code' => "RAPPORT_VISITE_SUIVI", 'is_required' => TRUE],
        ],
        'MPE' => [
            ['etape_code' => "MPE_PRCO_2", 'deliverable_code' => "PLAN_AFFAIRES", 'is_required' => TRUE],
            ['etape_code' => "MPE_PRCO_2", 'deliverable_code' => "FICHIER_REPARTITION_EXCEL", 'is_required' => TRUE],
            ['etape_code' => "MPE_PRCO_2", 'deliverable_code' => "COURRIER_TRANSMISSION", 'is_required' => TRUE],
            ['etape_code' => "MPE_PRCO_4", 'deliverable_code' => "PLAN_DECAISSEMENT", 'is_required' => TRUE],
            ['etape_code' => "MPE_PRCO_6", 'deliverable_code' => "TABLEAU_AMORTISSEMENT", 'is_required' => TRUE],
            ['etape_code' => "MPE_PRCO_6", 'deliverable_code' => "CONTRAT_CONVENTION_PRET", 'is_required' => TRUE],
            ['etape_code' => "MPE_PRCO_7_1", 'deliverable_code' => "JUSTIFICATIF_DECAISSEMENT", 'is_required' => TRUE],
            ['etape_code' => "MPE_PRCO_7_2", 'deliverable_code' => "JUSTIFICATIF_REMBOURSEMENT", 'is_required' => TRUE],
            ['etape_code' => "MPE_PRCO_8", 'deliverable_code' => "RAPPORT_VISITE_SUIVI", 'is_required' => TRUE],
        ],
        'MEPS' => [
            ['etape_code' => "MEPS_PRCO_2", 'deliverable_code' => "PLAN_AFFAIRES", 'is_required' => TRUE],
            ['etape_code' => "MEPS_PRCO_2", 'deliverable_code' => "FICHIER_REPARTITION_EXCEL", 'is_required' => TRUE],
            ['etape_code' => "MEPS_PRCO_2", 'deliverable_code' => "COURRIER_TRANSMISSION", 'is_required' => TRUE],
            ['etape_code' => "MEPS_PRCO_4", 'deliverable_code' => "PLAN_DECAISSEMENT", 'is_required' => TRUE],
            ['etape_code' => "MEPS_PRCO_6", 'deliverable_code' => "TABLEAU_AMORTISSEMENT", 'is_required' => TRUE],
            ['etape_code' => "MEPS_PRCO_6", 'deliverable_code' => "CONTRAT_CONVENTION_PRET", 'is_required' => TRUE],
            ['etape_code' => "MEPS_PRCO_7_1", 'deliverable_code' => "JUSTIFICATIF_DECAISSEMENT", 'is_required' => TRUE],
            ['etape_code' => "MEPS_PRCO_7_2", 'deliverable_code' => "JUSTIFICATIF_REMBOURSEMENT", 'is_required' => TRUE],
            ['etape_code' => "MEPS_PRCO_8", 'deliverable_code' => "RAPPORT_VISITE_SUIVI", 'is_required' => TRUE],
        ],
        'STARTUP_BOOST' => [
            ['etape_code' => "START_PRCO_1", 'deliverable_code' => "RAPPORT_ANALYSE", 'is_required' => TRUE],
            ['etape_code' => "START_PRCO_1", 'deliverable_code' => "DOSSIER_CREDIT", 'is_required' => TRUE],
            ['etape_code' => "START_PRCO_2", 'deliverable_code' => "PLAN_AFFAIRES", 'is_required' => TRUE],
            ['etape_code' => "START_PRCO_2", 'deliverable_code' => "COURRIER_TRANSMISSION", 'is_required' => TRUE],
            ['etape_code' => "START_PRCO_3_1", 'deliverable_code' => "PLAN_DECAISSEMENT", 'is_required' => TRUE],
            ['etape_code' => "START_PRCO_3_2", 'deliverable_code' => "CONTRAT_CONVENTION_PRET", 'is_required' => TRUE],
            ['etape_code' => "START_PRCO_5", 'deliverable_code' => "TABLEAU_AMORTISSEMENT", 'is_required' => TRUE],
            ['etape_code' => "START_PRCO_6_1", 'deliverable_code' => "JUSTIFICATIF_DECAISSEMENT", 'is_required' => TRUE],
            ['etape_code' => "START_PRCO_6_2", 'deliverable_code' => "JUSTIFICATIF_REMBOURSEMENT", 'is_required' => TRUE],
            ['etape_code' => "START_PRCO_7", 'deliverable_code' => "RAPPORT_VISITE_SUIVI", 'is_required' => TRUE],
        ],
        // AGR_PLUS, CAPITAL_INVEST, MENTORAT, PERMIS : non détaillés dans le document source.
    ];

    // SLA (Service Level Agreement) - Durées maximales autorisées pour chaque étape
    const WorkflowEtapesSla = [
        'AGR_CLASSIQUE' => [
            ["etape_code" => 'AGRC_PRCO_1', "duration_value" => 2, "duration_unit" => 'JOURS', "description" => 'Récupération de la liste des projets sélectionnés et formés (extraction API ou manuelle).'],
            ["etape_code" => 'AGRC_PRCO_2', "duration_value" => 5, "duration_unit" => 'JOURS', "description" => 'Ajout des plans d’affaires par les agences régionales pour l’ensemble des projets du lot.'],
            ["etape_code" => 'AGRC_PRCO_3', "duration_value" => 2, "duration_unit" => 'JOURS', "description" => 'Constitution du lot Excel et transmission au(x) partenaire(s) financier(s).'],
            ["etape_code" => 'AGRC_PRCO_4', "duration_value" => 15, "duration_unit" => 'JOURS', "description" => 'Analyse du dossier par le partenaire financier (approbation, montant, taux, durée) et ajout des pièces (contrat, tableau d’amortissement).'],
            ["etape_code" => 'AGRC_PRCO_5', "duration_value" => 20, "duration_unit" => 'JOURS', "description" => 'Agrégat indicatif : décaissement (5 j) + traitement d’une échéance de remboursement (3 j) — à ajuster selon le rythme réel des échéances.'],
            ["etape_code" => 'AGRC_PRCO_5_1', "duration_value" => 5, "duration_unit" => 'JOURS', "description" => 'Saisie d’une ligne de décaissement avec justificatifs.'],
            ["etape_code" => 'AGRC_PRCO_5_2', "duration_value" => 3, "duration_unit" => 'JOURS', "description" => 'Délai administratif de traitement d’une échéance de remboursement (hors durée totale du prêt).'],
            ["etape_code" => 'AGRC_PRCO_6', "duration_value" => 30, "duration_unit" => 'JOURS', "description" => 'Délai maximum avant la première visite de suivi terrain après le premier décaissement.'],
        ],
        'AGR_PLUS' => [
            ["etape_code" => 'AGRP_PRCO_5', "duration_value" => 5, "duration_unit" => 'JOURS', "description" => 'Déblocage du financement.'],
            ["etape_code" => 'AGRP_PRCO_6', "duration_value" => 30, "duration_unit" => 'JOURS', "description" => 'Délai maximum avant la première visite de suivi.'],
            ["etape_code" => 'AGRP_PRCO_7', "duration_value" => 3, "duration_unit" => 'JOURS', "description" => 'Délai administratif de traitement d’une échéance de remboursement.'],
            ["etape_code" => 'AGRP_PRCO_8', "duration_value" => 10, "duration_unit" => 'JOURS', "description" => 'Évaluation du dossier / de l’impact.'],
        ],
        'MPE' => [
            ["etape_code" => 'MPE_PRCO_1', "duration_value" => 2, "duration_unit" => 'JOURS', "description" => 'Récupération de la liste des projets sélectionnés et formés, disponible pour tous les profils AEJ.'],
            ["etape_code" => 'MPE_PRCO_2', "duration_value" => 3, "duration_unit" => 'JOURS', "description" => 'Ajout du plan d’affaires et transmission par lot au(x) partenaire(s) financier(s).'],
            ["etape_code" => 'MPE_PRCO_3', "duration_value" => 1, "duration_unit" => 'JOURS', "description" => 'Imputation des dossiers approuvés aux agences régionales selon la localisation.'],
            ["etape_code" => 'MPE_PRCO_4', "duration_value" => 3, "duration_unit" => 'JOURS', "description" => 'Saisie du plan de décaissement par le CIP (ou agent de la direction) et validation mobile du bénéficiaire.'],
            ["etape_code" => 'MPE_PRCO_5', "duration_value" => 10, "duration_unit" => 'JOURS', "description" => 'Agrégat indicatif de la chaîne de validation interne (chef d’agence → chef service DRF → sous-directeurs → Directeur PF).'],
            ["etape_code" => 'MPE_PRCO_5_1', "duration_value" => 10, "duration_unit" => 'JOURS', "description" => 'Cas des agences régionales : validation du CIP au chef d’agence puis chaîne de validation hiérarchique jusqu’au Directeur PF.'],
            ["etape_code" => 'MPE_PRCO_5_2', "duration_value" => 10, "duration_unit" => 'JOURS', "description" => 'Cas de l’agent de la direction : validation par le chef de service financement et monitoring puis chaîne hiérarchique jusqu’au Directeur PF.'],
            ["etape_code" => 'MPE_PRCO_6', "duration_value" => 15, "duration_unit" => 'JOURS', "description" => 'Analyse du dossier par le partenaire financier (approbation, montant, taux, durée) et ajout des pièces.'],
            ["etape_code" => 'MPE_PRCO_7', "duration_value" => 8, "duration_unit" => 'JOURS', "description" => 'Agrégat indicatif : autorisation/exécution du décaissement (5 j) + traitement d’une échéance de remboursement (3 j).'],
            ["etape_code" => 'MPE_PRCO_7_1', "duration_value" => 5, "duration_unit" => 'JOURS', "description" => 'Autorisation de la ligne de décaissement par le partenaire puis exécution par l’agence régionale.'],
            ["etape_code" => 'MPE_PRCO_7_2', "duration_value" => 3, "duration_unit" => 'JOURS', "description" => 'Délai administratif de traitement d’une échéance de remboursement (hors durée totale du prêt).'],
            ["etape_code" => 'MPE_PRCO_8', "duration_value" => 30, "duration_unit" => 'JOURS', "description" => 'Délai maximum avant la première visite de suivi terrain après le premier décaissement.'],
        ],
        'MEPS' => [
            ["etape_code" => 'MEPS_PRCO_1', "duration_value" => 2, "duration_unit" => 'JOURS', "description" => 'Récupération de la liste des projets sélectionnés et formés, disponible pour tous les profils AEJ.'],
            ["etape_code" => 'MEPS_PRCO_2', "duration_value" => 3, "duration_unit" => 'JOURS', "description" => 'Ajout du plan d’affaires et transmission par lot au(x) partenaire(s) financier(s).'],
            ["etape_code" => 'MEPS_PRCO_3', "duration_value" => 1, "duration_unit" => 'JOURS', "description" => 'Imputation des dossiers approuvés aux agences régionales selon la localisation.'],
            ["etape_code" => 'MEPS_PRCO_4', "duration_value" => 3, "duration_unit" => 'JOURS', "description" => 'Saisie du plan de décaissement par le CIP (ou agent de la direction) et validation mobile du bénéficiaire.'],
            ["etape_code" => 'MEPS_PRCO_5', "duration_value" => 10, "duration_unit" => 'JOURS', "description" => 'Agrégat indicatif de la chaîne de validation interne (chef d’agence → chef service DRF → sous-directeurs → Directeur PF).'],
            ["etape_code" => 'MEPS_PRCO_5_1', "duration_value" => 10, "duration_unit" => 'JOURS', "description" => 'Cas des agences régionales : validation du CIP au chef d’agence puis chaîne de validation hiérarchique jusqu’au Directeur PF.'],
            ["etape_code" => 'MEPS_PRCO_5_2', "duration_value" => 10, "duration_unit" => 'JOURS', "description" => 'Cas de l’agent de la direction : validation par le chef de service financement et monitoring puis chaîne hiérarchique jusqu’au Directeur PF.'],
            ["etape_code" => 'MEPS_PRCO_6', "duration_value" => 15, "duration_unit" => 'JOURS', "description" => 'Analyse du dossier par le partenaire financier (approbation, montant, taux, durée) et ajout des pièces.'],
            ["etape_code" => 'MEPS_PRCO_7', "duration_value" => 8, "duration_unit" => 'JOURS', "description" => 'Agrégat indicatif : autorisation/exécution du décaissement (5 j) + traitement d’une échéance de remboursement (3 j).'],
            ["etape_code" => 'MEPS_PRCO_7_1', "duration_value" => 5, "duration_unit" => 'JOURS', "description" => 'Autorisation de la ligne de décaissement par le partenaire puis exécution par l’agence régionale.'],
            ["etape_code" => 'MEPS_PRCO_7_2', "duration_value" => 3, "duration_unit" => 'JOURS', "description" => 'Délai administratif de traitement d’une échéance de remboursement (hors durée totale du prêt).'],
            ["etape_code" => 'MEPS_PRCO_8', "duration_value" => 30, "duration_unit" => 'JOURS', "description" => 'Délai maximum avant la première visite de suivi terrain après le premier décaissement.'],
        ],
        'CAPITAL_INVEST' => [
            ["etape_code" => 'CAPINV_PRCO_5', "duration_value" => 20, "duration_unit" => 'JOURS', "description" => 'Financement des projets (analyse et mise en place, montants généralement plus élevés).'],
            ["etape_code" => 'CAPINV_PRCO_6', "duration_value" => 1, "duration_unit" => 'MOIS', "description" => 'Délai maximum avant la première visite de suivi de l’exploitation.'],
            ["etape_code" => 'CAPINV_PRCO_7', "duration_value" => 3, "duration_unit" => 'JOURS', "description" => 'Délai administratif de traitement d’une échéance de remboursement.'],
        ],
        'MENTORAT' => [
            ["etape_code" => 'MENT_PRCO_6', "duration_value" => 2, "duration_unit" => 'SEMAINES', "description" => 'Mise en œuvre de l’accompagnement (mise en relation mentor/mentoré, cadrage initial).'],
            ["etape_code" => 'MENT_PRCO_7', "duration_value" => 1, "duration_unit" => 'MOIS', "description" => 'Délai maximum avant le premier point de suivi de l’accompagnement.'],
        ],
        'PERMIS' => [
            ["etape_code" => 'PERM_PRCO_1', "duration_value" => 1, "duration_unit" => 'JOURS', "description" => 'Visite médicale.'],
            ["etape_code" => 'PERM_PRCO_2', "duration_value" => 2, "duration_unit" => 'SEMAINES', "description" => 'Formation et suivi décentralisé de la mise en œuvre.'],
        ],
        'STARTUP_BOOST' => [
            ["etape_code" => 'START_PRCO_1', "duration_value" => 2, "duration_unit" => 'JOURS', "description" => 'Récupération de la liste des projets certifiés, disponible pour tous les profils AEJ.'],
            ["etape_code" => 'START_PRCO_2', "duration_value" => 3, "duration_unit" => 'JOURS', "description" => 'Ajout du nouveau plan d’affaires et transmission par lot au(x) partenaire(s) financier(s).',],
            ["etape_code" => 'START_PRCO_3', "duration_value" => 5, "duration_unit" => 'JOURS', "description" => 'Agrégat indicatif : saisie du plan de décaissement (5 j) et ajout de la convention de prêt (3 j) menés en parallèle.'],
            ["etape_code" => 'START_PRCO_3_1', "duration_value" => 5, "duration_unit" => 'JOURS', "description" => 'Saisie du plan de décaissement par le CIP et validation mobile du bénéficiaire.'],
            ["etape_code" => 'START_PRCO_3_2', "duration_value" => 3, "duration_unit" => 'JOURS', "description" => 'Ajout de la convention de prêt signée par le service garantie et contentieux.'],
            ["etape_code" => 'START_PRCO_4', "duration_value" => 10, "duration_unit" => 'JOURS', "description" => 'Chaîne de validation interne (chef d’agence → chef service DRF → sous-directeurs → Directeur PF).'],
            ["etape_code" => 'START_PRCO_5', "duration_value" => 20, "duration_unit" => 'JOURS', "description" => 'Analyse du dossier par le partenaire financier (montants et projets plus importants) et ajout des pièces.'],
            ["etape_code" => 'START_PRCO_6', "duration_value" => 8, "duration_unit" => 'JOURS', "description" => 'Agrégat indicatif : autorisation/exécution du décaissement (5 j) + traitement d’une échéance de remboursement (3 j).'],
            ["etape_code" => 'START_PRCO_6_1', "duration_value" => 5, "duration_unit" => 'JOURS', "description" => 'Autorisation de la ligne de décaissement par le partenaire puis exécution par l’agence régionale.'],
            ["etape_code" => 'START_PRCO_6_2', "duration_value" => 3, "duration_unit" => 'JOURS', "description" => 'Délai administratif de traitement d’une échéance de remboursement (hors durée totale du prêt).'],
            ["etape_code" => 'START_PRCO_7', "duration_value" => 30, "duration_unit" => 'JOURS', "description" => 'Délai maximum avant la première visite de suivi terrain après le premier décaissement.'],
        ],
    ];

    // Méthodes pour créer les données
    public static function getWorkflowRoles(): array
    {
        return self::WorkflowRoles;
    }

    public static function getWorkflowDecisionOutcomes(): array
    {
        return self::WorkflowDecisionOutcome;
    }

    public static function getWorkflowDeliverables(): array
    {
        return self::WorkflowDeliverables;
    }

    public static function getWorkflowModels(): array
    {
        return self::WorkflowModels;
    }

    public static function getWorkflowVersions(): array
    {
        return self::WorkflowVersion;
    }

    public static function getWorkflowEtapes(): array
    {
        return self::WorkflowEtapes;
    }

    public static function getWorkflowEtapesSla(): array
    {
        return self::WorkflowEtapesSla;
    }

    public static function getWorkflowEtapesRoles(): array
    {
        return self::WorkflowEtapesRoles;
    }

    public static function getWorkflowEtapesDecision(): array
    {
        return self::WorkflowEtapesDecision;
    }

    public static function getWorkflowEtapesDeliverable(): array
    {
        return self::WorkflowEtapesDeliverable;
    }
}
