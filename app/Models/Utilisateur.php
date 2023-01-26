<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Utilisateur extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_utilisateur';
    public $timestamps =false;

    protected $fillable = [
        'role',
        'nom',
        'prenom',
        'email',
        'password',
        'tel',
    ];
    public function etudiant()
    {
        return $this->hasOne(Etudiant::class,'id_utilisateur','id_utilisateur');
    }
    public function enseignant()
    {
        return $this->hasOne(Enseignant::class,'id_utilisateur','id_utilisateur');
    }

    public static function boot()
    {
        parent::boot();
        self::deleting(function($utilisateur){
            $utilisateur->etudiant()->delete();
            $utilisateur->enseignant()->delete();
        });
    }

}
