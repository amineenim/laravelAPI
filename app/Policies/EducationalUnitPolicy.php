<?php

namespace App\Policies;

use App\Models\EducationalUnit;
use App\Models\Utilisateur;
use Illuminate\Auth\Access\HandlesAuthorization;

class EducationalUnitPolicy
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
        //only an admin or teacher(directeur etudes) can view all ue resources 
        $firstCheck = $utilisateur->isTeacher();
        if(!$firstCheck)
        {
            return false;
        }
        $isAdmin = $utilisateur->role == 'admin' || $utilisateur->role == 'Admin';
        $secondCheck = $utilisateur->isDirecteurEtudes();
        return $firstCheck || $isAdmin || $secondCheck ? true : false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @param  \App\Models\EducationalUnit  $educationalUnit
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Utilisateur $utilisateur, EducationalUnit $educationalUnit)
    {
        //only an admin or teacher (directeur etudes) can view an Ue resource 
        $firstCheck = $utilisateur->isTeacher();
        if(!$firstCheck)
        {
            return false;
        }
        $isAdmin = $utilisateur->role == 'admin' || $utilisateur->role == 'Admin';
        $secondCheck = $utilisateur->isDirecteurEtudes();
        return $isAdmin || $secondCheck ? true : false;

    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(Utilisateur $utilisateur)
    {
        //only an admin or teacher(directeur etudes) can create ue resources 
        $firstCheck = $utilisateur->isTeacher();
        if(!$firstCheck)
        {
            return false;
        }
        $isAdmin = $utilisateur->role == 'admin' || $utilisateur->role == 'Admin';
        $secondCheck = $utilisateur->isDirecteurEtudes();
        return $isAdmin || $secondCheck ? true : false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @param  \App\Models\EducationalUnit  $educationalUnit
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Utilisateur $utilisateur, EducationalUnit $educationalUnit)
    {
        //only the admin or the teacher (directeur etudes) can update a ue resource
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
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @param  \App\Models\EducationalUnit  $educationalUnit
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Utilisateur $utilisateur, EducationalUnit $educationalUnit)
    {
        //only the admin can delete a Ue resource 
        $firstCheck = $utilisateur->isTeacher();
        if(!$firstCheck)
        {
            return false;
        }
        $isAdmin = $utilisateur->role == 'admin' || $utilisateur->role == 'Admin';
        return $isAdmin;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @param  \App\Models\EducationalUnit  $educationalUnit
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(Utilisateur $utilisateur, EducationalUnit $educationalUnit)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @param  \App\Models\EducationalUnit  $educationalUnit
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(Utilisateur $utilisateur, EducationalUnit $educationalUnit)
    {
        //
    }
}
