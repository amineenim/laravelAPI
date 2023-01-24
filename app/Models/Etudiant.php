<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Etudiant extends Model
{
    use HasFactory;

    protected $table = 'etudiants';
    protected $primaryKey = 'id_utilisateur';
    public $timestamps = false;
    protected $fillable = [
        'id_utilisateur',
        'diplome_etudiant',
        'id_filiere',
    ];

    public function cours()
    {
        return $this->belongsToMany(Cours::class,'cour_etudiant','etudiant_id','cours_id');
    }

    public function notes()
    {
        return $this->hasMany(Note::class,'id_utilisateur','id_utilisateur');
    }

    public function filiere()
    {
        return $this->hasOne(Filiere::class,'id_filiere','id_filiere') ;
    }

    public static function boot()
    {
        parent::boot();
        self::deleting(function($etudiant){
            $etudiant->notes()->each(function($note){
                $note->delete();
            });
        });
    }

   
    
}
