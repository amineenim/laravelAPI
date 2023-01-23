<?php

namespace App\Http\Controllers;
use App\Models\Enseignant;
use App\Http\Resources\EnseignantsResource;
use App\Models\Cours;
use App\Models\EtudiantCours;
use App\Models\Etudiant;
use App\Models\Utilisateur;
use App\Models\Filiere;
use App\Models\EducationalUnit;

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
        //grab the teacher with the given id 
        $teacher = Enseignant::find($enseignant)[0];
        $responsability = $teacher['responsabilite_ens'];
        $horaire = $teacher['volume_horaire'];
        // grab the user with the given id to get all data
        $user = Utilisateur::find($enseignant)[0];

        $fullName = $user['nom']." ".$user['prenom'];
        $email = $user['email'];
        $telephone = $user['tel'];
        return (object)[
            'nom_prenom' => $fullName,
            'contact' => $email,
            'phone' => $telephone,
            'responsabilite' => $responsability,
            'volumeHoraire' => $horaire
        ];
        
    }

    public function create()
    {
        //return a form to create a new teacher 
    }

    // alows creating a new teacher resource
    public function store(Request $request)
    {
        $validatedRequest = $request->validate([
            'id_utilisateur' => 'bail|required|unique:enseignants,id_utilisateur|numeric|integer',
            'responsabilite_ens' => 'bail|required|min:5|max:255|regex:/^[a-zA-Z\s]*$/',
            'volume_horaire' => 'bail|required|integer|numeric',
        ]);
        //after having valid data we can create a new Enseignant
        $newTeacher = Enseignant::create([
            'id_utilisateur' => $validatedRequest['id_utilisateur'],
            'reponsabilite_ens' => $validatedRequest['responsabilite_ens'],
            'volume_horaire'   => $validatedRequest['volume_horaire'],
        ]);
        return response('Eseignant crée avec succès',201);
    }

    public function update(Enseignant $enseignant,Request $request)
    {
        //this function handles both put which changes all the ressource 
        // and patch which changes a part of the ressource 
        $teacher = Enseignant::find($enseignant)[0];
        if (isset($request->responsabilite_ens) && isset($request->volume_horaire))
        {
            // validate the data 
            $validatedRequest = $request->validate([
                'responsabilite_ens' => 'bail|required|min:5|max:255|regex:/^[a-zA-Z\s]*$/',
                'volume_horaire'     => 'bail|required|integer|numeric'
            ]);
            $teacher->update([
                'responsabilite_ens' => $validatedRequest['responsabilite_ens'],
                'volume_horaire'     => $validatedRequest['volume_horaire']
            ]);

            return response('updated successefully',200);
        }
        elseif (isset($request->responsabilite_ens))
        {
            // validate the data 
            $validatedRequest = $request->validate([
                'responsabilite_ens' => 'bail|required|min:5|max:255|regex:/^[a-zA-Z\s]*$/'
            ]);
            $teacher->update([
                'responsabilite_ens' => $validatedRequest['responsabilite_ens']
            ]);
            
            return response('updated with succes',200);
        }
        elseif(isset($request->volume_horaire))
        {
             // validate the data 
            $validatedRequest = $request->validate([
                'volume_horaire' => 'bail|required|integer|numeric'
            ]);
            $teacher->update([
                'volume_horaire' => $validatedRequest['volume_horaire']
            ]);
                        
            return response('updated with succes',200);

        }
        else 
        {
            return response('missing data');
        }
        

    }

    public function getMyCourses($enseignantId)
    {
        $enseignant = Enseignant::find($enseignantId);
        $cours_enseignant = ($enseignant->cours);
        $mesCours = [];
        foreach($cours_enseignant as $cours)
        {
            $cours_name = $cours->nom_cours;
            $education_unit = EducationalUnit::find($cours->id_ue);
            $nom_unit_education = $education_unit->libelle_ue;
            $moncours = (object)[
                'nom_cours' => $cours_name,
                'unite-enseignement' => $nom_unit_education
            ];
            array_push($mesCours, $moncours);
        }
        return ["data" => $mesCours];
    }

    public function getMyStudents($enseignantId, $coursId)
    {
        //first get the teacher with the corresponding id 
        $teacher = Enseignant::find($enseignantId);
        //get the course for the corresponding coursId 
        $teacher_course = Cours::where('id_enseignant','=',$enseignantId)->where('id_cours',$coursId)->get();
        $nom_cours = $teacher_course[0]->nom_cours;
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
            //grab student's name and other data 
            $studentsData = [];
            foreach($myStudents as $student)
            {
                $studentData = Utilisateur::find($student->id_utilisateur);
                $studentFiliere = Filiere::find($student->id_filiere);
                $nom_prenom_eleve = $studentData->nom." ".$studentData->prenom;
                $email_eleve = $studentData->email;
                $filiere = $studentFiliere->nom_filiere;
                $niveau = $studentFiliere->niveau;
                $newStudent = (object)[
                    'full name' => $nom_prenom_eleve,
                    'email'     => $email_eleve,
                    'filiere'   => $filiere,
                    'niveau'    => $niveau
                ];
                array_push($studentsData, $newStudent);
            }
            return [$nom_cours => $studentsData];
            
        }
    
    }
}
