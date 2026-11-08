<?php

namespace App\Services;

use App\Models\MicroProjet;
use App\Models\Promoteur;
use App\Models\TableauAmortissement;
use App\Models\RappelHistorique;
use App\Models\Personnel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RappelRemboursementService
{
    public const MAX_RAPPELS = 5;

    public function __construct(
        protected MailService $mailService
    ) {}

    /**
     * Détecte si un micro-projet a au moins 3 remboursements consécutifs impayés
     * dont la date d'échéance est dépassée.
     *
     * Retourne les 3 premières échéances consécutives impayées en retard ou null si non éligible.
     */
    public function findThreeConsecutiveOverdueUnpaid(MicroProjet $projet): ?Collection
    {
        // 1. Récupérer le plan de remboursement et les échéances d'amortissement
        $plan = $projet->planRemboursement;
        if (!$plan) {
            return null;
        }

        // On ordonne par période et date d'échéance
        $echeances = TableauAmortissement::where('plan_remboursement_id', $plan->id)
            ->orderBy('periode', 'asc')
            ->orderBy('date_echeance', 'asc')
            ->get();

        if ($echeances->count() < 3) {
            // Vérification de secours si stocké dans la table `remboursements`
            return $this->findInRemboursementsTable($projet);
        }

        $now = Carbon::today();
        $consecutive = collect();

        foreach ($echeances as $echeance) {
            $isPastDue = !empty($echeance->date_echeance) && Carbon::parse($echeance->date_echeance)->lte($now);
            $isUnpaid  = in_array($echeance->statut, ['NON_PAYE', 'PARTIEL']) || (float)($echeance->capital_restant ?? 0) > 0;

            if ($isPastDue && $isUnpaid) {
                // Calculer les jours de retard et le reste à payer
                $dateEch = Carbon::parse($echeance->date_echeance);
                $joursRetard = $dateEch->diffInDays($now);
                $montantDu = (float)($echeance->montant_echeance ?? 0);
                $montantPaye = (float)($echeance->capital_rembourse ?? 0);
                $resteAPayer = max(0, $montantDu - $montantPaye);

                $consecutive->push([
                    'id'               => $echeance->id,
                    'periode'          => $echeance->periode,
                    'date_echeance'    => $echeance->date_echeance ? $echeance->date_echeance->format('Y-m-d') : null,
                    'montant_echeance' => $montantDu,
                    'montant_paye'     => $montantPaye,
                    'reste_a_payer'    => $resteAPayer > 0 ? $resteAPayer : $montantDu,
                    'jours_retard'     => $joursRetard,
                    'statut'           => $echeance->statut,
                ]);

                // Dès qu'on atteint 3 échéances consécutives impayées et échues
                if ($consecutive->count() === 3) {
                    return $consecutive;
                }
            } else {
                // La suite consécutive est brisée car une échéance a été payée ou n'est pas encore échue
                $consecutive = collect();
            }
        }

        // Si le promoteur avait précédemment un compteur de rappels mais a désormais régularisé
        if ($projet->compteur_rappels_impayes > 0 && $consecutive->count() < 3) {
            $projet->update(['compteur_rappels_impayes' => 0]);
        }

        return null;
    }

    /**
     * Recherche de secours dans la table `remboursements` au cas où
     * le tableau d'amortissement n'a pas été généré pour ce projet.
     */
    protected function findInRemboursementsTable(MicroProjet $projet): ?Collection
    {
        $plan = $projet->planRemboursement;
        if (!$plan) {
            return null;
        }

        $remboursements = DB::table('remboursements')
            ->where('plan_remboursement_id', $plan->id)
            ->orderBy('id', 'asc')
            ->get();

        if ($remboursements->count() < 3) {
            return null;
        }

        $now = Carbon::today();
        $consecutive = collect();

        foreach ($remboursements as $index => $r) {
            $dateReference = !empty($r->date_paiement) 
                ? Carbon::parse($r->date_paiement) 
                : (!empty($r->created_at) ? Carbon::parse($r->created_at) : null);

            $isPastDue = $dateReference ? $dateReference->lte($now) : true;
            $isUnpaid  = in_array($r->statut, ['NON_PAYE', 'PARTIEL', 'EN_ATTENTE']);

            if ($isPastDue && $isUnpaid) {
                $joursRetard = $dateReference ? $dateReference->diffInDays($now) : 30;
                $montantDu   = (float)($r->montant_echu ?? 0);
                $montantPaye = (float)($r->montant_paye ?? 0);
                $resteAPayer = (float)($r->montant_impaye ?? max(0, $montantDu - $montantPaye));

                $consecutive->push([
                    'id'               => $r->id,
                    'periode'          => $index + 1,
                    'date_echeance'    => $dateReference ? $dateReference->format('Y-m-d') : null,
                    'montant_echeance' => $montantDu,
                    'montant_paye'     => $montantPaye,
                    'reste_a_payer'    => $resteAPayer > 0 ? $resteAPayer : $montantDu,
                    'jours_retard'     => $joursRetard,
                    'statut'           => $r->statut,
                ]);

                if ($consecutive->count() === 3) {
                    return $consecutive;
                }
            } else {
                $consecutive = collect();
            }
        }

        return null;
    }

    /**
     * Liste tous les projets éligibles à un rappel (3 impayés consécutifs échus).
     */
    public function getEligibleProjects(): Collection
    {
        $projets = MicroProjet::with([
            'promoteur.agenceRegionale',
            'dispositif',
            'organisme',
            'guichet',
            'planRemboursement.tableauAmortissements',
        ])->get();

        $eligible = collect();

        foreach ($projets as $projet) {
            $troisEcheances = $this->findThreeConsecutiveOverdueUnpaid($projet);

            if ($troisEcheances && $troisEcheances->isNotEmpty()) {
                $totalImpaye = $troisEcheances->sum('reste_a_payer');
                $compteur = (int)($projet->compteur_rappels_impayes ?? 0);
                $peutRecevoirRappel = $compteur < self::MAX_RAPPELS;

                // Récupération du dernier historique / événement
                $dernierHistorique = RappelHistorique::where('micro_projet_id', $projet->id)
                    ->orderBy('id', 'desc')
                    ->first();

                $isContentieux = ($dernierHistorique && (
                    strtoupper($dernierHistorique->type) === 'CONTENTIEUX' || 
                    strtoupper($dernierHistorique->statut ?? '') === 'CONTENTIEUX'
                )) || strtoupper($projet->statut ?? '') === 'CONTENTIEUX';

                // Si le dernier historique ou le statut est au contentieux, synchroniser avec la table recouvrements
                if ($isContentieux) {
                    \App\Models\Recouvrement::firstOrCreate(
                        [
                            'micro_projet_id' => $projet->id,
                            'type_action'     => 'CONTENTIEUX',
                        ],
                        [
                            'plan_remboursement_id' => $projet->planRemboursement?->id,
                            'agent_id'              => $dernierHistorique?->personnel_id,
                            'date_recouvrement'     => $dernierHistorique?->date_action ? Carbon::parse($dernierHistorique->date_action)->format('Y-m-d') : Carbon::today()->format('Y-m-d'),
                            'observations'          => $dernierHistorique?->titre ? ($dernierHistorique->titre . ($dernierHistorique->description ? ' — ' . $dernierHistorique->description : '')) : 'Passage au contentieux consigné',
                        ]
                    );
                }

                $eligible->push([
                    'projet_id'            => $projet->id,
                    'projet_code'          => $projet->code ?? $projet->matricule,
                    'projet_intitule'      => $projet->intitule,
                    'promoteur_id'         => $projet->promoteur?->id,
                    'promoteur_nom'        => ($projet->promoteur?->prenom ?? '') . ' ' . ($projet->promoteur?->nom ?? ''),
                    'promoteur_email'      => $projet->promoteur?->email,
                    'promoteur_telephone'  => $projet->promoteur?->telephone,
                    'echeances_concernees' => $troisEcheances,
                    'total_impaye'         => $totalImpaye,
                    'compteur_rappels'     => $compteur,
                    'max_rappels'          => self::MAX_RAPPELS,
                    'peut_recevoir_rappel' => $peutRecevoirRappel,
                    'dernier_rappel_at'    => $projet->dernier_rappel_at ? $projet->dernier_rappel_at->format('Y-m-d H:i:s') : null,
                    'dernier_historique'   => $dernierHistorique ? [
                        'id'          => $dernierHistorique->id,
                        'type'        => $dernierHistorique->type,
                        'statut'      => $dernierHistorique->statut,
                        'titre'       => $dernierHistorique->titre,
                        'description' => $dernierHistorique->description,
                        'date_action' => $dernierHistorique->date_action ? Carbon::parse($dernierHistorique->date_action)->format('Y-m-d H:i:s') : null,
                    ] : null,
                    'is_contentieux'       => $isContentieux,
                ]);
            }
        }

        return $eligible;
    }

    /**
     * Génère le rapport PDF complet et généreux pour le projet et ses 3 échéances.
     *
     * @return string Chemin absolu du fichier PDF généré
     */
    public function generatePdfReport(MicroProjet $projet, array|Collection $echeances, int $numeroRappel): string
    {
        $echeancesArray = $echeances instanceof Collection ? $echeances->toArray() : $echeances;
        $totalImpaye = array_sum(array_column($echeancesArray, 'reste_a_payer'));

        // Synthèse financière du plan
        $plan = $projet->planRemboursement;
        $montantCredit = (float)($plan?->montant_credit ?? $projet->montant_total ?? 0);
        $totalRembourse = (float) TableauAmortissement::where('plan_remboursement_id', $plan?->id ?? 0)
            ->sum('capital_rembourse');
        $resteGlobal = max(0, $montantCredit - $totalRembourse);
        $tauxRemboursement = $montantCredit > 0 ? round(($totalRembourse / $montantCredit) * 100, 2) : 0;

        $synthese = [
            'montant_credit'     => $montantCredit,
            'total_rembourse'    => $totalRembourse,
            'reste_global'       => $resteGlobal,
            'taux_remboursement' => $tauxRemboursement,
        ];

        $promoteurData = $projet->promoteur ? [
            'id'               => $projet->promoteur->id,
            'nom'              => $projet->promoteur->nom,
            'prenom'           => $projet->promoteur->prenom,
            'matriculeaej'     => $projet->promoteur->matriculeaej,
            'numerocni'        => $projet->promoteur->numerocni,
            'numerocmu'        => $projet->promoteur->numerocmu,
            'telephone'        => $projet->promoteur->telephone,
            'email'            => $projet->promoteur->email,
            'agence_regionale' => $projet->promoteur->agenceRegionale ? ['nom' => $projet->promoteur->agenceRegionale->nom] : null,
        ] : [];

        $projetData = [
            'id'            => $projet->id,
            'code'          => $projet->code ?? $projet->matricule,
            'intitule'      => $projet->intitule,
            'montant_total' => $projet->montant_total,
            'statut'        => $projet->statut,
            'organisme'     => $projet->organisme ? ['nom' => $projet->organisme->nom] : null,
            'dispositif'    => $projet->dispositif ? ['nom' => $projet->dispositif->nom] : null,
            'guichet'       => $projet->guichet ? ['nom' => $projet->guichet->nom] : null,
            'agence'        => $projet->agence ? ['nom' => $projet->agence->nom] : null,
        ];

        $viewData = [
            'projet'        => $projetData,
            'promoteur'     => $promoteurData,
            'echeances'     => $echeancesArray,
            'total_impaye'  => $totalImpaye,
            'numero_rappel' => $numeroRappel,
            'max_rappels'   => self::MAX_RAPPELS,
            'synthese'      => $synthese,
        ];

        // Rendu PDF avec DomPDF
        $pdf = Pdf::loadView('reports.rappel-impaye', $viewData)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'defaultFont'          => 'DejaVu Sans',
                'dpi'                  => 150,
            ]);

        $folder = 'reports/rappels';
        if (!Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->makeDirectory($folder);
        }

        $codeClean = preg_replace('/[^A-Za-z0-9_\-]/', '_', $projet->code ?? $projet->id);
        $filename = sprintf(
            '%s/rappel_%s_n%d_%s.pdf',
            $folder,
            $codeClean,
            $numeroRappel,
            now()->format('Ymd_His')
        );

        Storage::disk('public')->put($filename, $pdf->output());

        return Storage::disk('public')->path($filename);
    }

    /**
     * Envoie le rappel officiel au promoteur (Email + PDF joint + Enregistrement Historique).
     *
     * @param MicroProjet $projet
     * @param Personnel|null $user Utilisateur initiateur (null pour cron automatique)
     * @param bool $force Forcer l'envoi même si le quota de 5 est dépassé ou déjà envoyé cette semaine
     */
    public function sendReminder(MicroProjet $projet, ?Personnel $user = null, bool $force = false): array
    {
        $troisEcheances = $this->findThreeConsecutiveOverdueUnpaid($projet);

        if (!$troisEcheances || $troisEcheances->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Ce projet ne présente pas 3 échéances consécutives impayées et échues.',
            ];
        }

        $compteurActuel = (int)($projet->compteur_rappels_impayes ?? 0);

        // Règle des 5 rappels maximum
        if ($compteurActuel >= self::MAX_RAPPELS && !$force) {
            return [
                'success' => false,
                'message' => sprintf(
                    'Le quota maximal de %d rappels a déjà été atteint pour ce projet. Le dossier doit être transmis au contentieux.',
                    self::MAX_RAPPELS
                ),
                'compteur' => $compteurActuel,
            ];
        }

        // Règle de l'envoi hebdomadaire (vérifie que 6 jours se sont écoulés sauf si forcé)
        if ($projet->dernier_rappel_at && !$force) {
            $joursDepuisDernier = Carbon::parse($projet->dernier_rappel_at)->diffInDays(now());
            if ($joursDepuisDernier < 6) {
                return [
                    'success' => false,
                    'message' => sprintf(
                        'Un rappel a déjà été envoyé il y a %d jour(s). Le prochain envoi aura lieu au début de la semaine prochaine.',
                        $joursDepuisDernier
                    ),
                    'jours_restants' => 6 - $joursDepuisDernier,
                ];
            }
        }

        $nouveauNumeroRappel = $compteurActuel + 1;
        $promoteur = $projet->promoteur;

        if (!$promoteur || empty($promoteur->email)) {
            // Enregistrement d'un échec d'envoi dans l'historique
            RappelHistorique::create([
                'micro_projet_id'      => $projet->id,
                'promoteur_id'         => $promoteur?->id,
                'personnel_id'         => $user?->id,
                'type'                 => 'RAPPEL_AUTOMATIQUE',
                'canal'                => 'EMAIL',
                'numero_rappel'        => $nouveauNumeroRappel,
                'statut'               => 'ECHOUE',
                'titre'                => "Échec envoi rappel #{$nouveauNumeroRappel} - Email manquant",
                'description'          => "Impossible d'envoyer l'email : aucune adresse email renseignée pour le promoteur.",
                'date_action'          => now(),
                'montant_concerne'     => $troisEcheances->sum('reste_a_payer'),
                'echeances_concernees' => $troisEcheances->toArray(),
            ]);

            return [
                'success' => false,
                'message' => 'Le promoteur ne possède pas d\'adresse email valide.',
            ];
        }

        $totalImpaye = $troisEcheances->sum('reste_a_payer');

        // 1. Génération du PDF
        $pdfAbsolutePath = null;
        $pdfRelativePath = null;
        try {
            $pdfAbsolutePath = $this->generatePdfReport($projet, $troisEcheances, $nouveauNumeroRappel);
            $pdfRelativePath = str_replace(Storage::disk('public')->path(''), '', $pdfAbsolutePath);
            $pdfRelativePath = ltrim($pdfRelativePath, '/');
        } catch (\Throwable $e) {
            Log::error("Erreur lors de la génération du PDF de rappel : " . $e->getMessage(), [
                'projet_id' => $projet->id,
                'trace'     => $e->getTraceAsString(),
            ]);
        }

        // 2. Préparation des données pour l'email
        $emailData = [
            'promoteur'     => [
                'nom'       => $promoteur->nom,
                'prenom'    => $promoteur->prenom,
                'matricule' => $promoteur->matriculeaej,
                'telephone' => $promoteur->telephone,
                'email'     => $promoteur->email,
            ],
            'projet'        => [
                'intitule'      => $projet->intitule,
                'code'          => $projet->code ?? $projet->matricule,
                'montant_total' => $projet->montant_total,
                'organisme'     => $projet->organisme?->nom,
            ],
            'echeances'     => $troisEcheances->toArray(),
            'total_impaye'  => $totalImpaye,
            'numero_rappel' => $nouveauNumeroRappel,
            'max_rappels'   => self::MAX_RAPPELS,
            'date_rappel'   => now(),
        ];

        // 3. Envoi effectif de l'email avec PDF joint
        $emailSent = false;
        $emailError = null;

        try {
            $this->mailService->sendRappelRemboursementEmail(
                $promoteur->email,
                $emailData,
                $pdfAbsolutePath
            );
            $emailSent = true;
        } catch (\Throwable $e) {
            $emailError = $e->getMessage();
            Log::error("Erreur d'envoi d'email de rappel : " . $e->getMessage(), [
                'projet_id' => $projet->id,
                'email'     => $promoteur->email,
            ]);
        }

        // 4. Enregistrement automatique dans l'historique des rappels
        $historique = RappelHistorique::create([
            'micro_projet_id'      => $projet->id,
            'promoteur_id'         => $promoteur->id,
            'personnel_id'         => $user?->id,
            'type'                 => 'RAPPEL_AUTOMATIQUE',
            'canal'                => 'EMAIL',
            'numero_rappel'        => $nouveauNumeroRappel,
            'statut'               => $emailSent ? 'ENVOYE' : 'ECHOUE',
            'titre'                => "Rappel officiel #{$nouveauNumeroRappel}/5 envoyé au promoteur",
            'description'          => $emailSent
                ? sprintf(
                    "Email officiel de rappel avec rapport PDF joint envoyé à %s pour 3 échéances consécutives en souffrance (Total impayé : %s FCFA).",
                    $promoteur->email,
                    number_format($totalImpaye, 0, ',', ' ')
                )
                : "Échec de transmission de l'email : " . $emailError,
            'date_action'          => now(),
            'montant_concerne'     => $totalImpaye,
            'echeances_concernees' => $troisEcheances->toArray(),
            'destinataire_email'   => $promoteur->email,
            'fichier_joint'        => $pdfRelativePath,
            'metadata'             => [
                'declencheur'       => $user ? 'MANUEL_UTILISATEUR' : 'CRON_HEBDOMADAIRE',
                'agent_nom'         => $user ? ($user->nom . ' ' . $user->prenom) : 'Système Automatique',
                'echeance_periodes' => $troisEcheances->pluck('periode')->toArray(),
                'jours_retard_max'  => $troisEcheances->max('jours_retard'),
            ],
        ]);

        // 5. Mise à jour du projet (compteur et date du dernier rappel)
        if ($emailSent || $force) {
            $projet->update([
                'compteur_rappels_impayes' => $nouveauNumeroRappel,
                'dernier_rappel_at'        => now(),
            ]);
        }

        return [
            'success'               => $emailSent,
            'message'               => $emailSent
                ? sprintf("Rappel n°%d/%d envoyé avec succès au promoteur avec le rapport PDF joint.", $nouveauNumeroRappel, self::MAX_RAPPELS)
                : "Le rapport a été généré mais l'envoi de l'email a échoué : " . $emailError,
            'numero_rappel'         => $nouveauNumeroRappel,
            'max_rappels'           => self::MAX_RAPPELS,
            'pdf_path'              => $pdfRelativePath,
            'pdf_url'               => $pdfRelativePath ? url('storage/' . $pdfRelativePath) : null,
            'rappel_historique_id'  => $historique->id,
            'trois_echeances'       => $troisEcheances,
            'total_impaye'          => $totalImpaye,
        ];
    }

    /**
     * Tâche planifiée hebdomadaire (début de chaque semaine / cron).
     * Parcourt tous les projets, filtre ceux ayant 3 impayés consécutifs échus,
     * et envoie le rappel s'ils n'ont pas encore atteint le 5ème rappel.
     */
    public function processWeeklyReminders(bool $dryRun = false): array
    {
        Log::info("Début du traitement hebdomadaire des rappels de remboursement...");

        $projetsEligibles = $this->getEligibleProjects();
        $stats = [
            'total_eligibles'  => $projetsEligibles->count(),
            'envoyes'          => 0,
            'bloques_max'      => 0,
            'deja_envoyes_sem' => 0,
            'echoues'          => 0,
            'details'          => [],
        ];

        foreach ($projetsEligibles as $eligibleItem) {
            $projet = MicroProjet::find($eligibleItem['projet_id']);
            if (!$projet) {
                continue;
            }

            if ($projet->compteur_rappels_impayes >= self::MAX_RAPPELS) {
                $stats['bloques_max']++;
                $stats['details'][] = [
                    'projet_id' => $projet->id,
                    'statut'    => 'STOP_5_RAPPELS_ATTEINTS',
                    'message'   => 'Arrêt : 5 rappels déjà envoyés. Transféré au contentieux.',
                ];
                continue;
            }

            // Vérifier si déjà envoyé cette semaine
            if ($projet->dernier_rappel_at && Carbon::parse($projet->dernier_rappel_at)->diffInDays(now()) < 6) {
                $stats['deja_envoyes_sem']++;
                $stats['details'][] = [
                    'projet_id' => $projet->id,
                    'statut'    => 'DEJA_ENVOYE_CETTE_SEMAINE',
                    'message'   => 'Rappel déjà transmis cette semaine.',
                ];
                continue;
            }

            if ($dryRun) {
                $stats['envoyes']++;
                $stats['details'][] = [
                    'projet_id'      => $projet->id,
                    'statut'         => 'DRY_RUN_SIMULATION',
                    'numero_suivant' => ($projet->compteur_rappels_impayes ?? 0) + 1,
                ];
                continue;
            }

            $result = $this->sendReminder($projet, null, false);
            if ($result['success']) {
                $stats['envoyes']++;
            } else {
                $stats['echoues']++;
            }

            $stats['details'][] = [
                'projet_id' => $projet->id,
                'result'    => $result,
            ];
        }

        Log::info("Fin du traitement hebdomadaire des rappels", $stats);

        return $stats;
    }
}

