<?php

namespace App\Policies;

use App\Models\Etudiant;
use App\Models\Utilisateur;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class EtudiantPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(Utilisateur $utilisateur)
    {
        //determine wheter the user can see all students 
        // basically only an admin can have access to this page 
        return $utilisateur->role === 'Admin' || $utilisateur->role ==='admin' || $utilisateur->isTeacher()
                            ? Response::allow()
                            : Response ::deny('u must be an administrator') ;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @param  \App\Models\Etudiant  $etudiant
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Utilisateur $utilisateur, Etudiant $etudiant)
    {
        //only the admin or the student corresponding to that $etudiant
        //resource can view it 
        return $utilisateur->role === 'Admin' || $utilisateur->role ==='admin' 
        || $utilisateur->id_utilisateur === $etudiant->id_utilisateur ? true :false ;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(Utilisateur $utilisateur)
    {
        //only admin can create a new Etudiant resource 
        return $utilisateur->role === 'Admin' || $utilisateur->role === 'admin' 
                            ? Response::allow()
                            : Response::deny('u must be an administrator') ;

    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @param  \App\Models\Etudiant  $etudiant
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Utilisateur $utilisateur, Etudiant $etudiant)
    {
        //only the admin or the user corresponding to the $etudiant resource can update it 
        return $utilisateur->role === "Admin" || $utilisateur->role === 'admin' || 
        $utilisateur->id_utilisateur === $etudiant->id_utilisateur ;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @param  \App\Models\Etudiant  $etudiant
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Utilisateur $utilisateur, Etudiant $etudiant)
    {
        //only the admin can delete the $etudiant resource
        return $utilisateur->role === 'admin' || $utilisateur->role === ' Admin' ;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @param  \App\Models\Etudiant  $etudiant
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(Utilisateur $utilisateur, Etudiant $etudiant)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @param  \App\Models\Etudiant  $etudiant
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(Utilisateur $utilisateur, Etudiant $etudiant)
    {
        //
    }
}
