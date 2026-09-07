<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiReportLog extends Model
{
    protected $table = 'ai_report_logs';

    protected $fillable = [
        'personnel_id',
        'type_rapport',
        'sujet_id',
        'sujet_type',
        'question',
        'sql_genere',
        'sql_valide',
        'erreurs_validation',
        'reponse_ia',
        'pdf_path',
        'duree_ms',
        'modele_ia',
        'tokens_utilises',
        'statut',
        'erreur_message',
    ];

    protected $casts = [
        'erreurs_validation' => 'array',
        'sql_valide'         => 'boolean',
        'genere_auto'        => 'boolean',
    ];

    public function personnel()
    {
        return $this->belongsTo(Personnel::class, 'personnel_id');
    }

    public static function logSuccess(
        string $typeRapport,
        ?int $personnelId,
        array $data = []
    ): self {
        return self::create(array_merge([
            'personnel_id' => $personnelId,
            'type_rapport' => $typeRapport,
            'statut'       => 'success',
            'modele_ia'    => env('ANTHROPIC_MODEL', 'claude-sonnet-4-5'),
        ], $data));
    }

    public static function logError(
        string $typeRapport,
        ?int $personnelId,
        string $message,
        array $data = []
    ): self {
        return self::create(array_merge([
            'personnel_id'  => $personnelId,
            'type_rapport'  => $typeRapport,
            'statut'        => 'error',
            'erreur_message' => $message,
            'modele_ia'     => env('ANTHROPIC_MODEL', 'claude-sonnet-4-5'),
        ], $data));
    }
}
