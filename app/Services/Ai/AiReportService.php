<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AiReportService — Interface avec l'API Anthropic (claude-sonnet).
 *
 * Sécurité : ce service ne reçoit JAMAIS les credentials de la base de données.
 * Il reçoit uniquement le schéma textuel et les données agrégées.
 */
class AiReportService
{
    private string $apiKey;
    private string $model;
    private int    $maxTokens;
    private int    $timeoutSeconds;
    private string $baseUrl = 'https://api.anthropic.com/v1/messages';

    public function __construct()
    {
        $this->apiKey         = config('services.anthropic.key', env('ANTHROPIC_API_KEY', ''));
        $this->model          = env('ANTHROPIC_MODEL', 'claude-sonnet-4-5');
        $this->maxTokens      = (int) env('ANTHROPIC_MAX_TOKENS', 4096);
        $this->timeoutSeconds = (int) env('AI_REQUEST_TIMEOUT_SECONDS', 30);
    }

    /**
     * Envoie une question analytique ponctuelle sur un jeune.
     * Retourne la réponse texte de l'IA.
     */
    public function askAnalytical(string $systemPrompt, string $userMessage, array $context = []): string
    {
        $contextStr = empty($context) ? '' : "\n\n**Données contextuelles :**\n" . json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return $this->sendMessage($systemPrompt, $userMessage . $contextStr);
    }

    /**
     * Génère une requête SQL à partir d'une question en langage naturel.
     * Retourne uniquement la requête SQL brute (sans markdown).
     */
    public function generateSql(string $schemaDescription, string $question, string $allowedTables): string
    {
        $systemPrompt = $this->buildSqlGenerationPrompt($schemaDescription, $allowedTables);
        $response     = $this->sendMessage($systemPrompt, "Question : $question");

        // Extraire uniquement le SQL (enlever les blocs markdown ```sql ... ```)
        return $this->extractSqlFromResponse($response);
    }

