<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cours extends Model
{
    use HasFactory;

    protected $table = 'cours';
    protected $primaryKey = 'id_cours';
    public $timestamps = false;

    protected $fillable = [
        'nom_cours',
        'id_enseignant',
        'id_ue'
    ];

    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class);
    }

    

   
    
}







?>
