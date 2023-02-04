<?php

namespace App\Policies;

use App\Models\Enseignant;
use App\Models\Utilisateur;
use Illuminate\Auth\Access\HandlesAuthorization;

class EnseignantPolicy
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
        //only an admin can view all enseignants resources 
        return $utilisateur->role == 'admin' || $utilisateur->role == 'Admin' ? true :false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @param  \App\Models\Enseignant  $enseignant
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Utilisateur $utilisateur, Enseignant $enseignant)
    {
        //only a teacher or an admin have the ability to view a 'enseignant' resource
        $firstCheck = $utilisateur->role =='admin' || $utilisateur->role =='Admin';
        $secondCheck = $utilisateur->id_utilisateur === $enseignant->id_utilisateur;
        return $firstCheck || $secondCheck ? true : false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(Utilisateur $utilisateur)
    {
        //only admin users can create a teacher resource
        return $utilisateur->role == 'admin' || $utilisateur->role == 'Admin';
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @param  \App\Models\Enseignant  $enseignant
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Utilisateur $utilisateur, Enseignant $enseignant)
    {
        //only an admin can change data about a teacher resource 
        // or the teacher himself representing that resource 
        $firstCheck = $utilisateur->role == 'admin' || $utilisateur->role == 'Admin';
        $secondCheck = $utilisateur->id_utilisateur === $enseignant->id_utilisateur ;
        return $firstCheck || $secondCheck ? true : false ;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @param  \App\Models\Enseignant  $enseignant
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Utilisateur $utilisateur, Enseignant $enseignant)
    {
        //only an admin can delete a teacher resource 
        return $utilisateur->role == 'admin' || $utilisateur->role == 'Admin';
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @param  \App\Models\Enseignant  $enseignant
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(Utilisateur $utilisateur, Enseignant $enseignant)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @param  \App\Models\Enseignant  $enseignant
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(Utilisateur $utilisateur, Enseignant $enseignant)
    {
        //
    }
}
