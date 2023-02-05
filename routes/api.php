<?php

use App\Models\Cours;
use App\Models\Etudiant;
use App\Models\Enseignant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EtudiantController;
use App\Http\Controllers\EnseignantController;
use App\Http\Controllers\UtilisateurController;
use App\Http\Controllers\CoursController;
use App\Http\Controllers\FiliereController;
use App\Http\Controllers\UeController;
use App\Http\Controllers\EdtController;
use App\Http\Controllers\AuthController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});




// handling students 
Route::group(['middleware' => ['auth:sanctum']],function(){
    
    //route that allows a students to get his grades 
    Route::get('/students/mesnotes',[EtudiantController::class,'getMyGrades']);
    //route that allows a student to check all his courses 
    Route::get('/students/mescours',[EtudiantController::class,'getMyCourses']);
    // route that allows a student to get his EDT 
    Route::get('/students/edt',[EtudiantController::class,'getMySchedule']);
    // handling students
    Route::resource('students', EtudiantController::class);

    //get all courses for a given teacher 
    Route::get('/enseignants/mescours',[EnseignantController::class,'getMyCourses']);
    // get all students for a given teacher course 
    Route::get('/enseignants/{coursId}/mystudents',[EnseignantController::class,"getMyStudents"]);
    // allows a teacher to create a course, so it renders a view with form 
    Route::get('/enseignants/addcourse',[CoursController::class,'create']);
    // handles creating the course by teacher
    Route::post('/enseignants/addcourse',[CoursController::class,'store']);
    // returns a form to edit a given course by teacher 
    Route::get('/enseignants/mescours/{coursId}/edit',[CoursController::class,'edit']);
    // handles modifying a course resource in storage 
    Route::post('/enseignants/mescours/{coursId}',[CoursController::class,'update']);
    // handles deleting a cours resource by teacher 
    Route::delete('/enseignants/mescours/{coursId}',[CoursController::class,'destroy']);
    // returns a form to add a student to a given course 
    Route::get('/enseignants/{coursId}/addstudent',[EnseignantController::class,"addStudent"]);
    // handles adding a student to a given course by a teacher 
    Route::post('/enseignants/{coursId}/addstudent',[EnseignantController::class,"addStudentToCourse"]);
    // displays a form to teacher so it can give grade to a student for given course
    Route::get('/enseignants/{coursId}/{studentId}/addgrade',[EnseignantController::class,"assignGrade"]);
    // allows a teacher to give grades to a student for a given course 
    Route::post('/enseignants/{coursId}/{studentId}/addgrade',[EnseignantController::class,"storeGrade"]);
    // handling enseignants 
    Route::resource('enseignants',EnseignantController::class);
    // route that displays form for creating a new edt resource 
    Route::get('/newedt',[EdtController::class,"create"]);
    // route that allows the creation of a an edt resource 
    Route::post('/newedt',[EdtController::class,'store']);

    //route that dispalys a form so  new user can be created
    // the user is either a teacher or student  
    Route::get('/admin/newUser',[UtilisateurController::class,'create']);
    // this route will redirect after submission to one 
    // of the routes for creating a teacher or student with post method

    // allows an admin to check all courses 
    Route::get('/admin/allcourses',[CoursController::class,'index']);
    // allows an admin to consult all filieres 
    Route::get('/admin/filieres',[FiliereController::class,'index']);
    // allows an admin to delete a filiere 
    Route::delete('/admin/filieres/{filiereId}',[FiliereController::class,'destroy']);
    // allows an admin to create a new filiere 
    Route::post('/admin/newfiliere',[FiliereController::class,'store']);

    //Route that returns form for creating a new UE resource
    Route::get('/newue',[UeController::class,'create']);
    // route that allows creationg a new UE resource 
    Route::post('/newue',[UeController::class,'store']);

    // handles logging out a user 
    Route::post('/logout',[AuthController::class,'logout']);

});






// -------- PUBLIC ROUTES --------------//

//authentication routes 
// returns a view with a form for authentication
Route::get('/login',[AuthController::class,'login']);
// handles the submit of the athentication form
Route::post('/login',[AuthController::class,'authenticate']);

