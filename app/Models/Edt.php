<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Edt extends Model
{
    use HasFactory;

    protected $table = 'edt';
    protected $primaryKey = 'id_edt';
    public $timestamps = false;

    protected $fillable = [
        'id_filiere',
        'id_cours',
        'date_debut',
        'date_fin',
        'type_cours'
    ];
}
