<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use App\Services\AejApiService;

use App\Models\Secteur;
use App\Models\Commune;
use App\Models\MicroProjet;
use App\Models\Promoteur;
use App\Models\Personnel;
use App\Models\Guichet;
use App\Models\Role;
use App\Models\WorkflowInstance;
use App\Models\WorkflowVersion;
use App\Models\WorkflowEtape;

class SyncMicroProjetsJob implements ShouldQueue
{
    protected array $guichetCodeCache = [];
    protected array $ref = [];
    protected ?string $tempPasswordHash = null;
    protected const BATCH_SIZE = 2000;
    protected const INSERT_CHUNK = 500;
    protected const STATUTS = [
        'BROUILLON',
        'EN_SOUMISSION',
        'EN_COURS',
        'EN_ANALYSE',
        'EN_ATTENTE',
        'ANNULE',
        'NON_APPROUVE',
        'APPROUVE',
        'EN_FORMATION',
        'EN_FINANCEMENT',
        'EN_DECAISSEMENT',
        'EN_SUIVI',
        'EN_REMBOURSEMENT',
        'TERMINE',
    ];

    public function handle(): void
    {
        $start = microtime(true);
        DB::disableQueryLog();
        @set_time_limit(0);

        $dataFile = storage_path('data/micro_projets_data.json');
        if (!file_exists($dataFile)) {
            Log::warning('Local micro projets data file not found', ['file' => $dataFile]);
            return;
        }

        $data = json_decode(file_get_contents($dataFile), true);
        if (!is_array($data) || empty($data)) {
            Log::warning('No micro projets data in local file');
            return;
        }

        $total = count($data);
        $this->loadReferences();

        $stats = ['created' => 0, 'skipped' => 0, 'errors' => 0];
        $chunks = array_chunk($data, self::BATCH_SIZE);
        unset($data);

        foreach ($chunks as $i => $batchData) {
            try {
                $batchStats = DB::transaction(fn() => $this->processMicroProjetsPage($batchData));
                $stats['created'] += $batchStats['created'];
                $stats['skipped'] += $batchStats['skipped'];
                $stats['errors']  += $batchStats['errors'];
            } catch (\Throwable $e) {
                $stats['errors'] += count($batchData);
                Log::error('Error processing batch', ['batch' => $i + 1, 'error' => $e->getMessage()]);
            }
            Log::info('Batch done', ['batch' => $i + 1, 'of' => count($chunks)]);
        }

        Log::info('Micro projets synchronization completed', $stats + [
            'total' => $total,
            'seconds' => round(microtime(true) - $start, 2),
        ]);
    }

    private function loadReferences(): void
    {
        $this->ref['role_id'] = Role::where('code', 'BENEF')->value('id');
        $this->ref['guichets'] = Guichet::all()->keyBy('code');
        $this->ref['communes'] = Commune::pluck('id', 'id')->all();
        $this->ref['secteurs'] = Secteur::pluck('id', 'id')->all();

        $workflowVersions = WorkflowVersion::where('is_default', true)->get()->keyBy('workflow_code');
        $this->ref['workflow_versions'] = $workflowVersions;
        $this->ref['etapes'] = WorkflowEtape::whereIn('workflow_version', $workflowVersions->pluck('code'))->get()->keyBy('workflow_version');
        $this->tempPasswordHash = Hash::make('TEMP_PASSWORD_' . now()->format('YmdHis'));
    }

    private function processMicroProjetsPage(array $items): array
    {
        $this->syncPersonnelsFromPromoteurs($items);
        $this->syncPromoteursFromMicroProjets($items);
        $stats = $this->syncMicroProjetsData($items);
        $this->syncWorkflowInstancesForMicroProjets($items);

        return $stats;
    }

    private function promoteurEmail(array $p): string
    {
        $email = $p['email'] ?? null;
        return !empty($email) ? $email : 'benef_' . ($p['numero_aej'] ?? $p['id'] ?? 'unknown') . '@aej.temp';
    }

    private function splitName(?string $full): array
    {
        $parts = explode(' ', $full ?? '');
        return [$parts[0] ?? '', $parts[1] ?? ($parts[0] ?? null)];
    }

    private function insertChunked(string $modelClass, array $rows, bool $ignore = true): void
    {
        foreach (array_chunk($rows, self::INSERT_CHUNK) as $chunk) {
            $ignore ? $modelClass::query()->insertOrIgnore($chunk) : $modelClass::query()->insert($chunk);
        }
    }

