<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\Utilisateur;
use App\Models\Filiere;
use App\Models\Enseignant;

class UtilisateurController extends Controller
{
    public function create()
    {
        //displays a form to create a new User
        return response()->json([
            'message' => 'here you can create a new user teacher/student'
        ]);
    }
    // this function allows to create a new User ressource 
    public function store(Request $request)
    {
        //this function determines weither we are creating a new teacher
        //or student and redirect to the right URL
        
    }

 
    
    // this function is only accessible to an admin 
    // it can delete a filiere
    // when a filiere is deleted all it's students must be deleted
    

    
    
}
