<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Utilisateur extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

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
    public function isStudent()
    {
        // verify if a given $user is student 
        $correspondingStudent = Etudiant::find($this->id_utilisateur);
        return $correspondingStudent ? true : false ;
    }

    public function isTeacher()
    {
        // verify if the user is a teacher and returns a boolean value 
        $correspondingTeacher = Enseignant::find($this->id_utilisateur);
        return $correspondingTeacher ? true : false;
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
