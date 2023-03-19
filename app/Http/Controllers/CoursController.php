<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Enseignant;
use App\Models\EducationalUnit;
use Illuminate\Support\Facades\Auth;
use App\Models\Utilisateur;
use App\Models\Filiere;
use App\Models\Cours;

class CoursController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //check if the user is authorized to view all Cours resources
        if(!Auth::user()->can('viewAny',Cours::class))
        {
            return response()->json([
                'message' => 'unauthorized'
            ],403);
        }
        $allCourses = Cours::all();
        $data = [];
        // loop over the array of courses and for each course grab 
        //the name and the ue to which it belongs and teacher
        foreach($allCourses as $course)
        {
            // get the teacher 
            $enseignant = Utilisateur::find($course->id_enseignant);
            $nom_prof = $enseignant->nom." ".$enseignant->prenom;
            $contact = $enseignant->email;
            // get the ue 
            $educationaUnit = EducationalUnit::find($course->id_ue);
            $nom_ue = $educationaUnit->libelle_ue;
            $nom_filiere = Filiere::find($educationaUnit->id_filiere)->nom_filiere;
            $dataCourse = (object)[
                'id_cours'  => $course->id_cours,
                'nom_cours' => $course->nom_cours,
                'unite_enseignement' => $nom_ue,
                'filiere'     => $nom_filiere,
                'professeur'  => (object) [
                    'nom' => $nom_prof,
                    'contact' => $contact
                ]
            ];
            array_push($data,$dataCourse);
        }
        return response()->json([
            'data' => $data
        ],200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //verify if the user is authorized 
        if(!Auth::user()->can('create',Cours::class))
        {
            return response()->json([
                'message' => 'unauthorized action'
            ],403);
        }
        return response()->json(
            ['message' => 'here you can create a course via form']
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
         //verify if the user is authorized 
         if(!Auth::user()->can('create',Cours::class))
         {
             return response()->json([
                 'message' => 'unauthorized action'
             ],403);
         }
        $enseignantId = Auth::user()->id_utilisateur;
        
        $enseignant = Enseignant::find($enseignantId);
      
        // now that we are sure that a teacher is the one creating the course
        //validate request data
        $validatedRequest = $request->validate([
            'nom' => 'required|min:3|max:60|regex:/^[a-zA-Z\s]*$/',
            'nom_ue' => 'required|exists:App\Models\EducationalUnit,libelle_ue'
        ]);
        // now that we have valid data, we should grab the id based on the ue name to create a new course record
        $ue = EducationalUnit::where('libelle_ue',$validatedRequest['nom_ue'])->first();
        // store the id of ue to use it when creating the course record
        $id_ue = $ue->id_ue;
        $newCourse = Cours::create([
            'nom_cours' => $validatedRequest['nom'],
            'id_enseignant' => $enseignantId,
            'id_ue'     => $id_ue
        ]);
        if(!$newCourse)
        {
            return response()->json([
                'message' => 'Oops! something went wrong'
            ]);
        }
        return response()->json([
            'message' => 'course created with succes !'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //this method displays a single course data based on it's id

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //form to edit a specific course resource
        //verify if the user is authorized 
        $course = Cours::find($id);
        if(!Auth::user()->can('update',$course))
        {
            return response()->json([
                'message' => 'Non Authorized Action'
            ],403);
        }
        // now that the user is authorized, grab data about the course to display it in form 
        $nom_cours = $course->nom_cours;
        $nom_ue    = EducationalUnit::find($course->id_ue)->libelle_ue;
        return (object)[
            'message' => 'here u can edit the course',
            'data'    => (object)[
                'nomCours' => $nom_cours,
                'unite_enseignement' => $nom_ue
            ]
        ];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $course = Cours::find($id);
        //verify if the user is authorized to perform this action
        if(!$request->user()->can('update',$course))
        {
            return response()->json([
                'message' => 'unauthorized action'
            ],403);
        }
        // validate request 
        $validatedRequest = $request->validate([
            'nomCours' => 'required|min:3|max:60|regex:/^[a-zA-Z\s]*$/',
            'nomUe'    => 'required|exists:App\Models\EducationalUnit,libelle_ue'
        ]);
        // get the id of the ue based on it's name
   
        $idUe = EducationalUnit::where('libelle_ue',$validatedRequest['nomUe'])->first()->id_ue;
        // update the corresponding resource in storage 
        $course->update([
            'nom_cours' => $validatedRequest['nomCours'],
            'id_ue'     => $idUe
        ]);
        return response()->json([
            'message' => 'updated with success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //verify if the user is authorized to delete the course resource
        $course = Cours::find($id);
        if(!Auth::user()->can('delete',$course))
        {
            return response()->json([
                'message' => 'unauthorized !'
            ],403);
        }
        $course->delete();
        return response()->json([
            'message' => 'deleted successfully !'
        ],202);
    }
}
