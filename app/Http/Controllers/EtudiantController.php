<?php

namespace App\Http\Controllers;

use App\Models\Etudiant;
use App\Models\Note;
use Illuminate\Http\Request;
use App\Http\Resources\EtudiantResource;
use App\Models\EtudiantCours;
use App\Models\Cours;
use App\Models\Utilisateur;
use App\Models\Filiere;
use App\Models\EducationalUnit;
use App\Models\Edt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class EtudiantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //verify if the user is having authorization to view this page 
        $currentUser = Auth::user();
        if($currentUser->cannot('viewAny'))
        {
            return response()->json([
                'message' => 'unauthorized ! only admin can view this page'
            ],403);
        }
        //this function returns all students in Database 
        $students = Etudiant::all();
        // loop over students and get data for each one 
        $etudiants = [];
        foreach($students as $student)
        {
            //query Utilisateurs table to get data about student 
            $studentData = Utilisateur::find($student->id_utilisateur);
            $fullName = $studentData->nom.' '.$studentData->prenom;
            $email = $studentData->email;
            $phone = $studentData->tel;
            $diplome = $student->diplome_etudiant;
            $filiere = Filiere::find($student->id_filiere);
            $nom_filiere = $filiere->nom_filiere;
            $niveau = $filiere->niveau;
            // now that we have all needed data about student we put it in an object
            $etudiant = (object)[
                'full_Name' => $fullName,
                'contact'   => $email,
                'phone'     => $phone,
                'diplome'   => $diplome,
                'filiere'   => $nom_filiere,
                'niveau'    => $niveau
            ];
            array_push($etudiants, $etudiant);
        }
        return ['students' => $etudiants];
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //verify if the currently authenticad user has authorization to create a new student
        if($request->user()->cannot('create'))
        {
            return response()->json([
                'message' => 'Not authorized !'
            ],403);
        }
        return response()->json([
            'message' => 'here u can create a new student'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // this function allows to create a new student resoource
        //verify if the currently authenticad user has authorization to create a new student
        if($request->user()->cannot('create'))
        {
            return response()->json([
                'message' => 'Not authorized !'
            ],403);
        }
        
         $validatedRequest = $request->validate([
            'role' => 'required|in:user,admin,User,Admin',
            'nom' => 'bail|required|alpha|min:3|max:255',
            'prenom' => 'bail|required|alpha|min:3|max:255',
            'email'  => 'bail|required|email|unique:utilisateurs',
            'password' => 'bail|required|alpha_dash|min:8|max:14',
            'phone'  => 'bail|required|digits:10',
            'diplome_etudiant' => 'required|min:5|max:60|regex:/^[a-zA-Z0-9\s]*$/',
            'filiere'       => 'required|exists:filieres,nom_filiere',
            'niveau'        => 'required|in:L,M,D'
        ]);
        // grab the id of the filiere based on it's name 
        $corresponding_filiere = Filiere::where('nom_filiere',$validatedRequest['filiere'])
        ->where('niveau',$validatedRequest['niveau'])->first();
        if(!$corresponding_filiere)
        {
            return response()->json(['message' => 'please verify your filiere data']);
        }
        $id_filiere = $corresponding_filiere->id_filiere;
         // create new user using validated data 
         $newUser = Utilisateur::create([
            'role' => $validatedRequest['role'],
            'nom'  => $validatedRequest['nom'],
            'prenom'  => $validatedRequest['prenom'],
            'email'  => $validatedRequest['email'],
            'password' => Hash::make($validatedRequest['password']),
            'tel'    => $validatedRequest['phone']
        ]);
         //grab the user just created to get his Id 
        $userJustCreated = Utilisateur::where('email',$validatedRequest['email'])->get()[0];
        $studentId = $userJustCreated->id_utilisateur;
        //now that data is validated we creata a new student instance
        $newStudent = Etudiant::create([
            'id_utilisateur'   => $studentId,
            'diplome_etudiant' => $validatedRequest['diplome_etudiant'],
            'id_filiere'       => $id_filiere,
        ]);

        return response()->json(
            ['message' => "student created with succes !"]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,$studentId)
    {
        $etudiant = Etudiant::find($studentId);
        if(!$etudiant)
        {
            return response()->json(
                ['message' => 'no such student found !']
            );
        }
        //verify if the authenticated user is authorized to view this resource 
        if($request->user()->cannot('view',$etudiant))
        {
            return response()->json([
                'message' => 'unauthorized action !'
            ],403);
        }
        
        // get all other data about student from utilisateurs table
        $user = Utilisateur::find($studentId);
        $nom_etudiant = $user['nom']." ".$user["prenom"];
        $email = $user["email"];
        $telephone = $user["tel"];
        $diplome = $etudiant["diplome_etudiant"];
        $filiereId = $etudiant['id_filiere'];
        $filiere = Filiere::find($filiereId);
        $nomFiliere = $filiere["nom_filiere"];
        $niveau = $filiere["niveau"];
        return (object)[
            'nom_prenom' => $nom_etudiant,
            'email_etudiant' => $email,
            'phone_etudiant' => $telephone,
            'diplome' => $diplome,
            'filiere' => $nomFiliere,
            'niveau' => $niveau
        ];

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function edit($studentId)
    {
        $student = Etudiant::find($studentId);
        if(!$student)
        {
            return response()->json([
                'message' => 'no resource found !'
            ]);
        }
        //verify if the user is authorized to perform update againt this resource
        if(Auth::user()->cannot('update',$student))
        {
            return response()->json([
                'message' => 'Unauthorized action !'
            ],403);
        }
        //returns a form to update a student resource
        return 'here you can update a student profile ';
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Etudiant $student)
    {
        //verify if the user is authorized to perform update action against this resource
        if(Auth::user()->cannot('update',$student))
        {
            return response()->json([
                'message' => 'Unauthorized action !'
            ],403);
        }
        // handles updating the student resource in storage 
        // a student can only modify it's email, password and phone 
        $validatedRequest = $request->validate([
            'email' => 'required|email|unique:utilisateurs,email|max:255',
            'password' => 'required|alpha_dash|min:8|max:14',
            'tel'   => 'bail|required|digits:10'
        ]);
        // now we can update corresponding resource in storage 
        $user = Utilisateur::find($student->id_utilisateur);
        $user->update([
            'email' => $validatedRequest['email'],
            'password' => Hash::make($validatedRequest['password']),
            'tel'     => $validatedRequest['tel']
        ]);
        return response()->json([
            'message' => 'resource updated succesfully !'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function destroy($studentId)
    {
        //delete the student with corresponding id 
        $studentToDelete = Etudiant::find($studentId);
        if(!$studentToDelete)
        {
            return response()->json(
                ['message' => 'resource student not found !']
            );
        }
        //verify if the user has the authorization to delete the resource 
        if(Auth::user()->cannot('delete',$studentToDelete))
        {
            return response()->json([
                'message' => 'Unautorized Action'
            ],403);
        }
        
        // delete the user corresponding to that student
        //$userToDelete = Utilisateur::find($studentId);
        //$userToDelete->delete();
        $studentToDelete->delete();
        return response()->json('deleted successefully',202);
    }

    public function getMyGrades()
    {
        // verify if the user is authorized to view this page 
        if(!Gate::allows('view-grades'))
        {
            return response()->json([
                'message' => 'unauthorized'
            ],403);
        }
        //get the currently authenticated user id 
        $studentId = Auth::user()->id_utilisateur;
        //grab the student with the given id 
        $student = Etudiant::find($studentId);
        //grab all courses related to the student
        if(!$student)
        {
            return response()->json(
                ["message" => "no student found !"]
            );
        }
        $cours = $student->cours;
        if(count($cours) == 0)
        {
            return response()->json(
                ["message" => "no grades found, still not having no courses yet"]
            );
        }

        //initialise an empty object that holds the course name and corresponding grade
        $cours_note = (object) [
            "cours" => "",
            "note" => ""
        ];

        //initialise an empty array to hold the student grades for all subjects/courses
        $studentGrades = [];

        foreach($cours as $cours)
        {
            $id_cours =  $cours['id_cours'];
            $nom_cours = $cours['nom_cours'];
            $notes = $cours->notes;
            $data = $cours['pivot']["etudiant_id"];// equivalent to $studentId


            // we return an array with one element 
            $note = Note::where('id_utilisateur','=',$data)->where('id_cours',$id_cours)->get();
            // we access the first element which is the only one in array 
            //it's an object so we get the key "note" value 
            $grade =  $note[0]["note"];

            $cours_note = (object) [
                "cours" => $nom_cours,
                "note" => $grade
            ];
            array_push($studentGrades,$cours_note);
            
        }
        return response()->json(['data' => $studentGrades]);
       
    }

    // function that allows a student to check all it's courses 
    public function getMyCourses()
    {
        if(!Gate::allows('view-courses-student'))
        {
            return response()->json([
                'message' => 'unauthorized'
            ],403);
        }
        $studentId = Auth::user()->id_utilisateur;
        //grab the student with the given id 
        $student = Etudiant::find($studentId);
        if(!$student)
        {
            return response()->json(['message' => 'no student found with given id']);
        }
        else 
        {
            // grab the id of courses from the table cour_etudiant that links a student to a course id
            $etudiant_cours = EtudiantCours::where('etudiant_id','=',$studentId)->get();
          
            if(count($etudiant_cours) == 0)
            {
                return response()->json(['message' => "you don't have no courses !"]);
                
            }
            else 
            {
                //initialise an empty array to store ids of courses 
                $coursesIds = [];
                // loop over the table of results and store the courses ids in a table 
                foreach($etudiant_cours as $element)
                {
                    array_push($coursesIds, $element["cours_id"]);
                }
                //loop over each id and grab the corresponding course 
                $studentCourses = [];
                foreach($coursesIds as $courseId)
                {
                    $cours = Cours::find($courseId);
                    array_push($studentCourses, $cours);
                }
                //initialise an array to hold name of courses and corresponding teacher
                $coursesWithTeacher = [];
                for($i=0; $i < count($studentCourses); $i++)
                {
                    $teacherOfCourse = Utilisateur::find($studentCourses[$i]["id_enseignant"]);
                    $cours_teacher = (object) [
                        "cours" => $studentCourses[$i]["nom_cours"],
                        "nom_prof" => $teacherOfCourse["nom"]." ".$teacherOfCourse["prenom"],
                        "contact_prof" => $teacherOfCourse["email"]
                    ];
                    array_push($coursesWithTeacher, $cours_teacher);
                }
                
                return response()->json(
                    [
                        "data" => $coursesWithTeacher
                    ]
                );

            }
            
        }
    }

    // returns EDT for a given student 
    public function getMySchedule()
    {
        // verify if the user has authorization 
        if(!Gate::allows('view-edt'))
        {
            return response()->json([
                'message' => 'unauthorized !'
            ]);
        }
        $studentId = Auth::user()->id_utilisateur ;
        // grab the student to get his filiere 
        $student = Etudiant::find($studentId);
        if(!$student)
        {
            return response()->json(
                ['message' => 'no such student found']
            );
        }
        $filiereId = $student['id_filiere'];
        //grab the filiere of the student 
        $filiere = Filiere::find($filiereId);
        // get name and level for filiere
        $filiereName = $filiere['nom_filiere'];
        $level = $filiere['niveau'];

        // grab all UE's for that filiere
        $educationalUnits = EducationalUnit::where('id_filiere',$filiereId)->get();
    
        // loop over the array of objects representing each UE and get "libelle_ue" and "id_ue"
        $units =[];
        foreach($educationalUnits as $unit)
        {
            $newUnit = (object) [
                'id_ue' => $unit['id_ue'],
                'nom_ue' => $unit['libelle_ue']
            ];
            array_push($units, $newUnit);
        }
        if(count($units) == 0)
        {
            return response()->json([
                'message' => 'no educational units are found in your branch now'
            ]);
        }
      
        // now for each unit we get all courses related to it 
        $coursesForUnits = [];
        foreach($units as $unit)
        {
            $id = $unit->id_ue;
            $courses = Cours::where('id_ue',$id)->get();
            $coursesForUnit = (object)[
                'ue' => $unit->nom_ue,
                'cours' => $courses
            ];
            array_push($coursesForUnits, $coursesForUnit);
        }
       

        // now that we have the courses with the UE to which they belong 
        // we can grab the EDT 
        $coursesWithEdt = [];
        foreach($coursesForUnits as $courseForUnit)
        {
            $nom_ue = $courseForUnit->ue;
            $courses = $courseForUnit->cours;
            foreach($courses as $course)
            {
                $nomCours = $course->nom_cours;
                //grab the teacher's name for that course
                $teacher = Utilisateur::find($course->id_enseignant);
                $teacherName = $teacher['nom']." ".$teacher['prenom'];
                $teacherEmail = $teacher['email'];
                //grab the EDT for that course 
                $edtForCourse = Edt::where('id_cours',$course->id_cours)->where('id_filiere',$filiereId)->get();
                //return $edtForCourse;
                foreach($edtForCourse as $edt)
                {
                    // create an object to store all data related to a course
                    $type_course = $edt->type_cours;
                    $start_course =  $edt->date_debut;
                    $end_course = $edt->date_fin;
                    $courseData = (object)[
                        'unite_enseignement' => $nom_ue,
                        'nom_cours' => $nomCours,
                        'prof_cours' => $teacherName,
                        'prof_contact' => $teacherEmail,
                        'type' => $type_course,
                        'debut' => $start_course,
                        'fin'  => $end_course,
                    ];
                    array_push($coursesWithEdt, $courseData);

                }

            }

        }
        if(count($coursesWithEdt) == 0)
        {
            return response()->json([
                'message' => "u don't have no courses for the moment !"
            ]);
        }
        return response()->json(['data' => $coursesWithEdt]);




    }
}
