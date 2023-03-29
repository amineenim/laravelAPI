<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Filiere;
use App\Models\EducationalUnit;
use Illuminate\Support\Facades\Auth;
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
        //verify if the user is authorized 
        if(!Auth::user()->can('viewAny',Edt::class))
        {
            return response()->json([
                'message' => 'unauthorized action'
            ]);
        }
        $edtEvents = Edt::all();
        // loop over the array of objects 
        $data = [];
        foreach($edtEvents as $edt)
        {
            $filiere = Filiere::find($edt->id_filiere);
            $nom_filiere = $filiere->nom_filiere;
            $niveau = $filiere->niveau;
            $nom_cours = Cours::find($edt->id_cours)->nom_cours;
            $debut = $edt->date_debut;
            $fin = $edt ->date_fin;
            $type_cours = $edt->type_cours;
            $data_edt = (object)[
                'filiere' => $nom_filiere,
                'niveau'  => $niveau,
                'cours'   => $nom_cours,
                'debut'   => $debut,
                'fin'     => $fin,
                'type'    => $type_cours
            ];
            array_push($data,$data_edt);
        }
        
        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //verify if the authenticated user is authorized 
        if(! Auth::user()->can('create',Edt::class))
        {
            return response()->json([
                'message' => 'unauthorized action'
            ],403);
        }
        //displays for a teacher "filieres" and "cours" so it can build a new 
        // edt event record 
        $filieres = Filiere::all();
        $mydata = [];
        // loop over the array $filieres and foreach filiere grab ue belonging to it 
        foreach($filieres as $filiere)
        {
            //initialise an empty array to store ues for each filiere 
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
        //verify if the authenticated user is authorized 
        if(! Auth::user()->can('create',Edt::class))
        {
            return response()->json([
                'message' => 'unauthorized action'
            ],403);
        }
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
            'type'  => 'bail|required|in:Tp,Td,Cours'
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
                //verify if all events are between 8:00 and 18:30
                $start_time = $date_debut_cours->format('H:i');
                $end_time = $date_fin_cours->format('H:i');
                if(!Carbon::parse($start_time)->isBetween('08:00','18:30') || !Carbon::parse($end_time)->isBetween('08:00','18:30'))
                {
                    return response()->json([
                        'message' => 'le temps choisi doit etre entre 8:00 et 18:30'
                    ]);
                }
         
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
                //verify if the corresponding time is not already occupied for the given "filiere"
                // get events for that day and filiere
                $edtEvents = Edt::where('id_filiere',$id_filiere)->
                where('date_debut','>=',$date_debut_formated.' 08-00-00')->
                where('date_fin','<=',$date_fin_formated.' 18-30-00')->get();
                //loop over edtevents and verify if the given date is between the start and end of any event of the day
                foreach($edtEvents as $edtEvent)
                {
                    
                    if($date_debut_cours->isBetween(Carbon::parse($edtEvent->date_debut),Carbon::parse($edtEvent->date_fin)))
                    {
                        return response()->json([
                            'message' => 'date de début de cours non valide, elle correspond à la séance de '.$edtEvent->date_debut.' à '.$edtEvent->date_fin
                        ]);
                    }
                    if($date_fin_cours->isBetween(Carbon::parse($edtEvent->date_debut),Carbon::parse($edtEvent->date_fin)))
                    {
                        return response()->json([
                            'message' => 'date de fin de cours non valide, elle correspond à la séance de '.$edtEvent->date_debut.' à '.$edtEvent->date_fin
                        ]);
                    }
                    if(Carbon::parse($edtEvent->date_debut)->isBetween($date_debut_cours,$date_fin_cours))
                    {
                        return response()->json([
                            'message' => 'date non valide, elle correspond déja à une séance de '.$edtEvent->date_debut.' à '.$edtEvent->date_fin
                        ]);
                    }
                    if(Carbon::parse($edtEvent->date_fin)->isBetween($date_debut_cours,$date_fin_cours))
                    {
                        return response()->json([
                            'message' => 'date non valide, elle correspond déja à une séance de '.$edtEvent->date_debut.' à '.$edtEvent->date_fin
                        ]);
                    }
                }
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
                    'success' => 'EDT event created succesefully !'
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
        $edtEvent = Edt::find($id);
        //verify if the user is authorized to view this resource
        if(!Auth::user()->can('view',$edtEvent))
        {
            if(Auth::user()->role == 'admin' || Auth::user()->role == 'Admin')
            {
                return response()->json([
                    'message' => 'resource not found'
                ],404);
            }
            return response()->json([
                'message' => 'unauthorized action'
            ],403);
        }
        //returns a view displaying a specific edt event 
        
        $filiere = Filiere::find($edtEvent->id_filiere);
        $nom_filiere = $filiere->nom_filiere;
        $niveau = $filiere->niveau;
        $cours = Cours::find($edtEvent->id_cours);
        $nom_cours = $cours->nom_cours;
        $id_enseignant = $cours->id_enseignant;
        $prof = Utilisateur::find($id_enseignant);
        $nom_prof =$prof->nom." ".$prof->prenom ;
        $email = $prof->email;
        $contact = $prof->tel;
        $data = (object)[
            'evenement' => $edtEvent->type_cours,
            'debut'     => $edtEvent->date_debut,
            'fin'       => $edtEvent->date_fin,
            'filiere'   => $nom_filiere,
            'niveau'    => $niveau,
            'cours'     => (object)[
                'nom' => $nom_cours,
                'professeur' => $nom_prof,
                'email' => $email,
                'telephone' => $contact
            ]
        ];
        return response()->json([
            'message' => 'here will be displayed a view to show an edt resource',
            'data' => $data
        ]);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $edtEvent = Edt::find($id);
        //verify if the user is authorized to edit this resource
        if(!Auth::user()->can('update',$edtEvent))
        {
            return response()->json([
                'message' => 'unauthorized action'
            ],403);
        }
        //returns a view displaying a specific edt event 
        
        $filiere = Filiere::find($edtEvent->id_filiere);
        $nom_filiere = $filiere->nom_filiere;
        $niveau = $filiere->niveau;
        $cours = Cours::find($edtEvent->id_cours);
        $nom_cours = $cours->nom_cours;
        $data = (object)[
            'filiere'   => $nom_filiere,
            'niveau'    => $niveau,
            'cours'     => $nom_cours,
            'evenement' => $edtEvent->type_cours,
            'debut'     => $edtEvent->date_debut,
            'fin'       => $edtEvent->date_fin,
        ];
        return response()->json([
            'message' => 'here will be displayed a form to edit an edt resource',
            'data' => $data
        ]);

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
        $edtEvent = Edt::find($id);
        //verify if the user is authorized to edit this resource
        if(!Auth::user()->can('update',$edtEvent))
        {
            return response()->json([
                'message' => 'unauthorized action'
            ],403);
        }
        // validate the data 
        $validatedRequest = $request->validate([
            'filiere' => 'bail|required|exists:filieres,nom_filiere',
            'niveau'  => 'bail|required|in:L,M,D',
            'ue'      => 'bail|required|exists:ue,libelle_ue',
            'cours'   => 'required|exists:cours,nom_cours',
            'debut'   => 'required|date_format:Y-m-d H:i',
            'fin'     => 'required|date_format:Y-m-d H:i|after:debut',
            'type'    => 'required|in:cours,Cours,td,Td,tp,Tp'
        ]);
        // get the actual scholar year (2022/2023)
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
        // now that request data is valid we must apply further validation to dates 
        $date_debut_cours = Carbon::parse($validatedRequest['debut']);
        $date_fin_cours = Carbon::parse($validatedRequest['fin']);
        if($date_debut_cours->greaterThan(Carbon::parse($start_year)) && $date_debut_cours->lessThan(Carbon::parse($end_year)))
        {
            $date_fin_formated = Carbon::parse($date_fin_cours)->format('Y-m-d');
            $date_debut_formated = Carbon::parse($date_debut_cours)->format('Y-m-d');
            //compare if we have the same d-m-Y
            if($date_debut_formated == $date_fin_formated)
            {                
                //verify if all events are between 8:00 and 18:30
                $start_time = $date_debut_cours->format('H:i');
                $end_time = $date_fin_cours->format('H:i');
                if(!Carbon::parse($start_time)->isBetween('08:00','18:30') || !Carbon::parse($end_time)->isBetween('08:00','18:30'))
                {
                    return response()->json([
                        'message' => 'le temps choisi doit etre entre 8:00 et 18:30'
                    ]);
                }
         
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
                //verify if the corresponding time is not already occupied for the given "filiere"
                // get events for that day and filiere
                $dayEvents = Edt::where('id_filiere',$id_filiere)->
                where('date_debut','>=',$date_debut_formated.' 08-00-00')->
                where('date_fin','<=',$date_fin_formated.' 18-30-00')->get();
                //exclude the edt event corresponding to the one being updated 
                $newEdtEvents = [];
                foreach($dayEvents as $dayEvent)
                {
                    if($dayEvent->id_edt != $id)
                    {
                        array_push($newEdtEvents,$dayEvent);
                    }
                }
                //loop over edtevents and verify if the given date is between the start and end of any event of the day
                foreach($newEdtEvents as $dayEvent)
                {
                    if($date_debut_cours->isBetween(Carbon::parse($dayEvent->date_debut),Carbon::parse($dayEvent->date_fin)))
                    {
                        return response()->json([
                            'message' => 'date de début de cours non valide, elle correspond à la séance de '.$edtEvent->date_debut.' à '.$edtEvent->date_fin
                        ]);
                    }
                    if($date_fin_cours->isBetween(Carbon::parse($dayEvent->date_debut),Carbon::parse($dayEvent->date_fin)))
                    {
                        return response()->json([
                            'message' => 'date de fin de cours non valide, elle correspond à la séance de '.$edtEvent->date_debut.' à '.$edtEvent->date_fin
                        ]);
                    }
                    if(Carbon::parse($dayEvent->date_debut)->isBetween($date_debut_cours,$date_fin_cours))
                    {
                        return response()->json([
                            'message' => 'date non valide, elle correspond déja à une séance de '.$edtEvent->date_debut.' à '.$edtEvent->date_fin
                        ]);
                    }
                    if(Carbon::parse($dayEvent->date_fin)->isBetween($date_debut_cours,$date_fin_cours))
                    {
                        return response()->json([
                            'message' => 'date non valide, elle correspond déja à une séance de '.$edtEvent->date_debut.' à '.$edtEvent->date_fin
                        ]);
                    }
                }
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

                $edtEvent->update([
                    'id_filiere' => $id_filiere,
                    'id_cours'   => $id_course,
                    'date_debut' => $validatedRequest['debut'],
                    'date_fin'   => $validatedRequest['fin'],
                    'type_cours' => $validatedRequest['type']
                ]);
                return response()->json([
                    'message' => 'EDT event updated succesefully !'
                ],200);
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $edtToDelete = Edt::find($id);
        //verify if the authenticated user is authorized
        if(!Auth::user()->can('delete',$edtToDelete))
        {
            return response()->json([
                'message' => 'unauthorized action'
            ],403);
        }
        $edtToDelete->delete();
        return response()->json([
            'message' => 'deleted successfully'
        ],403);
    }
}
