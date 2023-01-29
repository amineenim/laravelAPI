<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Filiere;
use App\Models\EducationalUnit;

class UeController extends Controller
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
        //this function handles creating a new educational unit resource
        $validatedRequest = $request->validate([
            'nom_filiere' => 'bail|required|exists:filieres,nom_filiere',
            'niveau'      => 'bail|required|in:L,M,D|',
            'nom_ue'      => 'bail|required|regex:/^[a-zA-Z0-9\s\']*$/|min:6|max:60|unique:ue,libelle_ue',
            'description' =>  'bail|required|min:10:max:255|regex:/^[a-zA-Z0-9\s\'\.\,]*$/|'
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
        //get id of filiere 
        $filiereId = $existingFiliere->id_filiere;
        //create UE record 
        $newRecord = EducationalUnit::create([
            'id_filiere' => $filiereId,
            'libelle_ue' => $validatedRequest['nom_ue'],
            'description' => $validatedRequest['description']
        ]);
        return response()->json([
            'message' => 'educational unit created successefully'
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
