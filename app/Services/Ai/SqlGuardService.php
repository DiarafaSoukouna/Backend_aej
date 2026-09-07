<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SqlGuardService — Validation et exécution sécurisée du SQL généré par l'IA.
 *
 * Contraintes de sécurité :
 * - SELECT uniquement (blocage complet de DDL/DML)
 * - Whitelist stricte de tables autorisées
 * - Blocage des colonnes sensibles
 * - Timeout d'exécution configurable
 * - Blocage des injections multi-instructions
 */
class SqlGuardService
{
    /**
     * Tables autorisées pour les requêtes IA.
     * Toute table absente de cette liste est REFUSÉE.
     */
    private const ALLOWED_TABLES = [
        'promoteurs',
        'micro_projets',
        'budgets',
        'plan_remboursements',
        'remboursements',
        'remboursements_declarations',
        'decaissements',
        'decaissements_declarations',
        'plan_decaissements',
        'ligne_decaissements',
        'recouvrements',
        'organisme_financements',
        'indicateurs',
        'indicateurs_suivi',
        'exploitations',
        'secteurs',
        'dispositifs',
        'guichets',
        'regions',
        'departements',
        'communes',
        'agences_regionales',
        'transactions_financieres',
        'categories_transactions',
    ];

    /**
     * Colonnes explicitement bloquées (données sensibles/credentials).
     */
    private const BLOCKED_COLUMNS = [
        'mot_de_passe',
        'password',
        'remember_token',
        'token_instance_whatsapp',
        'code_instance_whatsapp',
        'mot_de_passe_email_notifications',
        'smtp_password',
        'api_key',
        'secret',
        'token',
    ];

    /**
     * Patterns SQL dangereux — bloqués par regex insensible à la casse.
     */
    private const BLOCKED_PATTERNS = [
        '/\bINSERT\b/i',
        '/\bUPDATE\b/i',
        '/\bDELETE\b/i',
        '/\bDROP\b/i',
        '/\bALTER\b/i',
        '/\bTRUNCATE\b/i',
        '/\bCREATE\b/i',
        '/\bREPLACE\b/i',
        '/\bMERGE\b/i',
        '/\bCALL\b/i',
        '/\bEXEC(?:UTE)?\b/i',
        '/\bGRANT\b/i',
        '/\bREVOKE\b/i',
        '/\bLOAD\s+DATA\b/i',
        '/\bOUTFILE\b/i',
        '/\bINFILE\b/i',
        '/--/',                  // commentaire SQL (injection)
        '/\/\*.*\*\//s',        // commentaires bloc
        '/;\s*\w+/s',           // multi-instructions
        '/0x[0-9a-fA-F]+/',     // hex encoding (bypass)
        '/INFORMATION_SCHEMA/i',
        '/mysql\./i',
        '/performance_schema/i',
        '/sys\./i',
    ];

    /**
     * Valide un SQL généré par l'IA sans l'exécuter.
     *
     * @return array{valid: bool, errors: string[], sql: string}
     */
    public function validate(string $sql): array
    {
        $sql    = trim($sql);
        $errors = [];

        // 1. Vérifier que la requête commence par SELECT
        if (!preg_match('/^\s*SELECT\b/i', $sql)) {
            $errors[] = 'La requête doit commencer par SELECT.';
        }

        // 2. Vérifier les patterns bloqués
        foreach (self::BLOCKED_PATTERNS as $pattern) {
            if (preg_match($pattern, $sql)) {
                $errors[] = "Opération interdite détectée (pattern: $pattern).";
            }
        }

        // 3. Vérifier les colonnes sensibles
        foreach (self::BLOCKED_COLUMNS as $column) {
            if (stripos($sql, $column) !== false) {
                $errors[] = "Accès à la colonne sensible '$column' interdit.";
            }
        }

        // 4. Vérifier que seules les tables whitelistées sont utilisées
        $tablesInSql = $this->extractTableNames($sql);
        foreach ($tablesInSql as $table) {
            if (!in_array(strtolower($table), self::ALLOWED_TABLES)) {
                $errors[] = "Table '$table' non autorisée.";
            }
        }

        // 5. Longueur maximale (protection contre abus)
        if (strlen($sql) > 5000) {
            $errors[] = 'La requête SQL dépasse la taille maximale autorisée (5000 caractères).';
        }

        return [
            'valid'  => empty($errors),
            'errors' => $errors,
            'sql'    => $sql,
        ];
    }

    /**
     * Valide et exécute le SQL si valide.
     * Applique un timeout de AI_SQL_TIMEOUT_SECONDS secondes.
     *
     * @return array{valid: bool, errors: string[], results: array, row_count: int}
     */
    public function executeIfValid(string $sql): array
    {
        $validation = $this->validate($sql);

        if (!$validation['valid']) {
            Log::warning('SqlGuardService: SQL rejeté', [
                'sql'    => $sql,
                'errors' => $validation['errors'],
            ]);

            return [
                'valid'     => false,
                'errors'    => $validation['errors'],
                'results'   => [],
                'row_count' => 0,
            ];
        }

        try {
            $timeout = (int) env('AI_SQL_TIMEOUT_SECONDS', 5);

            // Forcer un timeout sur la session avant exécution
            // MariaDB utilise max_statement_time (en secondes), MySQL utilise MAX_EXECUTION_TIME (en ms)
            try {
                DB::statement("SET SESSION max_statement_time = " . $timeout);
            } catch (\Exception) {
                // Fallback MySQL si max_statement_time n'est pas disponible
                try {
                    DB::statement("SET SESSION MAX_EXECUTION_TIME = " . ($timeout * 1000));
                } catch (\Exception) {
                    // Ignore si aucun timeout n'est supporté
                }
            }

            $results = DB::select($sql);

            return [
                'valid'     => true,
                'errors'    => [],
                'results'   => array_map(fn($r) => (array) $r, $results),
                'row_count' => count($results),
            ];
        } catch (\Exception $e) {
            Log::error('SqlGuardService: erreur d\'exécution SQL', [
                'sql'   => $sql,
                'error' => $e->getMessage(),
            ]);

            return [
                'valid'     => false,
                'errors'    => ['Erreur d\'exécution SQL : ' . $e->getMessage()],
                'results'   => [],
                'row_count' => 0,
            ];
        }
    }

    /**
     * Retourne la liste des tables autorisées (pour injection dans les prompts IA).
     */
    public function getAllowedTablesDescription(): string
    {
        return implode(', ', self::ALLOWED_TABLES);
    }

    /**
     * Extrait les noms de tables présents dans un SQL via regex basique.
     * Cible les clauses FROM, JOIN.
     */
    private function extractTableNames(string $sql): array
    {
        $tables = [];

        // FROM table_name [alias] ou JOIN table_name [alias]
        preg_match_all(
            '/(?:FROM|JOIN)\s+([`"]?[\w]+[`"]?)(?:\s+(?:AS\s+)?[`"]?[\w]+[`"]?)?/i',
            $sql,
            $matches
        );

        if (!empty($matches[1])) {
            foreach ($matches[1] as $table) {
                $tables[] = strtolower(trim($table, '`"'));
            }
        }

        return array_unique($tables);
    }
}
