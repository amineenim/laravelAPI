<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enseignant extends Model
{
    use HasFactory;
    protected $primaryKey ='id_utilisateur';

    protected $fillable = [
        'id_utilisateur',
        'responsabilite_ens',
        'volume_horaire',
    ];

    public $timestamps = false;

    public function cours()
    {
        return $this->hasMany(Cours::class,'id_enseignant','id_utilisateur');
        // hasmany(model::class,foreign key in cours table, refernces key in enseignants table)
    }
}
