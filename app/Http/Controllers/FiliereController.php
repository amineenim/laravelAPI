<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Filiere;
use App\Models\Enseignant;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Gate;

class FiliereController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //verify if the user is authorized to view all resources filiere 
        if(!Gate::allows('view-filieres'))
        {
            return response()->json([
                'message' => 'not authorized'
            ],403);
        }
        $filieres = Filiere::all() ;
        //loop over $filieres array and for each grab the name of the responsable teacher
        //initialise an empty array to hold data about all filieres
        $filieresWithData = [];
        foreach($filieres as $filiere)
        {
            $nom = $filiere->nom_filiere;
            $description = $filiere->description;
            $niveau = $filiere->niveau;
            $nombre_annees = $filiere->nombre_annee;
            $responsable = Enseignant::find($filiere->id_responsable);
            $responsabilite = $responsable->responsabilite_ens;
            $user = Utilisateur::find($filiere->id_responsable);
            $responsable_full_name = $user->nom." ".$user->prenom;
            $responsable_contact = $user->email;
            $filiereData = (object)[
                'nom_filiere' => $nom,
                'description' => $description,
                'niveau'      => $niveau,
                'nb_annees'   => $nombre_annees,
                'responsable_filiere' => $responsable_full_name,
                'contact_responsable' => $responsable_contact,
                'role_responsable'    => $responsabilite
            ];
            array_push($filieresWithData, $filiereData);
        }
        return response()->json(
            [
                'filieres' => $filieresWithData
            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //displays a vue for creation a new filiere 
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // verify if the user is authorized to perform this action
        if(!Gate::allows('create-filiere'))
        {
            return response()->json([
                'message' => 'unauthorized action'
            ],403);
        }
        //this function hadnles creating a new filiere
        $validatedRequest = $request->validate([
            'nom_filiere' => 'bail|required|regex:/^[a-zA-Z\s]*$/|min:10|max:60',
            'description' => 'bail|required|regex:/^[a-zA-Z\s\']*$/|min:20|max:255',
            'niveau'      => 'bail|required|in:L,M,D',
            'nombre_annee' => 'bail|required|integer|between:1,4',
            'email_responsable' => 'bail|required|email|exists:utilisateurs,email'
        ]);
        //verify if the validated email actually belongs to a teacher 
        $user = Utilisateur::where('email',$validatedRequest['email_responsable'])->first();
        // grab the id of user and search for it in enseignants table
        $userId = $user->id_utilisateur;
        $enseignantCollection = Enseignant::where('id_utilisateur',$userId)->get();
        if(! $enseignantCollection->contains('id_utilisateur',$userId))
        {
            return response()->json([
                'message' => 'please verify the email !'
            ]);
        }
        // now that all data is valid we can create a new record in filieres table
        $newRecord = Filiere::create([
            'nom_filiere' => $validatedRequest['nom_filiere'],
            'description' => $validatedRequest['description'],
            'niveau'      => $validatedRequest['niveau'],
            'nombre_annee' => $validatedRequest['nombre_annee'],
            'id_responsable' => $userId
        ]);

        return response()->json([
            'message' => 'filiere créee avec succès'
        ],201);
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
    public function destroy($filiereId)
    {
        // verify if the user is authorized to perform this action
        if(!Gate::allows('delete-filiere'))
        {
            return response()->json([
                'message' => 'unauthorized action'
            ],403);
        }
        $filiereToDelete = Filiere::find($filiereId);
        if(!$filiereToDelete)
        {
            return response()->json([
                'message' => 'pas de filière trouvée !'
            ],202);
        }
        $filiereToDelete->delete();
        return response()->json([
            'message' => 'filiere supprimée avec succès !'
        ],202);
    }
}
