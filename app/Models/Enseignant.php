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

    public function filiere()
    {
        return $this->hasOne(Filiere::class,'id_responsable','id_utilisateur');
    }


    public static function boot()
    {
        parent::boot();
        self::deleting(function($enseignant){
            $enseignant->filiere()->delete();
            $enseignant->cours()->each(function($cours){
                $cours->delete();
            });
        });
    }


}
