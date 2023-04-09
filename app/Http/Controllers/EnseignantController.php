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
use App\Models\Note;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

use Illuminate\Http\Request;

class EnseignantController extends Controller
{
    //
    /* returns a listing of all enseignants */
    public function index()
    {
        
        //verify if the user is an admin so it's authorized
        if(!Auth::user()->can('viewAny',Enseignant::class))
        {
            return response()->json([
                'message' => 'Non authorized '
            ],403);
        }
        $enseignants = Enseignant::all();
        //loop over enseignants array and for each element 
        // grab complementary data about it using id
        $teachers = [];
        foreach($enseignants as $enseignant)
        {
            $enseignantData = Utilisateur::find($enseignant->id_utilisateur);
            $enseignant_cours = Cours::where('id_enseignant',$enseignant->id_utilisateur)->get();
            //loop over courses and get name of course and ue
            //because a teacher can have many courses
            $cours_enseignant = [];
            foreach($enseignant_cours as $cours)
            {
                $nom_cours = $cours->nom_cours;
                $ue_cours = EducationalUnit::find($cours->id_ue);
                $nom_ue = $ue_cours->libelle_ue;
                $filiere = Filiere::find($ue_cours->id_filiere);
                $nom_filiere = $filiere->nom_filiere;
                $niveau_filiere = $filiere->niveau;
                $cours = (object)[
                    'cours' => $nom_cours,
                    'unite_enseignement' => $nom_ue,
                    'filiere' => $nom_filiere,
                    'niveau'  => $niveau_filiere
                ];
                array_push($cours_enseignant, $cours);
            }
            //now that we have object $enseignantData storing data we can build a new object
            $teacher = (object)[
                'id'        => $enseignant->id_utilisateur,
                'full_Name' => $enseignantData->nom.' '.$enseignantData->prenom,
                'email'     => $enseignantData->email,
                'responsabilty' => $enseignant->responsabilite_ens,
                'phone'     => $enseignantData->tel,
                'volume_horaire' => $enseignant->volume_horaire,
                'cours'      => $cours_enseignant
            ];
            // add this object to an array of objects storing data about each teacher
            array_push($teachers, $teacher);
        }
        // now we return the array of data
        return response()->json(['enseignants' => $teachers]);
    }

    public function show($enseignantId)
    {
        $teacher = Enseignant::find($enseignantId);
        if(!$teacher && Auth::user()->role =='admin')
        {
            return response()->json([
                'message' => 'Not Found'
            ],404);
        }
        if(!$teacher && Auth::user()->role =='user')
        {
            return response()->json([
                'message' => 'unauthorized'
            ],403);
        }
        //verify if the user is authorized to view a teacher resource
        if(!Auth::user()->can('view',$teacher))
        {
            return response()->json([
                'message' => 'unauthorized action'
            ],403);
        }
        //grab the teacher with the given id 
        
        
        $responsability = $teacher['responsabilite_ens'];
        $horaire = $teacher['volume_horaire'];
        // grab the user with the given id to get all data
        $user = Utilisateur::find($enseignantId);

        $fullName = $user['nom']." ".$user['prenom'];
        $email = $user['email'];
        $telephone = $user['tel'];
        return (object)[
            'full_name' => $fullName,
            'contact' => $email,
            'phone' => $telephone,
            'responsabilite' => $responsability,
            'volumeHoraire' => $horaire
        ];
        
    }

    public function create()
    {
        //verify if the user is authorized 
        if(!Auth::user()->can('create',Enseignant::class))
        {
            return response()->json([
                'message' => 'unauthorized'
            ],403);
        }
        //return a form to create a new teacher 
    }

