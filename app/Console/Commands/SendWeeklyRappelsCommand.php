<?php

namespace App\Console\Commands;

use App\Services\RappelRemboursementService;
use Illuminate\Console\Command;

class SendWeeklyRappelsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rappels:envoyer-hebdomadaire 
                            {--dry-run : Exécuter en mode simulation sans envoyer d\'emails ni incrémenter les compteurs}
                            {--force : Forcer l\'envoi même si le délai hebdomadaire n\'est pas encore écoulé}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Détecte les micro-projets ayant au moins 3 remboursements consécutifs impayés et envoie le rappel hebdomadaire (max 5) avec rapport PDF';

    /**
     * Execute the console command.
     */
    public function handle(RappelRemboursementService $rappelService): int
    {
        $dryRun = $this->option('dry-run');
        $force  = $this->option('force');

        $this->info('========================================================================');
        $this->info('        AEJ - TRAITEMENT HEBDOMADAIRE DES RAPPELS DE REMBOURSEMENT       ');
        $this->info('========================================================================');
        if ($dryRun) {
            $this->warn('Mode SIMULATION activé (--dry-run). Aucun email ne sera expédié.');
        }

        $this->line('1. Recherche des micro-projets éligibles (3 échéances consécutives impayées)...');
        $eligibles = $rappelService->getEligibleProjects();

        $this->info(sprintf('Projets éligibles détectés : %d', $eligibles->count()));

        if ($eligibles->isEmpty()) {
            $this->info('Aucun projet en situation de défaut (3 impayés consécutifs) à traiter.');
            return Command::SUCCESS;
        }

        $rows = [];
        foreach ($eligibles as $item) {
            $rows[] = [
                $item['projet_code'] ?? 'N/A',
                $item['promoteur_nom'] ?? 'N/A',
                $item['promoteur_email'] ?? 'MANQUANT',
                number_format((float)$item['total_impaye'], 0, ',', ' ') . ' FCFA',
                sprintf('%d / %d', $item['compteur_rappels'], $item['max_rappels']),
                $item['peut_recevoir_rappel'] ? 'Éligible' : 'Quota 5 atteint (Contentieux)',
            ];
        }

        $this->table(
            ['Code Projet', 'Promoteur', 'Email', 'Total Impayé (3 Éch.)', 'Compteur', 'Statut'],
            $rows
        );

        $this->line('2. Lancement des envois...');
        $stats = $rappelService->processWeeklyReminders($dryRun);

        $this->newLine();
        $this->info('============================ RÉCAPITULATIF ============================');
        $this->line(sprintf('• Total dossiers éligibles analysés : %d', $stats['total_eligibles']));
        $this->line(sprintf('• Rappels envoyés avec succès      : %d', $stats['envoyes']));
        $this->line(sprintf('• Dossiers bloqués (5 rappels max) : %d', $stats['bloques_max']));
        $this->line(sprintf('• Déjà envoyés cette semaine (<6j) : %d', $stats['deja_envoyes_sem']));
        $this->line(sprintf('• Échecs d\'envoi                  : %d', $stats['echoues']));
        $this->info('========================================================================');

        return Command::SUCCESS;
    }
}

