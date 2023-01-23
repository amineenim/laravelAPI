<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\Utilisateur;

class UtilisateurController extends Controller
{
    public function create()
    {
        //displays a form to create a new User
    }
    // this function allows to create a new User ressource 
    public function store(Request $request)
    {
        //first we should validate the incoming request 
        
        $validatedRequest = $request->validate(
            [
                'role' => 'in:User,user,admin,Admin|required',
                'nom'  => 'required|min:3|max:255',
                'prenom' => 'required|min:3|max:255',
                'email'  => 'required|email|unique:utilisateurs,email',
                'password' => 'required|alpha_dash|min:6|max:12',
                'tel'    => 'digits:10|required'
            ]
        );
        //now if the request is validated, we only need to hash the password before
        // storing it to database
        $hashedPassword = Hash::make($validatedRequest['password']);
        $newUser = Utilisateur::create([
            'role' => $validatedRequest['role'],
            'nom' => $validatedRequest['nom'],
            'prenom' => $validatedRequest['prenom'],
            'email' => $validatedRequest['email'],
            'password' => $hashedPassword,
            'tel'   => $validatedRequest['tel'],
        ]);
        return response('User created successefully',201);

    }
}