    // alows creating a new teacher resource
    public function store(Request $request)
    {
        //verify if the user is authorized 
        if(!Auth::user()->can('create',Enseignant::class))
        {
            return response()->json([
                'message' => 'unauthorized'
            ],403);
        }
        $validatedRequest = $request->validate([
            'role' => 'required|in:user,admin,User,Admin',
            'nom' => 'bail|required|alpha|min:3|max:255',
            'prenom' => 'bail|required|alpha|min:3|max:255',
            'email'  => 'bail|required|email|unique:utilisateurs',
            'password' => 'bail|required|alpha_dash|min:8|max:14',
            'phone'  => 'bail|required|digits:10',
            'responsabilite_ens' => 'bail|required|min:5|max:255|regex:/^[a-zA-Z\s]*$/',
            'volume_horaire' => 'bail|required|integer|numeric',
        ]);
        // create new user using validated data 
        $newUser = Utilisateur::create([
            'role' => $validatedRequest['role'],
            'nom'  => $validatedRequest['nom'],
            'prenom'  => $validatedRequest['prenom'],
            'email'  => $validatedRequest['email'],
            'password' => Hash::make($validatedRequest['password']),
            'tel'    => $validatedRequest['phone']
        ]);
        // grab the id of the new user just created to create enseignant
        $userJustCreated = Utilisateur::where('email',$validatedRequest['email'])->get()[0];
        $userId = $userJustCreated->id_utilisateur;
        //after having valid data we can create a new Enseignant
        $newTeacher = Enseignant::create([
            'id_utilisateur'   => $userId,
            'responsabilite_ens' => $validatedRequest['responsabilite_ens'],
            'volume_horaire'   => $validatedRequest['volume_horaire'],
        ]);
        return response('Enseignant crée avec succès',201);
    }

    public function update($enseignantId,Request $request)
    {
        $teacher = Enseignant::find($enseignantId);
        if(!$teacher && $request->user()->role == 'admin')
        {
            return response()->json([
                'message' => 'no such resource teacher found !'
            ],404);
        }
        if(!$teacher && $request->user()->role == 'user')
        {
            return response()->json([
                'message' => 'Non authorized action'
            ],403);
        }
        // check if the user is authorized to update a teacher resource
        if(!$request->user()->can('update',$teacher))
        {
            return response()->json([
                'message' => 'Non authorized action'
            ],403);
        }
        
        $corresponding_user = Utilisateur::find($teacher->id_utilisateur);
        // verify if the form data submissed contains responsabilite fiedl
        if($request['responsabilite']){
            //if we get this field it means the form is coming from the admin
            $validatedRequest = $request->validate([
                'responsabilite' => 'required|min : 5|max:255|regex:/^[a-zA-Z\s]*$/',
                'vHoraire' => 'bail|required|integer|numeric',
            ]);
            $teacher->update([
                'responsabilite_ens' =>$validatedRequest['responsabilite'],
                'volume_horaire'     => $validatedRequest['vHoraire']
            ]);
            return response()->json([
                'message' => 'enseignant updated with succes'
               ],200);
        }
       // teacher can only modify it's email and phone number 
       $validatedRequest = $request->validate([
        'email' => 'required|email',
        'phone' => 'required|digits:10'
       ]);
       //verify if the new email has not already been taken
       $user = Utilisateur::where('email',$validatedRequest['email'])->first();
       if($user)
       {
            if($user->id_utilisateur != $enseignantId)
            {
                return response()->json([
                    'message' => 'this email adress has been taken already !'
                ]);
            }
       }
       // now that we have valid data, we can update in storage 
       $corresponding_user->update([
        'email' => $validatedRequest['email'],
        'tel'   => $validatedRequest['phone']
       ]);
       return response()->json([
        'message' => 'enseignant updated with succes'
       ],200);
       

        
    }
    public function destroy($enseignantId)
    {
        $enseignantToDelete = Enseignant::find($enseignantId);
        if(!$enseignantToDelete && Auth::user()->role =='admin')
        {
            return response()->json([
                'message' => 'no such resource found !'
            ],404);
        }
        elseif (!$enseignantToDelete && Auth::user()->role =='user')
        {
            return response()->json([
                'message' => 'unauthorized'
            ],403);
        }
        //verify if the user is authorized to perform this action
        if(!Auth::user()->can('delete',$enseignantToDelete))
        {
            return response()->json([
                'message' => 'unauthorized'
            ],403);
        }
        //look if there is a filiere who has the user as responsable 
        $filiereToDelete = Filiere::where('id_responsable',$enseignantId)->first();
        if($filiereToDelete)
        {
            $filiereToDelete->delete();
        }
        //$userToDelete = Utilisateur::find($enseignantToDelete->id_utilisateur);
        //$userToDelete->delete();
        $enseignantToDelete->delete();
        return response()->json(['deleted with succes'],202);
    }

