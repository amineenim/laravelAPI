<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;
    protected $primaryKey="id_note";

    //define the ralationship with the cours table

    public function cours()
    {
        $this->belongsTo(Cours::class);
    }

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class);
    }
}
