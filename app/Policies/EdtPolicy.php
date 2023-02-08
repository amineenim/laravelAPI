<?php

namespace App\Policies;

use App\Models\Edt;
use App\Models\Utilisateur;
use Illuminate\Auth\Access\HandlesAuthorization;

class EdtPolicy
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
        //only the admin and teacher 'directeur etudes' can view all edt resources
        $firstCheck = $utilisateur->isTeacher();
        if(!$firstCheck)
        {
            return false;
        }
        $isAdmin = $utilisateur->role == 'admin' || $utilisateur->role == 'Admin';
        $secondCheck = $utilisateur->isDirecteurEtudes();
        return $isAdmin || $secondCheck ? true : false ;

    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @param  \App\Models\Edt  $edt
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Utilisateur $utilisateur, Edt $edt)
    {
        //only admin and teacher 'directeur etudes' can view the edt event resource
        $firstCheck = $utilisateur->isTeacher();
        if(!$firstCheck)
        {
            return false;
        }
        $isAdmin = $utilisateur->role == 'admin' || $utilisateur->role == 'Admin';
        $secondCheck = $utilisateur->isDirecteurEtudes();
        return $isAdmin || $secondCheck ? true : false ;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(Utilisateur $utilisateur)
    {
        //only a teacher 'directeur etudes' can create an Edt resource
        $firstCheck = $utilisateur->isTeacher();
        if(!$firstCheck)
        {
            return false;
        }
        // check if the teacher has role 'directeur etudes'
        $secondCheck = $utilisateur->isDirecteurEtudes();
        return $secondCheck;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @param  \App\Models\Edt  $edt
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Utilisateur $utilisateur, Edt $edt)
    {
        //only the teacher 'directeur etudes' can update a given edt resource 
        $firstCheck = $utilisateur->isTeacher();
        if(!$firstCheck){
            return false;
        }
        $secondCheck = $utilisateur->isDirecteurEtudes();
        return $secondCheck;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @param  \App\Models\Edt  $edt
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Utilisateur $utilisateur, Edt $edt)
    {
        //only a teacher 'directeur etudes' can delete a edt resource
        $firstCheck = $utilisateur->isTeacher();
        if(!$firstCheck)
        {
            return false;
        }
        $secondCheck = $utilisateur->isDirecteurEtudes();
        return $secondCheck;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @param  \App\Models\Edt  $edt
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(Utilisateur $utilisateur, Edt $edt)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @param  \App\Models\Edt  $edt
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(Utilisateur $utilisateur, Edt $edt)
    {
        //
    }
}
