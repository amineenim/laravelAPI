<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
        return $this->belongsTo(Enseignant::class,'id_utilisateur','id_enseignant');
    }

    public function notes()
    {
        return $this->hasMany(Note::class,'id_cours','id_cours');
    }

    public static function boot()
    {
        parent::boot();
        self::deleting(function($cours){
            $cours->notes()->each(function($note){
                $note->delete();
            });
        DB::table('cour_etudiant')->where('cours_id',$cours->id_cours)->delete();
        });
    }
    

   
    
}







?>
