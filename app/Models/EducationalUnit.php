<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EducationalUnit extends Model
{
    use HasFactory;

    protected $table = 'ue';
    protected $primaryKey = 'id_ue';
    public $timestamps = false;

    protected $fillable = [
        'id_filiere',
        'libelle_ue',
        'description'
    ];

    public function cours()
    {
        return $this->hasMany(Cours::class,'id_ue','id_ue');
    }

    public static function boot()
    {
        parent::boot();
        self::deleting(function($educationalUnit){
            $educationalUnit->cours->each(function($cours){
                $cours->delete();
            });
        });
    }
}
