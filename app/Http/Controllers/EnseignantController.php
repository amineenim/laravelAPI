<?php

namespace App\Http\Controllers;
use App\Models\Enseignant;
use App\Http\Resources\EnseignantsResource;
use App\Models\Cours;
use App\Models\EtudiantCours;
use App\Models\Etudiant;

use Illuminate\Http\Request;

class EnseignantController extends Controller
{
    //
    /* returns a listing of all enseignants */
    public function index()
    {
        return response()->json(
            [
                "data" => Enseignant::all()
            ]
        );
    }

    public function show(Enseignant $enseignant)
    {
        return new EnseignantsResource($enseignant);
    }

    public function getMyCourses($enseignantId)
    {
        $enseignant = Enseignant::find($enseignantId);
        $cours_enseignant = ($enseignant->cours);
        return ["data" => $cours_enseignant];
    }

    public function getMyStudents($enseignantId, $coursId)
    {
        //first get the teacher with the corresponding id 
        $teacher = Enseignant::find($enseignantId);
        //get the course for the corresponding coursId 
        $teacher_course = Cours::where('id_enseignant','=',$enseignantId)->where('id_cours',$coursId)->get();
        if(count($teacher_course) == 0)
        {
            return "no such course for the teacher with id ".$enseignantId ;
        }
        else 
        {

            $listeEtudiants = EtudiantCours::where('cours_id','=',$coursId)->get();
            //initialise an empty array to hold id's of students for that course
            $studentsIdentifiers = [];
            foreach($listeEtudiants as $etudiantCours)
            {
                array_push($studentsIdentifiers, $etudiantCours["etudiant_id"]);
            }
            //now that we have students id's we loop over the array and grab each student
            $myStudents = [];
            foreach($studentsIdentifiers as $studentId)
            {
                $myStudent = Etudiant::find($studentId);
                array_push($myStudents, $myStudent);
            }
            return ["data" => $myStudents];
            
        }
    
    }
}
