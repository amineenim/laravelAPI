<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class etudiantCours extends Model
{
    use HasFactory;

    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;
    protected $table = 'cour_etudiant';
    protected $fillable = [
        'cours_id',
        'etudiant_id'
    ];
}
