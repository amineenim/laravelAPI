<?php

namespace App\Http\Controllers;

use App\Models\Etudiant;
use App\Models\Note;
use Illuminate\Http\Request;
use App\Http\Resources\EtudiantResource;
use App\Models\EtudiantCours;
use App\Models\Cours;
use App\Models\Utilisateur;

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
    public function show(Student $student)
    {
        //this method returns a single student with his id 
        return new EtudiantResource($student);
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
        return $studentGrades;
       
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
}
