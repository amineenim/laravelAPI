<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Utilisateur;
use App\Models\Etudiant;
use App\Models\Enseignant;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // function that returns authentication form
    public function login(){
        return response()->json([
            'message' => 'here you can authenticate via a form'
        ]);
    }
    // function that handles authenticating a user
    public function authenticate(Request $request)
    {
        // validate the incoming resuqest's data 
        $validatedRequest = $request->validate([
            'email' => 'required|email',
            'password' => 'required|max:255'
        ]);
        if(!Auth::attempt($validatedRequest))
        {
            return response()->json([
                'message' => "the given credentials don't match our records"
            ]);
        }
        // retreive the user with corresponding email address
        $userToAuthenticate = Utilisateur::where('email',$validatedRequest['email'])->first();
        // get th user id 
        $id = Auth::id();
        // verify if the user belongs to either students or teachers tables 
        $correspondingStudent = Etudiant::find($id);
        $correspondingTeacher = Enseignant::find($id);
        if(!$correspondingStudent && !$correspondingTeacher)
        {
            // no teacher or student corresponds to given credentials
            return response()->json([
                'message' => 'sorry, verify your credentials, no similar data found !'
            ]);
        }
        // if we get to here, all is good 
        if($correspondingStudent)
        {
            $user = (object)[
                'id'       => $id,
                'fullName' => $userToAuthenticate->nom." ".$userToAuthenticate->prenom,
                'email'    => $userToAuthenticate->email,
                'tel'      => $userToAuthenticate->tel,
                'user'     => 'etudiant',
                'role'     => $userToAuthenticate->role  
            ];
            return response()->json([
                'user' => $user,
                'token' => Auth::user()->createToken('API Token for '.$user->fullName)->plainTextToken
            ]);
        }
        if($correspondingTeacher)
        {
            $user = (object)[
                'id'       => $id,
                'fullName' => $userToAuthenticate->nom." ".$userToAuthenticate->prenom,
                'email'    => $userToAuthenticate->email,
                'tel'      => $userToAuthenticate->tel,
                'user'     => 'enseignant',
                'role'     => $userToAuthenticate->role     
            ];
            return response()->json([
                'user' => $user,
                'token' => Auth::user()->createToken('API Token for '.$user->fullName)->plainTextToken
            ]);
        }

        
    }
    // function that handles loging out a user
    public function logout()
    {
        //delete the authenticated user Token
        Auth::user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'logged out successfully !'
        ]);
    }
}
