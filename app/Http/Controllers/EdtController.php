<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Filiere;
use App\Models\EducationalUnit;
use App\Models\Cours;
use App\Models\Edt;
use App\Models\Utilisateur;
use Carbon\Carbon;

class EdtController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //displays for a teacher "filieres" and "cours" so it can build a new 
        // edt event record 
        $filieres = Filiere::all();
        $mydata = [];
        // loop over the array $filieres and foreach filiere grab ue belonging to it 
        foreach($filieres as $filiere)
        {
            //initilise an empty array to store ues for each filiere 
            $ues_for_filiere = [];
            $nom_filiere = $filiere->nom_filiere;
            $niveau_filiere = $filiere->niveau;
            $ues_filiere = EducationalUnit::where('id_filiere',$filiere->id_filiere)->get();
            //loop over ues and grab name and corresponding courses 
            foreach($ues_filiere as $ue)
            {
                $nom_ue = $ue->libelle_ue;
                //initialise an empty array to store courses for a given ue
                $courses_for_ue = [];
                $cours_ue = Cours::where('id_ue',$ue->id_ue)->get();
                foreach($cours_ue as $cours)
                {
                    $nom_cours = $cours->nom_cours;
                    $enseignant_cours = $cours->id_enseignant;
                    // get data about the teacher of course 
                    $user = Utilisateur::find($enseignant_cours);
                    $nom_prof = $user->nom." ".$user->prenom;
                    $contact_prof = $user->email;
                    $data_enseignant = (object)[
                        'fullName' => $nom_prof,
                        'contact'  => $contact_prof
                    ];
                    $coursData = (object)[
                        'nom_cours' => $nom_cours,
                        'enseignant' => $data_enseignant
                    ];
                    array_push($courses_for_ue, $coursData);
                }
                $ueData = (object)[
                    $nom_ue => $courses_for_ue
                ];
                array_push($ues_for_filiere, $ueData);
            }
            $data = (object)[
                $nom_filiere => $ues_for_filiere,
                'niveau'     => $niveau_filiere
            ];
            array_push($mydata,$data);
        }
        return response()->json([
            'data' => $mydata
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //get the current year 
        $actual_year = Carbon::now()->format('Y');
        $actual_month = Carbon::now()->format('m');
        if($actual_month <= 7)
        {
            $previous_year = Carbon::now()->format('Y') - 1;
            $first_year = $previous_year;
            $second_year = Carbon::now()->format('Y');
            $start_year = $first_year.'-09-01';
            $end_year   = $second_year.'-07-01';
        }
        else 
        {
            $next_year = Carbon::now()->format('Y') + 1 ;
            $first_year = Carbon::now()->format('Y');
            $second_year = $next_year;
            $start_year = $first_year.'-09-01';
            $end_year   = $second_year.'-07-01';
        }
        $next_year = $actual_year + 1 ;
        //this function handles validating the data and creating a new edt event in db
        //for me i'll display for the user a select list from which it can
        //select the filiere name, it receives in other list the ues for that filiere 
        // for a selected ue it gets the courses and it can enter edt 
        $validatedRequest = $request->validate([
            'filiere' => 'bail|required|exists:filieres,nom_filiere',
            'niveau'  => 'bail|required|in:L,M,D',
            'ue'  => 'bail|required|exists:ue,libelle_ue',
            'cours' => 'bail|required|exists:cours,nom_cours',
            'debut' => 'bail|required|date_format:Y-m-d H:i',
            'fin'   => 'bail|required|date_format:Y-m-d H:i|after:debut',
            'type'  => 'bail|required|in:tp,td,cours,Tp,Td,Cours'
        ]);
        $date_debut_cours = Carbon::parse($validatedRequest['debut']);
        $date_fin_cours = Carbon::parse($validatedRequest['fin']);
        if($date_debut_cours->greaterThan(Carbon::parse($start_year)) && $date_debut_cours->lessThan(Carbon::parse($end_year)))
        {
            $date_fin_formated = Carbon::parse($date_fin_cours)->format('Y-m-d');
            $date_debut_formated = Carbon::parse($date_debut_cours)->format('Y-m-d');
            //compare if we have the same d-m-Y
            if($date_debut_formated == $date_fin_formated)
            {
                //now we can store data 
         
                //grab the id of filiere based on it's name and level
                $filiere = Filiere::where('nom_filiere',$validatedRequest['filiere'])
                ->where('niveau',$validatedRequest['niveau'])->first();
                if(!$filiere)
                {
                    return response()->json([
                        'message' => 'no filiere corresponding to your data check again !'
                    ]);
                }
                $id_filiere = $filiere->id_filiere;
                //grab the course based on it's name and ue_id to which it belongs
                $ue = EducationalUnit::where('libelle_ue',$validatedRequest['ue'])
                ->where('id_filiere',$id_filiere)->first();
                if(!$ue)
                {
                    return response()->json([
                        'message' => 'no such ue for given filiere !'
                    ]);
                }
                $id_ue =$ue->id_ue;
                $course = Cours::where('nom_cours',$validatedRequest['cours'])
                ->where('id_ue',$id_ue)->first();
                $id_course = $course->id_cours;

                $newRecord = Edt::create([
                    'id_filiere' => $id_filiere,
                    'id_cours'   => $id_course,
                    'date_debut' => $validatedRequest['debut'],
                    'date_fin'   => $validatedRequest['fin'],
                    'type_cours' => $validatedRequest['type']
                ]);
                if(!$newRecord)
                {
                    return response()->json([
                        'message' => 'Oops, something went wrong !'
                    ]);
                }
                return response()->json([
                    'message' => 'created succesefully !'
                ],201);
            }
            else 
            {
                return response()->json([
                    'message' => 'the start and end of course must be the same day ! invalid fin date'
                ]);
            }
        }
        else 
        {
            return response()->json([
                'meesage' => 'date invalide, selectionnez la date entre '.$start_year." et ".$end_year
            ]);
        }
        
        
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
