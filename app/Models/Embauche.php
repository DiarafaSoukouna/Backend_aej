<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Embauche extends Model
{
    use HasFactory;

    protected $table = 'embauches';

    protected $fillable = [
        'promoteur_id',
        'entreprise_id',
        'micro_projet_id',
        'type_emploi_id',
        'poste',
    ];

    public function promoteur()
    {
        return $this->belongsTo(Promoteur::class, 'promoteur_id');
    }

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_id');
    }

    public function microProjet()
    {
        return $this->belongsTo(MicroProjet::class, 'micro_projet_id');
    }

    public function typeEmploi()
    {
        return $this->belongsTo(TypeEmploi::class, 'type_emploi_id');
    }
}