    public function getMyCourses()
    {
        // verify if the user is authorized to check this page
        if(!Gate::allows('view-courses-teacher'))
        {
            return response()->json([
                'message' => 'Non Authorized !'
            ]);
        }
        $enseignantId = Auth::user()->id_utilisateur;
        $enseignant = Enseignant::find($enseignantId);
        if(!$enseignant)
        {
            return response()->json(
                ['message' => 'no such teacher foound']
            );
        }
        $cours_enseignant = ($enseignant->cours);
        if(count($cours_enseignant) ==0)
        {
            return response()->json(
                ['message' => "you don't have no courses"]
            );
        }
        $mesCours = [];
        foreach($cours_enseignant as $cours)
        {
            $cours_id = $cours->id_cours;
            $cours_name = $cours->nom_cours;
            $education_unit = EducationalUnit::find($cours->id_ue);
            $nom_unit_education = $education_unit->libelle_ue;
            $filiere = Filiere::find($education_unit->id_filiere);
            $nom_filiere = $filiere->nom_filiere;
            $niveau = $filiere->niveau;
            $moncours = (object)[
                'id_cours' => $cours_id,
                'nom_cours' => $cours_name,
                'unite_enseignement' => $nom_unit_education,
                'filiere'  => $nom_filiere,
                'niveau'   => $niveau
            ];
            array_push($mesCours, $moncours);
        }
        return ["mesCours" => $mesCours];
    }

