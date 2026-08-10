<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Recouvrement extends Model
{
    use HasFactory;

    protected $fillable = [
        'micro_projet_id',
        'plan_remboursement_id',
        'agent_id',
        'montant_recouvre',
        'date_recouvrement',
        'type_action',
        'observations',
        'justificatif_path',
    ];

    protected $casts = [
        'montant_recouvre' => 'decimal:2',
        'date_recouvrement' => 'date',
    ];

    public function microProjet()
    {
        return $this->belongsTo(MicroProjet::class, 'micro_projet_id');
    }

    public function planRemboursement()
    {
        return $this->belongsTo(PlanRemboursement::class, 'plan_remboursement_id');
    }

    public function agent()
    {
        return $this->belongsTo(Personnel::class, 'agent_id');
    }
}
