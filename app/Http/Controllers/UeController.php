<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Filiere;
use App\Models\EducationalUnit;
use App\Models\Cours;
use App\Models\Utilisateur;

class UeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //verify that the user is authorized to perform the action
        if(!Auth::user()->can('viewAny',EducationalUnit::class))
        {
            return response()->json([
                'message' => 'unauthorized action'
            ],403);
        }
        //returns all ues 
        $educationalUnits = EducationalUnit::all();
        // loop over array of ues and foreach ue grab the courses beloging to it and which filiere 
        $data_for_ues = [];
        foreach($educationalUnits as $educationalUnit)
        {
            $id = $educationalUnit->id_ue;
            $nom_ue = $educationalUnit->libelle_ue;
            $description = $educationalUnit->description;
            $filiere = Filiere::find($educationalUnit->id_filiere);
            $correspondingFiliere = $filiere->nom_filiere;
            $niveau = $filiere->niveau;
            $courses = Cours::where('id_ue','=',$educationalUnit->id_ue)->get();
            //now that we get courses for ue we loop over them an grab name of the course and it's teacher
            $coursesForUe = [];
            foreach($courses as $course)
            {
                $nom_cours = $course->nom_cours;
                $enseignant_course = utilisateur::find($course->id_enseignant);
                $enseignant_fullName = $enseignant_course->nom." ".$enseignant_course->prenom;
                $data_course = (object)[
                    'nom_cours' => $nom_cours,
                    'enseignant' => $enseignant_fullName
                ];
                array_push($coursesForUe,$data_course);
            }
            $ue_data = (object)[
                'id'    => $id,
                'nom_ue'=> $nom_ue,
                'description' => $description,
                'filiere' => $correspondingFiliere,
                'niveau'  => $niveau,
                'cours'   => $coursesForUe
            ];
            array_push($data_for_ues,$ue_data);
        }
        return response()->json([
            'data' => $data_for_ues
        ],200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // verify if the user is authorized 
        if(!Auth::user()->can('create',EducationalUnit::class))
        {
            return response()->json([
                'message' => 'unauthorized action'
            ],403);
        }
        //returns the form for creating a new UE resource
        return response()->json([
            'message' => 'here you might add a new ue'
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
        // verify if the user is authorized 
        if(!$request->user()->can('create',EducationalUnit::class))
        {
            return response()->json([
                'message' => 'unauthorized action'
            ],403);
        }
        //this function handles creating a new educational unit resource
        $validatedRequest = $request->validate([
            'nom_filiere' => 'bail|required|exists:filieres,nom_filiere',
            'niveau'      => 'bail|required|in:L,M,D|',
            'nom_ue'      => 'bail|required|regex:/^[a-zA-Z0-9éè\s\']*$/|min:6|max:60',
            'description' =>  'bail|required|min:10|max:255|regex:/^[a-zA-Z0-9éè\s\'\.\,]*$/'
        ]);
        //verify if the given "nom_filiere" and "niveau" correspond to an existing filiere 
        $existingFiliere = Filiere::where('nom_filiere',$validatedRequest['nom_filiere'])
        ->where('niveau',$validatedRequest['niveau'])->first();
        if(!$existingFiliere)
        {
            return response()->json([
                'message' => 'no such filiere found, please verify your data'
            ]);
        }
        //verify if the UE doesn't already exist
        $ueToCreate = EducationalUnit::where('id_filiere',$existingFiliere->id_filiere)
        ->where('libelle_ue',$validatedRequest['nom_ue'])->first();
        if($ueToCreate)
        {
            return response()->json([
                'message' => "unité d'enseignement déja existante pour la filère ". $existingFiliere->nom_filiere
            ]);
        }
        //get id of filiere 
        $filiereId = $existingFiliere->id_filiere;
        //create UE record 
        $newRecord = EducationalUnit::create([
            'id_filiere' => $filiereId,
            'libelle_ue' => $validatedRequest['nom_ue'],
            'description' => $validatedRequest['description']
        ]);
        return response()->json([
            'success' => 'educational unit created successefully'
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
        // grab the ue based on it's id 
        $educationalUnit = EducationalUnit::find($id);
        //verify if the user is authorized to view the resource
        if(!Auth::user()->can('view',$educationalUnit))
        {
            return response()->json([
                'message' => 'unauthorized action'
            ],403);
        }

        //grab data about ue 
        $nom_ue = $educationalUnit->libelle_ue;
        $description = $educationalUnit->description;
        $filiere = Filiere::find($educationalUnit->id_filiere);
        $nom_filiere = $filiere->nom_filiere;
        $niveau = $filiere->niveau;
        $courses_for_ue = Cours::where('id_ue',$educationalUnit->id_ue)->get();
        $ue_courses = [];
        foreach($courses_for_ue as $course_ue)
        {
            $nom_cours = $course_ue->nom_cours;
            $enseignant_course = Utilisateur::find($course_ue->id_enseignant);
            $nom_prof = $enseignant_course->nom." ".$enseignant_course->prenom;
            $contact = $enseignant_course->email;
            $phone = $enseignant_course->tel;
            $data_course = (object)[
                'nom_cours' => $nom_cours,
                'prof' => (object)[
                    'nom' => $nom_prof,
                    'contact' => $contact,
                    'telephone' => $phone
                ]
            ];
            array_push($ue_courses,$data_course);
        }
        $data_ue = (object)[
            'nom_ue' => $nom_ue,
            'description' => $description,
            'filiere' => $nom_filiere,
            'niveau' => $niveau,
            'cours' => $ue_courses
        ];


        return response()->json([
            'data' => $data_ue
        ],200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //returns data about a ue resource so it can be modified 
        $educationalUnit = EducationalUnit::find($id);
        //verify if user is authorized 
        if(!Auth::user()->can('update',$educationalUnit))
        {
            return response()->json([
                'message' => 'unauthorized action !'
            ],403);
        }
        // get data about ue 
        $filiere = Filiere::find($educationalUnit->id_filiere);
        $nom_filiere = $filiere->nom_filiere;
        $niveau  = $filiere->niveau;
        $nom_ue = $educationalUnit->libelle_ue;
        $description = $educationalUnit->description;
        $data_ue = (object)[
            'nom_ue' => $nom_ue,
            'description' => $description,
            'filiere' => $nom_filiere,
            'niveau'  => $niveau
        ];

        return response()->json([
            'message' => 'here u can update a Ue resource',
            'data'    => $data_ue
        ]);
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
        //handles modifying a Ue resource in storage 
        $ue_to_modify = EducationalUnit::find($id);
        //verify if user is authorized 
        if(!$request->user()->can('update',$ue_to_modify))
        {
            return response()->json([
                'message' => 'unauthorized action !'
            ],403);
        }
        //validate the data 
        $validatedRequest = $request->validate([
            'nom' => 'bail|required|min:6|max:60|regex:/^[a-zA-Z0-9éè\s\']*$/',
            'description' =>  'bail|required|min:10|max:255|regex:/^[a-zA-Z0-9éè\s\'\.\,]*$/|',
        ]);
        //verify if the name doesn't already correspond to an existing UE for that filiere
        $corresponding_ue = EducationalUnit::where('libelle_ue',$validatedRequest['nom'])
        ->where('id_filiere',$ue_to_modify->id_filiere)->first() ;
        $corresponding_filiere = Filiere::find($ue_to_modify->id_filiere);
        $nom_filiere = $corresponding_filiere->nom_filiere;
        if($corresponding_ue->id_ue != $id)
        {
            return response()->json([
                'message' => "unité d'enseignement avec le meme nom existe déja 
                pour la filière $nom_filiere "
            ]);
        }
        
        // all is set we can update in storage 
        $ue_to_modify->update([
            'libelle_ue' => $validatedRequest['nom'],
            'description'=> $validatedRequest['description']
        ]);
        return response()->json([
            'success' => 'resource updated with success'
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
        $educationalUnit = EducationalUnit::find($id);
        // allow to delete a Ue resource from strorage 
        if(!Auth::user()->can('delete',$educationalUnit))
        {
            return response()->json([
                'message' => 'unauthorized'
            ],403);
        }
        $educationalUnit->delete();
        return response()->json([
            'success' => "resource deleted with success !"
        ],202);
    }
}
