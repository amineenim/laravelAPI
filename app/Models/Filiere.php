<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Filiere extends Model
{
    use HasFactory;
    protected $primaryKey = "id_filiere";
    protected $fillable = [
        'nom_filiere',
        'description',
        'niveau',
        'nombre_annee',
        'id_responsable'
    ];
    public $timestamps =false;


    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class,'id_responsable','id_utilisateur');
    }

    public function etudiants()
    {
        return $this->hasMany(Etudiant::class,'id_filiere','id_filiere');
    }



    public static function boot()
    {
        parent::boot();
        self::deleting(function($filiere){
            $filiere->etudiants()->each(function($etudiant){
                $etudiant->delete();
            });
        });
    }

}
