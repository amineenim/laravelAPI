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

class EtudiantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //this function returns all students in Database 
        $students = Etudiant::all();
        return ['students' => $students];
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function show(Etudiant $student)
    {
        //this method returns a single student with his id 
        $etudiant = Etudiant::find($student)[0];
        // get all other data about student from utilisateurs table
        $user = Utilisateur::find($student)[0];
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
    public function edit(Student $student)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Student $student)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function destroy(Student $student)
    {
        //
    }

    public function getMyGrades($studentId)
    {
        //grab the student with the given id 
        $student = Etudiant::find($studentId);
        //grab all courses related to the student
        $cours = $student->cours;

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
    public function getMyCourses($studentId)
    {
        
        //grab the student with the given id 
        $student = Etudiant::find($studentId);
        if(!$student)
        {
            return "no such student found !";
        }
        else 
        {
            // grab the id of courses from the table cour_etudiant that links a student to a course id
            $etudiant_cours = EtudiantCours::where('etudiant_id','=',$studentId)->get();
          
            if(count($etudiant_cours) == 0)
            {
                return "you don't have no courses !";
                
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
    public function getMySchedule($studentId)
    {
        // grab the student to get his filiere 
        $student = Etudiant::find($studentId);
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
        return response()->json(['data' => $coursesWithEdt]);




    }
}
