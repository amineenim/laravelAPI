<?php

namespace App\Policies;

use App\Models\Cours;
use App\Models\Utilisateur;
use Illuminate\Auth\Access\HandlesAuthorization;

class CoursPolicy
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
        //only an admin can view all courses 
        return $utilisateur->role =='admin' || $utilisateur->role == 'Admin';
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @param  \App\Models\Cours  $cours
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Utilisateur $utilisateur, Cours $cours)
    {
        //only the teacher who owns the course resource can view it 
        // only accessible for teachers 
        $firstCheck = $utilisateur->isTeacher() ? true :false;
        $secondCheck = $utilisateur->id_utilisateur === $cours->id_enseignant;
        return $firstCheck && $secondCheck ? true : false ;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(Utilisateur $utilisateur)
    {
        //only teacher can create course 
        return $utilisateur->isTeacher() && $utilisateur->role != 'admin';
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @param  \App\Models\Cours  $cours
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Utilisateur $utilisateur, Cours $cours)
    {
        //only the teacher owner of the course can update the course resource
        //this route is only accessible to teachers 
        $firstCheck = $utilisateur->isTeacher() ? true : false ;
        $secondCheck = $utilisateur->id_utilisateur === $cours->id_enseignant ;
        return $firstCheck && $secondCheck ? true : false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @param  \App\Models\Cours  $cours
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Utilisateur $utilisateur, Cours $cours)
    {
        //only the teacher who owns the course model can delete it 
        $firstCheck = $utilisateur->isTeacher() ? true :false;
        $secondCheck = $utilisateur->id_utilisateur === $cours->id_enseignant;
        return $firstCheck && $secondCheck ? true : false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @param  \App\Models\Cours  $cours
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(Utilisateur $utilisateur, Cours $cours)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\Utilisateur  $utilisateur
     * @param  \App\Models\Cours  $cours
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(Utilisateur $utilisateur, Cours $cours)
    {
        //
    }
}
