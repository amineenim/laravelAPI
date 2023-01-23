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

}
