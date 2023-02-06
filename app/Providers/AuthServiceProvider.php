<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Auth\Access\Response;
use App\Models\Utilisateur;
use App\Models\Etudiant;
use App\Models\Cours;
use App\Models\EtudiantCours;
use App\Policies\EtudiantPolicy;
use App\Models\Enseignant;
use App\Policies\EnseignantPolicy;
use App\Models\EducationalUnit;
use App\Policies\EducationalUnitPolicy;


class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        Etudiant::class   => EtudiantPolicy::class,
        Enseignant::class => EnseignantPolicy::class,
        EducationalUnit::class => EducationalUnitPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Gate that allows a student to view their grades 
        // typically only students can have access to this page 
        Gate::define('view-grades',function(Utilisateur $user){
            return $user->isStudent()
                            ? Response::allow()
                            : Response::deny('u must be a student');
        });
        //Gate that allows a student to check his courses 
        // this route must be only accessible by students 
        Gate::define('view-courses-student',function(Utilisateur $user){
            return $user->isStudent()
                            ? Response::allow()
                            : Response::deny('u must be a student');
            
        });
        //Gate that allows a student to check his EDT 
        // this route is only accessible by students 
        Gate::define('view-edt',function(Utilisateur $user){
            return $user->isStudent()
                            ? Response::allow()
                            : Response::deny();
        });

        // Gate that allows a teacher to view his courses 
        // this route is only accessible by teachers 
        Gate::define('view-courses-teacher',function(Utilisateur $user){
            return $user->isTeacher()
                                ? Response::allow()
                                : Response::deny('u must be a teacher') ;
        });

        //Gate that allows a teacher to view his students for a given course
        // this route is only accessible by teachers 
        Gate::define('view-students-for-my-course',function(Utilisateur $user, $coursId){
            // first verify if the user is a teacher
            $firstCheck = $user->isTeacher() ? true : false;
            // check if the course belongs to the teacher
            $course = Cours::find($coursId);
            if(!$course)
            {
                return false;
            }
            $secondCheck = $user->id_utilisateur === $course->id_enseignant ? true :false ;
            // return a booleana value 
            return ($firstCheck && $secondCheck) ? true :false ;

        });

        // Gate that allows a teacher to add a student to a given course of his courses
        // this route is only accessible to teachers 
        Gate::define('add-student-to-my-course',function(Utilisateur $user,$coursId){
            // first check if the user is a teacher 
            $firstCheck = $user->isTeacher() ? true : false ;
            // verify if the course belongs to the teacher 
            $course = Cours::find($coursId);
            if(!$course)
            {
                return false;
            }
            $secondCheck = $course->id_enseignant === $user->id_utilisateur ? true : false;
            // return a boolean response 
            return ($firstCheck && $secondCheck) ? true :false;
        });

        // Gate that allows a teacher to assign grade to a student for one of his courses
        // this route is only accessible by teachers 
        Gate::define('assign-grade',function(Utilisateur $user, $studentId, $coursId){
            // verify if the user is a teacher 
            $firstCheck = $user->isTeacher() ? true : false;
            // verify if the course belongs to the teacher 
            $course = Cours::find($coursId);
            if(!$course)
            {
                // if no course is found return false
                return false;
            }
            $secondCheck = $course->id_enseignant === $user->id_utilisateur ? true : false;
            // check if the student exists 
            $studentToNote = Etudiant::find($studentId);
            if(!$studentToNote)
            {
                //if no student is found 
                return false;
            }
            //check if the student is having that course 
            $studentCourse = EtudiantCours::where('cours_id',$coursId)
            ->where('etudiant_id',$studentId)->first();
            if(!$studentCourse)
            {
                // the student doesn't have the course 
                return false;
            }
            //return a boolean value 
            return $firstCheck && $secondCheck && $studentCourse ? true : false;
        });

        //Gate that allows an admin to viewAny filiere (all filieres)
        Gate::define('view-filieres',function(Utilisateur $user){
            //determine if the user is an administrator
            return $user->role == 'admin' || $user->role == 'Admin';
        });

        //Gate that allows an admin to create new filiere resource 
        Gate::define('create-filiere',function(Utilisateur $user){
            //determine if the user is an administrator
            return $user->role == 'admin' || $user->role == 'Admin';
        });

        //Gate that allows an admin to delete a filiere resource
        Gate::define('delete-filiere',function(Utilisateur $user){
            //determine if the user is an administrator
            return $user->role == 'admin' || $user->role == 'Admin';
        });

    }
}
