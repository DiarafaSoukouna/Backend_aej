<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class SaveMicroProjetsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 0;
    public int $tries = 3;
    public int $uniqueFor = 7200;

    private const PER_PAGE = 100;
    private const CONCURRENCY = 20;
    private const MAX_ATTEMPTS = 4;

    private string $token = '';
    private string $endpoint = '';
    private string $pagesDir = '';
    private string $dataFile = '';

    public function handle(): void
    {
        $start = microtime(true);

        $dataDir = storage_path('data');
        $this->dataFile = $dataDir . '/projets_data.json';
        $this->pagesDir = $dataDir . '/pages';
        if (!is_dir($this->pagesDir)) {
            mkdir($this->pagesDir, 0775, true);
        }

        $baseUrl = rtrim(config('aej_api.backoffice_url'), '/');
        $this->endpoint = $baseUrl . '/' . ltrim(config('aej_api.backoffice_endpoints.micro_projets'), '/');
        $this->token = $this->authenticate() ?? throw new RuntimeException('Backoffice authentication failed');

        $first = $this->fetchOne(1);
        if ($first === null) {
            throw new RuntimeException('Unable to fetch first page');
        }
        $lastPage = (int) ($first['meta']['last_page'] ?? 1);
        $expected = (int) ($first['meta']['total'] ?? 0);
        $this->writePage(1, $first['data'] ?? []);

        $missing = [];
        for ($p = 2; $p <= $lastPage; $p++) {
            if (!is_file($this->pageFile($p))) {
                $missing[] = $p;
            }
        }

        Log::info('Micro projets fetch started', [
            'total_pages' => $lastPage,
            'expected_records' => $expected,
            'pages_to_fetch' => count($missing),
            'pages_already_done' => $lastPage - 1 - count($missing),
        ]);

        $failed = $this->fetchPages($missing);

        if ($failed) throw new RuntimeException('Pages still failing after retries: ' . implode(',', $failed));

        $total = $this->mergePages($lastPage);

        if ($expected > 0 && $total < $expected) throw new RuntimeException("Data incomplete: {$total}/{$expected} records");

        $this->cleanupPages();

        Log::info('Micro projets fetch completed', [
            'total_records' => $total,
            'seconds' => round(microtime(true) - $start, 2),
        ]);
    }

    public function uniqueId(): string
    {
        return 'save-micro-projets';
    }

    public function failed(Throwable $e): void
    {
        Log::error('SaveMicroProjetsJob failed', ['error' => $e->getMessage()]);
    }

    /* ------------------------------------------------------------------ */
    /*  Authentification                                                  */
    /* ------------------------------------------------------------------ */
    private function authenticate(): ?string
    {
        $baseUrl = rtrim(config('aej_api.backoffice_url'), '/');
        $endpoint = $baseUrl . '/' . ltrim(config('aej_api.backoffice_endpoints.auth_login'), '/');

        try {
            $response = Http::timeout(60)->retry(3, 500)->post($endpoint, [
                'email' => config('aej_api.backoffice_email'),
                'password' => config('aej_api.backoffice_password'),
                'device_name' => config('aej_api.backoffice_device_name'),
            ]);

            Log::info('Backoffice authentication', ['status' => $response->status()]);

            return $response->json('token');
        } catch (Throwable $e) {
            Log::error('Authentication error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Téléchargement                                                    */
    /* ------------------------------------------------------------------ */
    private function fetchOne(int $page): ?array
    {
        $response = Http::timeout(120)->retry(self::MAX_ATTEMPTS, 1000, throw: false)
            ->withToken($this->token)
            ->get($this->endpoint, ['page' => $page, 'per_page' => self::PER_PAGE]);

        if ($response->status() === 401 && ($t = $this->authenticate())) {
            $this->token = $t;
            $response = Http::timeout(120)->withToken($this->token)
                ->get($this->endpoint, ['page' => $page, 'per_page' => self::PER_PAGE]);
        }

        return $response->successful() ? $response->json() : null;
    }

    private function fetchPages(array $pages): array
    {
        if (!$pages) {
            return [];
        }

        $queue = new \SplQueue();
        foreach ($pages as $p) {
            $queue->enqueue(['page' => $p, 'attempt' => 1]);
        }

        $delayed = [];
        $inflight = [];
        $failed = [];
        $done = 0;
        $refreshedToken = false;
        $totalToFetch = count($pages);

        $mh = curl_multi_init();

        while (!$queue->isEmpty() || $inflight || $delayed) {
            $now = microtime(true);
            foreach ($delayed as $k => $item) {
                if ($item['at'] <= $now) {
                    $queue->enqueue($item);
                    unset($delayed[$k]);
                }
            }

            while (count($inflight) < self::CONCURRENCY && !$queue->isEmpty()) {
                $item = $queue->dequeue();
                $ch = $this->makeHandle($item['page']);
                curl_multi_add_handle($mh, $ch);
                $inflight[spl_object_id($ch)] = $item + ['ch' => $ch];
            }

            if (!$inflight) {
                usleep(100000);
                continue;
            }

            do {
                $status = curl_multi_exec($mh, $active);
            } while ($status === CURLM_CALL_MULTI_PERFORM);

            if ($active) {
                curl_multi_select($mh, 0.5);
            }

            while (($info = curl_multi_info_read($mh)) !== false) {
                $ch = $info['handle'];
                $item = $inflight[spl_object_id($ch)];
                unset($inflight[spl_object_id($ch)]);

                $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $body = curl_multi_getcontent($ch);
                $curlOk = $info['result'] === CURLE_OK;

                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);

                $json = ($curlOk && $code === 200) ? json_decode($body, true) : null;

                if (is_array($json) && isset($json['data']) && is_array($json['data'])) {
                    $this->writePage($item['page'], $json['data']);
                    $done++;
                    if ($done % 50 === 0 || $done === $totalToFetch) {
                        Log::info('Fetch progress', ['done' => $done, 'of' => $totalToFetch]);
                    }
                    continue;
                }

                if ($code === 401 && !$refreshedToken && ($t = $this->authenticate())) {
                    $this->token = $t;
                    $refreshedToken = true;
                    $queue->enqueue($item);
                    continue;
                }

                if ($item['attempt'] < self::MAX_ATTEMPTS) {
                    $item['attempt']++;
                    $item['at'] = microtime(true) + min(30, 2 ** $item['attempt']) + mt_rand(0, 500) / 1000;
                    unset($item['ch']);
                    $delayed[] = $item;
                } else {
                    $failed[] = $item['page'];
                    Log::warning('Page failed permanently', [
                        'page' => $item['page'],
                        'http' => $code,
                        'error' => $curlOk ? null : curl_strerror($info['result']),
                    ]);
                }
            }
        }

        curl_multi_close($mh);

        return $failed;
    }

    private function makeHandle(int $page): \CurlHandle
    {
        $ch = curl_init($this->endpoint . '?' . http_build_query(['page' => $page, 'per_page' => self::PER_PAGE]));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Accept: application/json', 'Authorization: Bearer ' . $this->token],
            CURLOPT_TIMEOUT => 120,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_ENCODING => '',                         // gzip/deflate : moins de données transférées
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2TLS, // HTTP/2 si disponible
            CURLOPT_TCP_KEEPALIVE => 1,
        ]);

        return $ch;
    }

    /* ------------------------------------------------------------------ */
    /*  Stockage : 1 fichier par page => reprise fiable, pas de re-lecture  */
    /* ------------------------------------------------------------------ */
    private function pageFile(int $page): string
    {
        return sprintf('%s/page_%06d.json', $this->pagesDir, $page);
    }

    private function writePage(int $page, array $data): void
    {
        $file = $this->pageFile($page);
        $tmp = $file . '.tmp';
        file_put_contents($tmp, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        rename($tmp, $file);
    }

    private function mergePages(int $lastPage): int
    {
        $tmp = $this->dataFile . '.tmp';
        $out = fopen($tmp, 'w');
        fwrite($out, '[');

        $first = true;
        $total = 0;

        for ($p = 1; $p <= $lastPage; $p++) {
            $file = $this->pageFile($p);
            if (!is_file($file)) {
                fclose($out);
                @unlink($tmp);
                throw new RuntimeException("Missing page file {$p} during merge");
            }

            $content = file_get_contents($file);
            $count = substr_count($content, '"id":') > 0 ? count(json_decode($content, true) ?? []) : 0;
            if ($count === 0) {
                continue;
            }

            if (!$first) {
                fwrite($out, ',');
            }
            fwrite($out, substr($content, 1, -1));
            $first = false;
            $total += $count;
        }

        fwrite($out, ']');
        fclose($out);
        rename($tmp, $this->dataFile);

        return $total;
    }

    private function cleanupPages(): void
    {
        foreach (glob($this->pagesDir . '/page_*.json') ?: [] as $f) {
            @unlink($f);
        }
    }
}
