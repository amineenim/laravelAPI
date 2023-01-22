<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EtudiantController;
use App\Http\Controllers\EnseignantController;
use App\Models\Enseignant;
use App\Models\Cours;
use App\Models\Etudiant;


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
Route::resource('students', EtudiantController::class);
//route that allows a students to get his grades 
Route::get('/students/{studentId}/notes',[EtudiantController::class,'getMyGrades']);
//route that allows a student to check all his courses 
Route::get('/students/{studentId}/mescours',[EtudiantController::class,'getMyCourses']);
// route that allows a student to get his EDT 
Route::get('/students/{studentId}/edt',[EtudiantController::class,'getMySchedule']);

// handling enseignants 
Route::resource('enseignants',EnseignantController::class);
//get all courses for a given teacher 
Route::get('/enseignants/{enseignantId}/mescours',[EnseignantController::class,'getMyCourses']);
// get all students for a given teacher course 
Route::get('/enseignants/{enseignantId}/{coursId}/mystudents',[EnseignantController::class,"getMyStudents"]);

