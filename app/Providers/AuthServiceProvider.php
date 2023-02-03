<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Auth\Access\Response;
use App\Models\Utilisateur;
use App\Models\Etudiant;
use App\Policies\EtudiantPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        Etudiant::class => EtudiantPolicy::class,
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

    }
}