    private function syncPersonnelsFromPromoteurs(array $items): void
    {
        $wanted = [];
        foreach ($items as $item) {
            if (!isset($item['promoteur'])) continue;
            $p = $item['promoteur'];
            $email = $this->promoteurEmail($p);
            if (!isset($wanted[$email])) $wanted[$email] = $p;
        }
        if (!$wanted) return;

        $existing = [];
        foreach (array_chunk(array_keys($wanted), 5000) as $emails) {
            foreach (Personnel::whereIn('email', $emails)->pluck('email') as $e) $existing[$e] = true;
        }

        $now = now();
        $rows = [];
        foreach ($wanted as $email => $p) {
            if (isset($existing[$email])) continue;
            $parts = explode(' ', $p['nom_complet'] ?? '');
            $rows[] = [
                'nom'          => $parts[1] ?? 'Promoteur',
                'prenom'       => $parts[0] ?? '',
                'email'        => $email,
                'telephone'    => $p['telephone'] ?? null,
                'mot_de_passe' => $this->tempPasswordHash,
                'is_active'    => true,
                'role_id'      => $this->ref['role_id'],
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }

        $this->insertChunked(Personnel::class, $rows);
    }

    private function syncPromoteursFromMicroProjets(array $items): void
    {
        $emails = [];
        foreach ($items as $item) {
            if (isset($item['promoteur'])) $emails[$this->promoteurEmail($item['promoteur'])] = true;
        }
        if (!$emails) return;

        $personnels = [];
        foreach (array_chunk(array_keys($emails), 5000) as $chunk) {
            $personnels += Personnel::whereIn('email', $chunk)->pluck('id', 'email')->all();
        }

        $now = now();
        $rows = [];
        foreach ($items as $item) {
            if (!isset($item['promoteur']['id'])) continue;
            $p = $item['promoteur'];
            $personnelId = $personnels[$this->promoteurEmail($p)] ?? null;
            if (!$personnelId) continue;

            [$prenom, $nom] = $this->splitName($p['nom_complet'] ?? '');
            $rows[$p['id']] = [
                'id'           => $p['id'],
                'nom'          => $nom,
                'prenom'       => $prenom,
                'email'        => $p['email'] ?? null,
                'telephone'    => $p['telephone'] ?? null,
                'matriculeaej' => $p['numero_aej'] ?? null,
                'personnel_id' => $personnelId,
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }

        foreach (array_chunk(array_values($rows), self::INSERT_CHUNK) as $chunk) {
            Promoteur::query()->upsert(
                $chunk,
                ['id'],
                ['nom', 'prenom', 'email', 'telephone', 'matriculeaej', 'personnel_id', 'updated_at']
            );
        }
    }

    private function syncMicroProjetsData(array $items): array
    {
        $ids = array_column($items, 'id');
        $existing = MicroProjet::whereIn('id', $ids)->pluck('id', 'id')->all();
        $promoteurIds = Promoteur::whereIn('id', array_filter(array_map(fn($i) => $i['promoteur']['id'] ?? null, $items)))
            ->pluck('id', 'id')->all();
        $statuts = array_flip(self::STATUTS);

        $now = now();
        $rows = [];
        $skipped = 0;

        foreach ($items as $item) {
            $id = $item['id'];
            if (isset($existing[$id]) || isset($rows[$id])) {
                $skipped++;
                continue;
            }

            $mapped = isset($item['type_guichet']['code']) ? $this->mapGuichetCode($item['type_guichet']['code']) : null;
            $guichetId = $mapped ? ($this->ref['guichets'][$mapped]->id ?? null) : null;
            if (!$guichetId) {
                Log::debug('No guichet found for micro projet', ['id' => $id, 'mapped_code' => $mapped]);
            }

            $libelle = $item['forme_juridique']['libelle'] ?? null;
            $etape = $item['etape']['code'] ?? null;

            $rows[$id] = [
                'id'           => $id,
                'code'         => $item['code'] ?? 'PRO_' . ($item['matricule'] ?? ''),
                'matricule'    => $item['matricule'] ?? null,
                'intitule'     => $item['intitule'] ?? null,
                'description'  => $item['description'] ?? null,
                'montant_total' => $item['montant_total'] ?? 0,
                'guichet_id'   => $guichetId,
                'promoteur_id' => isset($item['promoteur']['id']) ? ($promoteurIds[$item['promoteur']['id']] ?? null) : null,
                'type_projet'  => $libelle !== null && stripos($libelle, 'INDIVIDUEL') === false ? 'COLLECTIF' : 'INDIVIDUEL',
                'secteur_id'   => isset($item['secteur_activite']['id']) ? ($this->ref['secteurs'][$item['secteur_activite']['id']] ?? null) : null,
                'commune_id'   => isset($item['commune']['id']) ? ($this->ref['communes'][$item['commune']['id']] ?? null) : null,
                'statut'       => ($etape !== null && isset($statuts[$etape])) ? $etape : 'EN_SOUMISSION',
                'synced_at'    => $now,
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }

        $this->insertChunked(MicroProjet::class, array_values($rows));

        return ['created' => count($rows), 'skipped' => $skipped, 'errors' => 0];
    }

    private function syncWorkflowInstancesForMicroProjets(array $items): void
    {
        $ids = array_column($items, 'id');
        $existingProjects = MicroProjet::whereIn('id', $ids)->pluck('id', 'id')->all();
        $existingInstances = WorkflowInstance::whereIn('micro_projet_id', $ids)->pluck('micro_projet_id', 'micro_projet_id')->all();

        $now = now();
        $rows = [];

        foreach ($items as $item) {
            $id = $item['id'];
            if (!isset($existingProjects[$id]) || isset($existingInstances[$id]) || isset($rows[$id])) continue;
            if (!isset($item['type_guichet']['code'])) continue;

            $mapped = $this->mapGuichetCode($item['type_guichet']['code']);
            $guichet = $mapped ? ($this->ref['guichets'][$mapped] ?? null) : null;
            if (!$guichet || !$guichet->workflow_code) continue;

            $version = $this->ref['workflow_versions'][$guichet->workflow_code] ?? null;
            if (!$version) continue;

            $etape = $this->ref['etapes'][$version->code] ?? null;
            if (!$etape) continue;

            $rows[$id] = [
                'micro_projet_id'    => $id,
                'workflow_version'   => $version->code,
                'current_etape_code' => $etape->code,
                'statut'             => 'EN_COURS',
                'started_at'         => $now,
            ];
        }

        $this->insertChunked(WorkflowInstance::class, array_values($rows));
    }

    private function mapGuichetCode(string $apiCode): ?string
    {
        if (array_key_exists($apiCode, $this->guichetCodeCache)) {
            return $this->guichetCodeCache[$apiCode];
        }
        return $this->guichetCodeCache[$apiCode] = $this->resolveGuichetCode($apiCode);
    }

    private function resolveGuichetCode(string $apiCode): ?string
    {
        static $mapping = [
            'GUICHET_2_AGR_CLASSIC' => 'GUICHET_AGR_CLASSIC',
            'GUICHET_2_AGR_METIERS_SPORT' => 'GUICHET_AGR_CLASSIC',
            'GUICHET_2_AGR_METIERS_CULTURE' => 'GUICHET_AGR_CLASSIC',
            'GUICHET_2_AGR_METIERS_NUMERIQUE' => 'GUICHET_AGR_CLASSIC',
            'GUICHET_3_AGR_PLUS' => 'GUICHET_AGR_PLUS',
            'GUICHET_4_MEPS' => 'GUICHET_MEPS',
            'GUICHET_5_MPE' => 'GUICHET_MPE',
            'GUICHET_6_CAPITAL_INVEST' => 'GUICHET_CAPITAL_INVEST',
            'GUICHET_7_MENTORAT' => 'GUICHET_MENTORAT',
            'GUICHET_8_PERMIS' => 'GUICHET_PERMIS',
            'GUICHET_9_STARTUP_BOOST' => 'GUICHET_STARTUP_BOOST',
        ];

        if (isset($mapping[$apiCode])) return $mapping[$apiCode];

        foreach ($mapping as $apiPattern => $dbCode) {
            if (stripos($apiCode, str_replace('GUICHET_', '', $apiPattern)) !== false) return $dbCode;
        }

        $has = fn(string $s) => stripos($apiCode, $s) !== false;

        return match (true) {
            $has('AGR') && $has('PLUS') => 'GUICHET_AGR_PLUS',
            $has('AGR')                 => 'GUICHET_AGR_CLASSIC',
            $has('MEPS')                => 'GUICHET_MEPS',
            $has('MPE')                 => 'GUICHET_MPE',
            $has('CAPITAL') || $has('INVEST') => 'GUICHET_CAPITAL_INVEST',
            $has('MENTORAT')            => 'GUICHET_MENTORAT',
            $has('PERMIS')              => 'GUICHET_PERMIS',
            $has('STARTUP')             => 'GUICHET_STARTUP_BOOST',
            default                     => null,
        };
    }
}