    public function getMyStudents($coursId)
    {
        //verify if the user has the authorization to view 
        // students for a given course
        $enseignantId = Auth::user()->id_utilisateur;
        if(!Gate::allows('view-students-for-my-course',[$coursId]))
        {
            return response()->json([
                'message' => 'Unauthorized action'
            ]);
        }
        //get the course for the corresponding coursId 
        $teacher_course = Cours::where('id_enseignant','=',$enseignantId)->where('id_cours',$coursId)->get();
        $nom_cours = $teacher_course[0]->nom_cours;
        if(count($teacher_course) == 0)
        {
            return response()->json([
                'message' => 'no courses yet found !'
            ]);
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
                if($myStudent !== null)
                {
                    array_push($myStudents, $myStudent);
                }
            }
            
            //grab student's name and other data 
            if(!empty($myStudents)){
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
                        'id'       => $student->id_utilisateur,
                        'fullName' => $nom_prenom_eleve,
                        'email'     => $email_eleve,
                        'filiere'   => $filiere,
                        'niveau'    => $niveau
                    ];
                    array_push($studentsData, $newStudent);
                }
                return [$nom_cours => $studentsData];
            }else {
                return response()->json([
                    'message' => "you have no students for the course ".$nom_cours
                ],203);
            }
            
        }
    
    }

    //this function returns simply a form aalowing teacher to add a student to a given course
    public function addStudent($coursId)
    {
        //verify if the user has authorization to hit this route
        if(!Gate::allows('add-student-to-my-course',[$coursId]))
        {
            return response()->json([
                'message' => 'unauthorized action'
            ]);
        }
        return 'here you may add a student to your course with id '.$coursId.' and you are teacher with id '.Auth::user()->id ;
    }

    //this function handles the submission of the form and creating the link between the student and course
    public function addStudentToCourse(Request $request,$coursId)
    {
        //verify if the user has authorization to hit this route
        if(!Gate::allows('add-student-to-my-course',[$coursId]))
        {
            return response()->json([
                'message' => 'unauthorized action'
            ]);
        }
        //verify if the course belongs to the given teacher 
        $enseignantId = Auth::user()->id;
        $course = Cours::find($coursId);
        $course_name = $course->nom_cours ;
        $course_ueId = $course->id_ue;
        // we proceed to validate data 
        $validatedRequest = $request->validate([
            'nom_eleve' => 'required|exists:App\Models\Utilisateur,nom',
            'prenom_eleve' => 'required|exists:App\Models\Utilisateur,prenom',
            'email'     => 'required|exists:App\Models\Utilisateur,email'
        ]);
        //after having a valid data we can request etudiants table to grab etudiant id 
        $user = Utilisateur::where('nom',$validatedRequest['nom_eleve'])
        ->where('prenom',$validatedRequest['prenom_eleve'])
        ->where('email',$validatedRequest['email'])->first();
        $user_id = $user->id_utilisateur;
        //verify that the found id belongs actually to a student 
        $etudiant = Etudiant::find($user_id);
        if(!$etudiant)
        {
            return response()->json([
                'message' => 'no student corresponds to your data, please verify'
            ]);
        }
        // verify if the student is having the same filiere as cours by comparing to the id of UE to which the course belongs
        $student_filiereId = $etudiant->id_filiere;
        $owner_ue = EducationalUnit::find($course_ueId);
        if($student_filiereId != $owner_ue->id_filiere)
        {
            return response()->json([
                'message' => "the student can't be added to this course !"
            ]);
        }
        // verify if the student isn't already linked to the course 
        $record = EtudiantCours::where('cours_id',$coursId)
        ->where('etudiant_id',$user_id)->first();
        if($record)
        {
            return response()->json([
                'message' => 'the student already exists in the given course !'
            ]);
        }
        // now that all is set we can create a new record in the table
        // cour_etudiant which links a student to a given course
        $newRecord = EtudiantCours::create([
            'cours_id' => $coursId,
            'etudiant_id' => $etudiant->id_utilisateur
        ]);

        return response()->json([
            'success' => 'Etudiant ajoué au Cours '. $course_name.' avec succèes'
        ],201);
        
    }

    //this function handles displaying a form so the teacher can give grade to student
    public function assignGrade($coursId,$studentId)
    {
        //verify if the user is authorized 
        if(!Gate::allows('assign-grade',[$studentId,$coursId]))
        {
            return response()->json([
                'message' => 'action Non Authorized'
            ],403);
        }
        $cours = Cours::find($coursId);
        $nom_cours = $cours->nom_cours;
        $enseignantId = Auth::user()->id;
    
    
        $user = Utilisateur::find($studentId);
        $studenFullName = $user->nom." ".$user->prenom;
        $studentemail = $user->email;

        $student = Etudiant::find($studentId);
        $id_filiere = $student->id_filiere;
        $filiere = Filiere::find($id_filiere);
        $nom_filiere = $filiere->nom_filiere;
        $niveau = $filiere->niveau;
        
        $dataToDisplay = (object)[
            'courseName' => $nom_cours,
            'student' => [
                'fullName' => $studenFullName,
                'filiere'  => $nom_filiere,
                'niveau'   => $niveau
            ]
        ];
        return response()->json([
            "message" => 'here we will display a form with a single input that receives the grade for a student for a given course',
            "data" => $dataToDisplay
        ]);
    }

    //this function handles storing the grade of the student for a given course
    public function storeGrade(Request $request,$coursId,$studentId)
    {
        //verify if the user is authorized 
        if(!Gate::allows('assign-grade',[$studentId,$coursId]))
        {
            return response()->json([
                'message' => 'action Non Authorized'
            ],403);
        }
        //verify if the grade already exists in table notes 
        $grade = Note::where('id_utilisateur',$studentId)
        ->where('id_cours',$coursId)->first();
        if($grade)
        {
            return response()->json([
                'message' => "étudiant déja noté !"
            ]);
        }
     
        //here we only should validate the grade and maybe redirect 
        $validatedRequest = $request->validate([
            'note' => 'required|numeric|between:0,20.00'
        ]);
        // now we create a new grade record in notes table
        $newGrade = Note::create([
            'id_utilisateur' => $studentId,
            'id_cours'       => $coursId,
            'note'           => $validatedRequest['note']
        ]);
        return response()->json([
            "success" => "Note ajoutée avec succès!"
        ]);
    }
}
