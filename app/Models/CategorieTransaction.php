<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategorieTransaction extends Model
{
    protected $table = 'categories_transactions';

    protected $fillable = [
        'code',
        'libelle',
        'description',
        'niveau',
        'parent_id',
    ];

    protected $casts = [
        'niveau' => 'integer',
    ];

    public function parent()
    {
        return $this->belongsTo(CategorieTransaction::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(CategorieTransaction::class, 'parent_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'categorie_id');
    }
}
