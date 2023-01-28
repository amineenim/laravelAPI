<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    public function etudiantscours()
    {
        return $this->hasMany(EtudiantCours::class,'etudiant_id','id_utilisateur');
    }
    public function utilisateur()
    {
        return $this->hasOne(utilisateur::class,'id_utilisateur','id_utilisateur');
    }

    public static function boot()
    {
        parent::boot();
        self::deleting(function($etudiant){
            DB::table('cour_etudiant')->where('etudiant_id',$etudiant->id_utilisateur)->delete();
            $etudiant->notes()->each(function($note){
                $note->delete();
            });

        });
    }

   
    
}
