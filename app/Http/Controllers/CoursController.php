<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Enseignant;
use App\Models\EducationalUnit;
use Illuminate\Support\Facades\Auth;
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
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $enseignantId = Auth::user()->id_utilisateur;
        //first verify if the passed id in URL corresponds to a teacher 
        $enseignant = Enseignant::find($enseignantId);
        if(!$enseignant)
        {
            return response()->json(
                ['message' => 'you are not allowed to create course !']
            );
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
        $enseignantId = Auth::user()->id_utilisateur;
        //
        $enseignant = Enseignant::find($enseignantId);
        if(!$enseignant)
        {
            return response()->json(
                ['message' => 'you are not allowed to create course !']
            );
        }
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