    /**
     * Génère un commentaire narratif pour un bulletin périodique.
     * Reçoit les KPIs calculés et génère le texte analytique.
     */
    public function generateBulletinNarrative(array $kpis, string $contexte, string $periodeLabel): string
    {
        $systemPrompt = $this->buildBulletinNarrativePrompt();
        $kpisJson     = json_encode($kpis, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $userMessage = <<<MSG
Période analysée : $periodeLabel
Contexte : $contexte

KPIs calculés :
$kpisJson

Génère le commentaire analytique structuré pour ce bulletin.
MSG;

        return $this->sendMessage($systemPrompt, $userMessage);
    }

    /**
     * Génère le commentaire analytique pour un rapport individuel.
     */
    public function generateIndividualComment(array $profileData, array $kpis): string
    {
        $systemPrompt = $this->buildIndividualReportPrompt();
        $dataJson     = json_encode(['profil' => $profileData, 'kpis' => $kpis], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $userMessage = "Génère une analyse complète et structurée pour ce jeune bénéficiaire :\n\n$dataJson";

        return $this->sendMessage($systemPrompt, $userMessage);
    }

    /**
     * Génère l'analyse pour un rapport global.
     */
    public function generateGlobalAnalysis(array $globalKpis, ?string $organismeNom = null): string
    {
        $systemPrompt = $this->buildGlobalReportPrompt();
        $scope        = $organismeNom ? "Organisme : $organismeNom" : "Rapport transversal global (tous organismes)";
        $kpisJson     = json_encode($globalKpis, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $userMessage = "$scope\n\nIndicateurs globaux :\n$kpisJson\n\nGénère l'analyse complète avec tendances, comparaisons et profils à risque.";

        return $this->sendMessage($systemPrompt, $userMessage);
    }

    /**
     * Envoie un message à l'API Anthropic et retourne la réponse textuelle.
     */
    private function sendMessage(string $systemPrompt, string $userMessage): string
    {
        if (empty($this->apiKey) || $this->apiKey === 'your-anthropic-api-key-here') {
            throw new \RuntimeException('Clé API Anthropic non configurée. Veuillez définir ANTHROPIC_API_KEY dans .env');
        }

        try {
            $start    = microtime(true);
            $response = Http::timeout($this->timeoutSeconds)
                ->withHeaders([
                    'x-api-key'         => $this->apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type'      => 'application/json',
                ])
                ->post($this->baseUrl, [
                    'model'      => $this->model,
                    'max_tokens' => $this->maxTokens,
                    'system'     => $systemPrompt,
                    'messages'   => [
                        ['role' => 'user', 'content' => $userMessage],
                    ],
                ]);

            $duration = round((microtime(true) - $start) * 1000);

            if (!$response->successful()) {
                $body = $response->json();
                $err  = $body['error']['message'] ?? $response->body();
                Log::error('AiReportService: erreur API Anthropic', ['status' => $response->status(), 'error' => $err]);
                throw new \RuntimeException("Erreur API Anthropic ({$response->status()}) : $err");
            }

            $data    = $response->json();
            $content = $data['content'][0]['text'] ?? '';

            Log::info('AiReportService: appel réussi', [
                'model'    => $this->model,
                'tokens'   => $data['usage']['output_tokens'] ?? null,
                'duration' => $duration . 'ms',
            ]);

            return $content;
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('AiReportService: timeout ou erreur réseau', ['error' => $e->getMessage()]);
            throw new \RuntimeException("Timeout ou erreur réseau lors de l'appel à l'API Anthropic : " . $e->getMessage());
        }
    }

    /**
     * Extrait le SQL brut d'une réponse IA qui peut contenir des blocs markdown.
     */
    private function extractSqlFromResponse(string $response): string
    {
        // Extraire contenu entre ```sql ... ``` ou ``` ... ```
        if (preg_match('/```(?:sql)?\s*([\s\S]+?)\s*```/i', $response, $matches)) {
            return trim($matches[1]);
        }

        // Si pas de blocs markdown, retourner la réponse nettoyée
        return trim($response);
    }

    // =========================================================================
    // Prompts système
    // =========================================================================

    private function buildSqlGenerationPrompt(string $schemaDescription, string $allowedTables): string
    {
        return <<<PROMPT
Tu es un expert SQL spécialisé dans l'analyse de données de programmes de financement de jeunes entrepreneurs.

RÈGLES ABSOLUES ET NON NÉGOCIABLES :
1. Tu génères UNIQUEMENT des requêtes SELECT. Toute autre opération (INSERT, UPDATE, DELETE, DROP, ALTER, TRUNCATE, CREATE, EXEC, REPLACE) est STRICTEMENT INTERDITE.
2. Tu n'utilises UNIQUEMENT les tables autorisées suivantes : $allowedTables
3. Tu ne sélectionnes JAMAIS les colonnes : mot_de_passe, password, remember_token, token_instance_whatsapp, code_instance_whatsapp, ou tout champ credential/secret.
4. Tu génères une seule requête SQL (pas de point-virgule en milieu de requête).
5. Tu réponds UNIQUEMENT avec le SQL entre balises ```sql ... ```, sans explication.
6. Si la question ne peut pas être répondue avec un SELECT sur les tables autorisées, réponds : "IMPOSSIBLE: [raison]"

SCHÉMA DE LA BASE :
$schemaDescription

Génère maintenant la requête SQL correspondant à la question de l'utilisateur.
PROMPT;
    }

    private function buildIndividualReportPrompt(): string
    {
        return <<<PROMPT
Tu es un analyste financier expert en programmes d'insertion économique pour jeunes entrepreneurs en Côte d'Ivoire.

Tu analyses les données individuelles d'un jeune bénéficiaire de l'AEJ (Agence Emploi Jeunes) et produis une analyse structurée, factuelle et bienveillante.

TON ANALYSE DOIT COUVRIR :
1. **Résumé du profil** : situation du jeune, secteur d'activité, type de projet
2. **Analyse financière** : montant accordé, taux de remboursement, historique de paiement
3. **Évaluation du comportement de paiement** : ponctualité, régularité, incidents
4. **Niveau de risque** : Faible / Modéré / Élevé avec justification factuelle
5. **Points forts** identifiés
6. **Points d'attention** et recommandations

FORMAT : Réponse en français, structurée avec des titres markdown (#, ##), concise et professionnelle.
TON : Analytique, objectif, respectueux — jamais condescendant envers le jeune.
LONGUEUR : 400-600 mots maximum.
PROMPT;
    }

    private function buildGlobalReportPrompt(): string
    {
        return <<<PROMPT
Tu es un analyste de performance spécialisé dans les programmes d'emploi des jeunes en Afrique de l'Ouest.

Tu analyses des données agrégées d'un programme de financement (AEJ - Côte d'Ivoire) pour produire un rapport de performance global et des recommandations stratégiques.

TON ANALYSE DOIT COUVRIR :
1. **Vue d'ensemble** : performance globale de la période
2. **Analyse des remboursements** : taux global, tendances, évolution
3. **Profils à risque** : identification des segments nécessitant une attention particulière
4. **Comparaisons** : par secteur, par région, par organisme si applicable
5. **Points d'excellence** : bons payeurs, secteurs performants
6. **Recommandations stratégiques** : actions prioritaires à engager

FORMAT : Rapport en français avec sections claires, données chiffrées intégrées dans le texte, bullet points pour les recommandations.
LONGUEUR : 500-800 mots.
PROMPT;
    }

    private function buildBulletinNarrativePrompt(): string
    {
        return <<<PROMPT
Tu es chargé de rédiger les commentaires analytiques d'un bulletin périodique de suivi pour l'AEJ (Agence Emploi Jeunes, Côte d'Ivoire).

Tu reçois des KPIs calculés de manière déterministe et tu génères UNIQUEMENT le commentaire narratif qui les accompagne.

STRUCTURE OBLIGATOIRE DE TA RÉPONSE :
## 1. Résumé de la période
[2-3 phrases synthétisant les faits saillants]

## 2. Points positifs
[Liste bullet des indicateurs en progression ou satisfaisants]

## 3. Points d'attention
[Liste bullet des indicateurs préoccupants ou en régression]

## 4. Catégorisation des bénéficiaires
[Commentaire sur la répartition bons payeurs / payeurs en retard / défaillants]

## 5. Recommandations
[3-5 recommandations concrètes et actionnables]

RÈGLES :
- Basé UNIQUEMENT sur les KPIs fournis, sans invention de données
- Ton professionnel et objectif
- En français
- Longueur : 350-500 mots
PROMPT;
    }
}
