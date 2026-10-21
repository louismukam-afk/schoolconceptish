<?php namespace gesecole\Http\Controllers\personnel;

use gesecole\classe;
use gesecole\BiometrieImport;
use gesecole\BiometriePointage;
use gesecole\CoursEnseignant;
use gesecole\cycle;
use gesecole\dispense;
use gesecole\EmploiPermanent;
use gesecole\heure_realise;
use gesecole\HeureRealiseeEnseignant;
use gesecole\heuresPrevues;
use gesecole\horaire;
use gesecole\Http\Controllers\Controller;
use gesecole\matiere;
use gesecole\niveau;
use gesecole\NNiveau;
use gesecole\parametres;
use gesecole\personnel;
use gesecole\PlageHoraire;
use gesecole\SeanceCours;
use gesecole\taux;
use gesecole\trimestre;
use gesecole\classeMatiere;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class emploiTempsController extends Controller {

    private $jours=[1=>'Lundi',2=>'Mardi',3=>'Mercredi',4=>'Jeudi',5=>'Vendredi',6=>'Samedi',7=>'Dimanche'];

    public function __construct()
    {
        session(['big_title'=>'Gestion des ressources humaines']);
        if(session('year')==null){
            $p=parametres::whereNom('annee_scolaire')->get()->first();
            if(count($p)<=0){
                $p=new parametres();
                $p->nom='annee_scolaire';
                $p->valeur='2017-2018';
                $p->save();
            }
            session(['year'=>$p->valeur]);
        }
    }

    public function index()
    {
        $year=session('year');
        return view('personnel.emploi_du_temps_index',[
            'titre'=>'Gestion des ressources humaines',
            'stats'=>[
                'personnel'=>$this->personnelQuery()->count(),
                'enseignants'=>$this->enseignantsQuery()->count(),
                'permanents'=>$this->typePersonnelQuery('permanent')->count(),
                'vacataires'=>$this->typePersonnelQuery('vacataire')->count(),
                'classes'=>classe::count(),
                'matieres'=>matiere::count(),
                'horaires'=>PlageHoraire::count()+horaire::count(),
                'cours'=>CoursEnseignant::whereAnneeScolaire($year)->count()+dispense::whereAnneeScolaire($year)->count(),
                'heures_prevues'=>CoursEnseignant::whereAnneeScolaire($year)->sum('volume_horaire_prevu')+heuresPrevues::whereAnneeScolaire($year)->sum('total'),
                'heures_realisees'=>HeureRealiseeEnseignant::whereAnneeScolaire($year)->sum('duree_realisee')+heure_realise::whereAnneeScolaire($year)->sum('nombre'),
            ],
        ]);
    }

    public function plages()
    {
        $this->seedDefaultPlages();
        return view('personnel.plages_horaires',['titre'=>'Plages horaires','plages'=>PlageHoraire::orderBy('ordre')->orderBy('heure_debut')->get()]);
    }

    public function storePlage(Request $request)
    {
        $this->validate($request,['libelle'=>'required','heure_debut'=>'required','heure_fin'=>'required','type_plage'=>'required','type_personnel'=>'required','periode_journee'=>'required','format_plage'=>'required']);
        PlageHoraire::create($this->plageData($request));
        session(['success'=>true]);
        return redirect()->route('plages_horaires');
    }

    public function updatePlage(Request $request,$id)
    {
        $plage=PlageHoraire::find($id);
        if($plage){ $plage->update($this->plageData($request)); session(['success'=>true]); }
        return redirect()->route('plages_horaires');
    }

    public function destroyPlage($id)
    {
        $plage=PlageHoraire::find($id);
        if($plage){ $plage->delete(); session(['success'=>true]); }
        return redirect()->route('plages_horaires');
    }

    public function coursIndex(Request $request)
    {
        return view('personnel.cours_enseignants_index',$this->coursBaseData(['titre'=>'Cours enseignants','cours'=>$this->coursQuery($request)->orderBy('id_personnel')->orderBy('date_debut','desc')->get()]));
    }

    public function createCours(Request $request,$idClasse=0)
    {
        $classe=$idClasse?classe::find($idClasse):null;
        return view('personnel.cours_enseignants_form',$this->coursBaseData([
            'titre'=>'Programmer un cours enseignant',
            'cours'=>null,
            'selectedClasse'=>$idClasse,
            'selectedPersonnel'=>$request->input('id_personnel'),
            'selectedNiveau'=>$request->input('id_niveau',$classe?$classe->id_niveau:0),
            'selectedCycle'=>$request->input('id_cycle',$this->cycleIdForClasse($classe)),
            'selectedNniveau'=>$request->input('id_nniveau',0),
            'selectedSemestre'=>$request->input('semestre',1),
        ]));
    }

    public function editCours($id)
    {
        $cours=CoursEnseignant::with('seances')->find($id);
        $classe=$cours?classe::find($cours->id_classe):null;
        return view('personnel.cours_enseignants_form',$this->coursBaseData([
            'titre'=>'Modifier le cours enseignant',
            'cours'=>$cours,
            'selectedClasse'=>$cours?$cours->id_classe:0,
            'selectedPersonnel'=>$cours?$cours->id_personnel:0,
            'selectedNiveau'=>$cours&&isset($cours->id_niveau)?$cours->id_niveau:($classe?$classe->id_niveau:0),
            'selectedCycle'=>$cours&&isset($cours->id_cycle)?$cours->id_cycle:$this->cycleIdForClasse($classe),
            'selectedNniveau'=>$cours&&isset($cours->id_nniveau)?$cours->id_nniveau:0,
            'selectedSemestre'=>$cours&&isset($cours->semestre)?$cours->semestre:1,
        ]));
    }

    public function storeCours(Request $request)
    {
        $this->validateCours($request);
        $programmeError=$this->programmeError($request);
        if($programmeError) return redirect()->back()->withErrors(['id_matiere'=>$programmeError])->withInput();
        $data=$this->coursData($request);
        $error=$this->teacherConflict($data,$request->input('seances'));
        if($error) return redirect()->back()->withErrors(['seances'=>$error])->withInput();

        DB::transaction(function() use ($data,$request){
            $cours=CoursEnseignant::create($data);
            $this->syncSeances($cours,$request->input('seances'));
            $this->syncLegacyDispenses($cours);
        });
        session(['success'=>true]);
        return redirect()->route('cours_enseignants');
    }

    public function updateCours(Request $request,$id)
    {
        $this->validateCours($request);
        $programmeError=$this->programmeError($request);
        if($programmeError) return redirect()->back()->withErrors(['id_matiere'=>$programmeError])->withInput();
        $data=$this->coursData($request);
        $error=$this->teacherConflict($data,$request->input('seances'),$id);
        if($error) return redirect()->back()->withErrors(['seances'=>$error])->withInput();

        DB::transaction(function() use ($data,$request,$id){
            $cours=CoursEnseignant::find($id);
            $cours->update($data);
            SeanceCours::where('id_cours_enseignant','=',$cours->id)->delete();
            $this->syncSeances($cours,$request->input('seances'));
            $this->syncLegacyDispenses($cours);
        });
        session(['success'=>true]);
        return redirect()->route('cours_enseignants');
    }

    public function destroyCours($id)
    {
        SeanceCours::where('id_cours_enseignant','=',$id)->delete();
        CoursEnseignant::where('id','=',$id)->delete();
        session(['success'=>true]);
        return redirect()->route('cours_enseignants');
    }

    public function enseignants(Request $request)
    {
        return view('personnel.emploi_temps_enseignants',$this->coursBaseData([
            'titre'=>'Emploi du temps enseignant',
            'rows'=>$this->emploiRows($request),
            'selectedPersonnel'=>$request->input('id_personnel'),
            'selectedSemestre'=>$request->input('semestre'),
            'selectedDateDebut'=>$request->input('date_debut'),
            'selectedDateFin'=>$request->input('date_fin'),
        ]));
    }

    public function specialites(Request $request)
    {
        return view('personnel.emploi_temps_specialites',$this->coursBaseData([
            'titre'=>'Emploi du temps specialites',
            'rows'=>$this->emploiRows($request),
            'selectedSemestre'=>$request->input('semestre'),
            'selectedDateDebut'=>$request->input('date_debut'),
            'selectedDateFin'=>$request->input('date_fin'),
        ]));
    }

    public function emploisPermanents()
    {
        $this->seedDefaultPlages();
        return view('personnel.emplois_permanents',[
            'titre'=>'Emplois du temps permanents',
            'emplois'=>EmploiPermanent::with(['personnel','plage'])->orderBy('id_personnel')->orderBy('jour_semaine')->get(),
            'personnels'=>$this->typePersonnelQuery('permanent')->orderBy('nom')->get(),
            'plages'=>PlageHoraire::where('statut','=','actif')->where('type_plage','=','cours')->whereIn('type_personnel',['tous','permanent'])->orderBy('ordre')->get(),
            'jours'=>$this->jours,
        ]);
    }

    public function storeEmploiPermanent(Request $request)
    {
        $this->validate($request,['id_personnel'=>'required','id_plage_horaire'=>'required','jours'=>'required','date_debut'=>'required']);
        foreach($request->input('jours') as $jour){
            EmploiPermanent::create(['id_personnel'=>$request->input('id_personnel'),'id_plage_horaire'=>$request->input('id_plage_horaire'),'jour_semaine'=>$jour,'date_debut'=>$request->input('date_debut'),'date_fin'=>$request->input('date_fin'),'annee_scolaire'=>session('year'),'statut'=>$request->input('statut','actif'),'observations'=>$request->input('observations'),'id_user'=>$this->userId()]);
        }
        session(['success'=>true]);
        return redirect()->route('emplois_permanents');
    }

    public function destroyEmploiPermanent($id)
    {
        EmploiPermanent::where('id','=',$id)->delete();
        session(['success'=>true]);
        return redirect()->route('emplois_permanents');
    }

    public function volumes(Request $request)
    {
        $rows=[];
        $prevus=$this->volumeCoursQuery($request)
            ->select('id_classe','id_matiere',DB::raw('SUM(volume_horaire_prevu) as total'))
            ->groupBy('id_classe','id_matiere')
            ->get();
        $realises=$this->volumeHeuresQuery($request)
            ->select('id_classe','id_matiere',DB::raw('SUM(duree_realisee) as total'))
            ->groupBy('id_classe','id_matiere')
            ->get();

        $map=[];
        foreach($prevus as $row){
            $key=$row->id_classe.'-'.$row->id_matiere;
            if(!isset($map[$key])) $map[$key]=['id_classe'=>$row->id_classe,'id_matiere'=>$row->id_matiere,'prevu'=>0,'realise'=>0];
            $map[$key]['prevu']=(float)$row->total;
        }
        foreach($realises as $row){
            $key=$row->id_classe.'-'.$row->id_matiere;
            if(!isset($map[$key])) $map[$key]=['id_classe'=>$row->id_classe,'id_matiere'=>$row->id_matiere,'prevu'=>0,'realise'=>0];
            $map[$key]['realise']=(float)$row->total;
        }

        $classeIds=[];
        $matiereIds=[];
        foreach($map as $item){
            $classeIds[]=$item['id_classe'];
            $matiereIds[]=$item['id_matiere'];
        }
        $classes=count($classeIds)>0?classe::whereIn('id',array_unique($classeIds))->get()->keyBy('id'):[];
        $matieres=count($matiereIds)>0?matiere::whereIn('id',array_unique($matiereIds))->get()->keyBy('id'):[];

        foreach($map as $item){
            $classe=isset($classes[$item['id_classe']])?$classes[$item['id_classe']]:null;
            $matiere=isset($matieres[$item['id_matiere']])?$matieres[$item['id_matiere']]:null;
            if($classe && $matiere) $rows[]=['classe'=>$classe,'matiere'=>$matiere,'prevu'=>$item['prevu'],'realise'=>$item['realise']];
        }
        return view('personnel.volumes_specialites',[
            'titre'=>'Suivi des volumes horaires',
            'rows'=>$rows,
            'cycles'=>cycle::orderBy('nom')->get(),
            'cycleSelectionAvailable'=>$this->cycleSelectionAvailable(),
            'niveaux'=>niveau::orderBy((App::getLocale()=='en')?'nom_anglais':'nom')->get(),
            'nniveaux'=>NNiveau::orderBy('id_cycle')->orderBy('nom')->get(),
            'classes'=>classe::with('niveau')->orderBy('nom')->get(),
            'semestres'=>$this->semestresData(),
        ]);
    }

    public function decompte(Request $request)
    {
        $imports=BiometrieImport::whereIn('type_import',['cours','manuel'])->orWhereNull('type_import')->orderBy('id','desc')->get();
        $heures=$this->heuresRealiseesCollection($request);
        $unmatchedPointages=BiometriePointage::whereNull('id_personnel')
            ->select('nom_biometrie','numero_biometrie')
            ->whereNotNull('nom_biometrie')
            ->groupBy('nom_biometrie','numero_biometrie')
            ->orderBy('nom_biometrie')
            ->get();
        $biometrieIdentites=BiometriePointage::select('nom_biometrie','numero_biometrie',DB::raw('COUNT(*) as total'))
            ->whereNotNull('nom_biometrie')
            ->groupBy('nom_biometrie','numero_biometrie')
            ->orderBy('nom_biometrie')
            ->get();

        return view('personnel.decompte_heures',[
            'titre'=>'Decompte des heures realisees',
            'imports'=>$imports,
            'heures'=>$heures,
            'totaux'=>$this->totauxParEnseignant($heures),
            'personnels'=>$this->enseignantsQuery()->orderBy('nom')->get(),
            'unmatchedPointages'=>$unmatchedPointages,
            'biometrieIdentites'=>$biometrieIdentites,
            'cycles'=>cycle::orderBy('nom')->get(),
            'niveaux'=>niveau::orderBy((App::getLocale()=='en')?'nom_anglais':'nom')->get(),
            'nniveaux'=>NNiveau::orderBy('id_cycle')->orderBy('nom')->get(),
            'classes'=>classe::with('niveau')->orderBy('nom')->get(),
            'semestres'=>$this->semestresData(),
            'cycleSelectionAvailable'=>$this->cycleSelectionAvailable(),
        ]);
    }

    public function storeHeure(Request $request)
    {
        return $this->manualStore($request);
    }

    public function manualCreate(Request $request)
    {
        $selectedImportId=$request->input('id_biometrie_import');
        $cours=CoursEnseignant::with(['personnel','classe','matiere','seances.plage'])
            ->where('statut','=','actif')
            ->orderBy('id_personnel')
            ->orderBy('date_debut','desc')
            ->get();
        $coursData=[];
        foreach($cours as $item){
            $seances=[];
            foreach($item->seances as $seance){
                $debut=$seance->plage?substr($seance->plage->heure_debut,0,5):'';
                $fin=$seance->plage?substr($seance->plage->heure_fin,0,5):'';
                $seances[]=[
                    'id'=>$seance->id,
                    'jour'=>$seance->jour_semaine,
                    'jour_label'=>isset($this->jours[$seance->jour_semaine])?$this->jours[$seance->jour_semaine]:'-',
                    'libelle'=>($seance->plage?$seance->plage->libelle:'Plage').' ('.$debut.' - '.$fin.')',
                    'debut'=>$debut,
                    'fin'=>$fin,
                ];
            }
            $coursData[$item->id]=['seances'=>$seances];
        }

        $manualImportIds=BiometrieImport::where('statut','=','manuel')->lists('id');
        $heuresManuelles=HeureRealiseeEnseignant::with(['personnel','cycle','filiere','nniveau','classe','matiere','cours.matiere','cours.classe','seance.plage','import'])
            ->whereIn('id_biometrie_import',$manualImportIds);
        if($selectedImportId) $heuresManuelles->where('id_biometrie_import','=',$selectedImportId);

        return view('personnel.decompte_heures_manual',[
            'titre'=>'Saisie manuelle des heures realisees',
            'imports'=>BiometrieImport::where('statut','=','manuel')->orderBy('id','desc')->get(),
            'heuresManuelles'=>$heuresManuelles->orderBy('date_seance','desc')->get(),
            'cours'=>$cours,
            'coursDataJson'=>json_encode($coursData),
        ]);
    }

    public function manualStore(Request $request)
    {
        $this->validate($request,[
            'id_cours_enseignant'=>'required',
            'id_seance_cours'=>'required',
            'date_seance'=>'required|date',
            'heure_debut_reelle'=>'required',
            'heure_fin_reelle'=>'required',
        ]);
        $cours=CoursEnseignant::find($request->input('id_cours_enseignant'));
        $seance=SeanceCours::with('plage')->where('id_cours_enseignant','=',$cours?$cours->id:0)->find($request->input('id_seance_cours'));
        if(!$cours || !$seance || !$seance->plage) return redirect()->route('biometrie_heures_manual_create')->withErrors(['cours'=>'Cours ou plage introuvable']);

        $import=$request->input('id_biometrie_import')?BiometrieImport::find($request->input('id_biometrie_import')):null;
        if(!$import){
            $this->validate($request,['date_debut_import'=>'required|date','date_fin_import'=>'required|date']);
            $import=BiometrieImport::create([
                'libelle'=>$request->input('libelle')?$request->input('libelle'):'Saisie manuelle du '.date('d/m/Y H:i'),
                'date_debut'=>$request->input('date_debut_import'),
                'date_fin'=>$request->input('date_fin_import'),
                'statut'=>'manuel',
                'type_import'=>'manuel',
                'observations'=>'Import cree depuis la saisie manuelle des heures realisees.',
                'id_user'=>$this->userId(),
            ]);
        }

        $dateSeance=$request->input('date_seance');
        if(strtotime($dateSeance)<strtotime($import->date_debut) || strtotime($dateSeance)>strtotime($import->date_fin)){
            return redirect()->back()->withErrors(['date_seance'=>'La date de la seance doit etre comprise dans la periode de l import manuel.'])->withInput();
        }
        if(strtotime($dateSeance)<strtotime($cours->date_debut) || strtotime($dateSeance)>strtotime($cours->date_fin)){
            return redirect()->back()->withErrors(['date_seance'=>'La date de la seance doit etre comprise dans la periode du cours enseignant.'])->withInput();
        }
        if((int)date('N',strtotime($dateSeance)) !== (int)$seance->jour_semaine){
            return redirect()->back()->withErrors(['date_seance'=>'La date choisie ne correspond pas au jour programme pour cette seance.'])->withInput();
        }

        list($dureePrevue,$dureeRealisee,$heureDebut,$heureFin,$observation)=$this->calculHeureRealisee($dateSeance,$seance,$request->input('heure_debut_reelle'),$request->input('heure_fin_reelle'),$request->input('observation'));
        $t=taux::whereIdPersonnel($cours->id_personnel)->where('id_matiere','=',$cours->id_matiere)->where('id_classe','=',$cours->id_classe)->first();
        if(!$t) $t=taux::whereIdPersonnel($cours->id_personnel)->where('id_matiere','=',0)->where('id_classe','=',0)->first();
        $montantTaux=$t?$t->taux:0;

        $heure=HeureRealiseeEnseignant::where('id_biometrie_import','=',$import->id)->where('id_seance_cours','=',$seance->id)->where('date_seance','=',$dateSeance)->first();
        if(!$heure) $heure=new HeureRealiseeEnseignant();
        $heure->fill(array_merge($this->universiteDataForCours($cours),['id_biometrie_import'=>$import->id,'id_cours_enseignant'=>$cours->id,'id_seance_cours'=>$seance->id,'id_personnel'=>$cours->id_personnel,'id_classe'=>$cours->id_classe,'id_matiere'=>$cours->id_matiere,'date_seance'=>$dateSeance,'jour_semaine'=>$seance->jour_semaine,'id_plage_horaire'=>$seance->id_plage_horaire,'heure_debut_prevue'=>$seance->plage->heure_debut,'heure_fin_prevue'=>$seance->plage->heure_fin,'heure_debut_reelle'=>$heureDebut,'heure_fin_reelle'=>$heureFin,'duree_prevue'=>$dureePrevue,'duree_realisee'=>$dureeRealisee,'montant_taux'=>$montantTaux,'montant_total'=>$montantTaux*$dureeRealisee,'annee_scolaire'=>session('year'),'statut'=>$dureeRealisee>0?'realise':'non_realise','observation'=>$observation,'id_user'=>$this->userId()]));
        $heure->save();
        $this->refreshImportTotals($import);
        session(['success'=>true]);
        return redirect()->route('biometrie_heures_manual_create',['id_biometrie_import'=>$import->id]);
    }

    public function storeBiometrie(Request $request)
    {
        $this->validate($request,['date_debut'=>'required|date','date_fin'=>'required|date','fichier'=>'required']);
        $file=$request->file('fichier');
        if(!$file) return redirect()->route('decompte_heures')->withErrors(['fichier'=>'Fichier introuvable']);
        $fileName=time().'_'.preg_replace('/[^A-Za-z0-9_.-]/','_',$file->getClientOriginalName());
        $dir=public_path('uploads/biometrie');
        if(!is_dir($dir)) mkdir($dir,0777,true);
        $file->move($dir,$fileName);

        $import=BiometrieImport::create([
            'libelle'=>$request->input('libelle')?$request->input('libelle'):'Import biometrie du '.date('d/m/Y H:i'),
            'date_debut'=>$request->input('date_debut'),
            'date_fin'=>$request->input('date_fin'),
            'fichier'=>'uploads/biometrie/'.$fileName,
            'statut'=>'importe',
            'type_import'=>'cours',
            'id_user'=>$this->userId(),
        ]);

        $rows=$this->readBiometrieFile($dir.DIRECTORY_SEPARATOR.$fileName);
        $personnels=personnel::all();
        $created=0;
        $unmatched=0;
        foreach($rows as $row){
            $date=$this->parseDateTime(isset($row['date_time'])?$row['date_time']:null);
            if(!$date) continue;
            $personnel=$this->matchPersonnel(isset($row['name'])?$row['name']:'',$personnels);
            if(!$personnel) $unmatched++;
            BiometriePointage::create([
                'id_biometrie_import'=>$import->id,
                'id_personnel'=>$personnel?$personnel->id:null,
                'departement'=>isset($row['department'])?$row['department']:null,
                'nom_biometrie'=>isset($row['name'])?$row['name']:null,
                'numero_biometrie'=>isset($row['no'])?$row['no']:null,
                'date_heure_pointage'=>$date,
                'raw_data'=>json_encode($row),
                'id_user'=>$this->userId(),
            ]);
            $created++;
        }
        $import->total_lignes=$created;
        $import->total_non_associees=$unmatched;
        $import->save();
        session(['success'=>true]);
        return redirect()->route('decompte_heures',['id_biometrie_import'=>$import->id]);
    }

    public function consoliderBiometrie($id)
    {
        $import=BiometrieImport::find($id);
        if(!$import) return redirect()->route('decompte_heures')->withErrors(['import'=>'Import introuvable']);
        $this->reassocierPointages($import);
        HeureRealiseeEnseignant::where('id_biometrie_import','=',$import->id)->delete();
        $count=0;
        $coursList=CoursEnseignant::with(['personnel','seances.plage'])->where('statut','=','actif')->get();
        foreach($coursList as $cours){
            foreach($cours->seances as $seance){
                if(!$seance->plage) continue;
                $start=max(strtotime($import->date_debut),strtotime($cours->date_debut));
                $end=min(strtotime($import->date_fin),strtotime($cours->date_fin));
                for($day=$start;$day<=$end;$day=strtotime('+1 day',$day)){
                    if((int)date('N',$day)!==(int)$seance->jour_semaine) continue;
                    $dateSeance=date('Y-m-d',$day);
                    $pointages=$this->pointagesForPersonnelDay($import,$cours->id_personnel,$dateSeance);
                    $interval=$this->intervalForPlage($pointages,$dateSeance,$seance->plage);
                    $debut=$interval?$interval[0]:null;
                    $fin=$interval?$interval[1]:null;
                    if($interval){
                        list($dureePrevue,$dureeRealisee,$heureDebut,$heureFin,$observation)=$this->calculHeureRealisee($dateSeance,$seance,date('H:i',$debut),date('H:i',$fin),null);
                    }else{
                        $dureePrevue=$this->plannedDuration($seance);
                        $dureeRealisee=0;
                        $heureDebut=null;
                        $heureFin=null;
                        $observation='Aucun couple entree/sortie biometrie exploitable pour cette plage.';
                    }
                    $t=taux::whereIdPersonnel($cours->id_personnel)->where('id_matiere','=',$cours->id_matiere)->where('id_classe','=',$cours->id_classe)->first();
                    if(!$t) $t=taux::whereIdPersonnel($cours->id_personnel)->where('id_matiere','=',0)->where('id_classe','=',0)->first();
                    $montantTaux=$t?$t->taux:0;
                    HeureRealiseeEnseignant::create(array_merge($this->universiteDataForCours($cours),['id_biometrie_import'=>$import->id,'id_cours_enseignant'=>$cours->id,'id_seance_cours'=>$seance->id,'id_personnel'=>$cours->id_personnel,'id_classe'=>$cours->id_classe,'id_matiere'=>$cours->id_matiere,'date_seance'=>$dateSeance,'jour_semaine'=>$seance->jour_semaine,'id_plage_horaire'=>$seance->id_plage_horaire,'heure_debut_prevue'=>$seance->plage->heure_debut,'heure_fin_prevue'=>$seance->plage->heure_fin,'heure_debut_reelle'=>$heureDebut,'heure_fin_reelle'=>$heureFin,'duree_prevue'=>$dureePrevue,'duree_realisee'=>$dureeRealisee,'montant_taux'=>$montantTaux,'montant_total'=>$montantTaux*$dureeRealisee,'annee_scolaire'=>session('year'),'statut'=>$dureeRealisee>0?'realise':'non_realise','observation'=>$observation,'id_user'=>$this->userId()]));
                    $count++;
                }
            }
        }
        $import->statut='consolide';
        $import->total_consolidees=$count;
        $import->total_non_associees=BiometriePointage::where('id_biometrie_import','=',$import->id)->whereNull('id_personnel')->count();
        $import->save();
        session(['success'=>true]);
        return redirect()->route('decompte_heures',['id_biometrie_import'=>$import->id]);
    }

    public function clearBiometrie($id)
    {
        $import=BiometrieImport::find($id);
        if($import){
            HeureRealiseeEnseignant::where('id_biometrie_import','=',$import->id)->delete();
            $import->statut='importe';
            $import->total_consolidees=0;
            $import->save();
            session(['success'=>true]);
        }
        return redirect()->route('decompte_heures');
    }

    public function destroyBiometrie($id)
    {
        $import=BiometrieImport::find($id);
        if($import){
            HeureRealiseeEnseignant::where('id_biometrie_import','=',$import->id)->delete();
            BiometriePointage::where('id_biometrie_import','=',$import->id)->delete();
            $import->delete();
            session(['success'=>true]);
        }
        return redirect()->route('decompte_heures');
    }

    public function storeMapping(Request $request)
    {
        $this->validate($request,['id_personnel'=>'required']);
        DB::table('biometrie_personnel_mappings')->insert([
            'id_personnel'=>$request->input('id_personnel'),
            'nom_biometrie'=>$request->input('nom_biometrie'),
            'numero_biometrie'=>$request->input('numero_biometrie'),
            'id_user'=>$this->userId(),
            'created_at'=>date('Y-m-d H:i:s'),
            'updated_at'=>date('Y-m-d H:i:s'),
        ]);
        BiometriePointage::where(function($q) use ($request){
            if($request->input('numero_biometrie')) $q->orWhere('numero_biometrie','=',$request->input('numero_biometrie'));
            if($request->input('nom_biometrie')) $q->orWhere('nom_biometrie','=',$request->input('nom_biometrie'));
        })->update(['id_personnel'=>$request->input('id_personnel')]);
        session(['success'=>true]);
        return redirect()->route('decompte_heures');
    }

    public function exportHeuresCsv(Request $request)
    {
        $heures=$this->heuresRealiseesCollection($request);
        $filename='heures_realisees_enseignants.csv';
        $headers=['Content-Type'=>'text/csv; charset=UTF-8','Content-Disposition'=>'attachment; filename="'.$filename.'"'];
        $callback=function() use ($heures){
            $out=fopen('php://output','w');
            fputcsv($out,['Date','Enseignant','Cycle','Filiere','Niveau','Specialite','Semestre','Matiere','Plage prevue','Heure debut realisee','Heure fin realisee','Heures prevues','Heures realisees','Heures non comptabilisees','Taux horaire','Montant','Statut','Observation'],';');
            foreach($heures as $heure){
                fputcsv($out,[$heure->date_seance,$heure->personnel?$heure->personnel->nom:'',$heure->cycle?$heure->cycle->nom:'',$heure->filiere?$heure->filiere->nom:'',$heure->nniveau?$heure->nniveau->nom:'',$heure->classe?$heure->classe->nom:'',$heure->semestre,$heure->matiere?$heure->matiere->nom:'',substr($heure->heure_debut_prevue,0,5).' - '.substr($heure->heure_fin_prevue,0,5),$heure->heure_debut_reelle?substr($heure->heure_debut_reelle,0,5):'',$heure->heure_fin_reelle?substr($heure->heure_fin_reelle,0,5):'',$heure->duree_prevue,$heure->duree_realisee,max($heure->duree_prevue-$heure->duree_realisee,0),$heure->montant_taux,$heure->montant_total,$heure->statut,$heure->observation],';');
            }
            fclose($out);
        };
        return \Response::stream($callback,200,$headers);
    }

    public function definirEnseignant($id)
    {
        return redirect()->route('cours_enseignants_create',['id_personnel'=>$id]);
    }

    private function coursBaseData($extra)
    {
        $this->seedDefaultPlages();
        return array_merge([
            'personnels'=>$this->enseignantsQuery()->orderBy('nom')->get(),
            'cycles'=>cycle::orderBy('nom')->get(),
            'cycleSelectionAvailable'=>$this->cycleSelectionAvailable(),
            'niveaux'=>niveau::orderBy((App::getLocale()=='en')?'nom_anglais':'nom')->get(),
            'classes'=>classe::with('niveau')->orderBy('nom')->get(),
            'nniveaux'=>NNiveau::orderBy('id_cycle')->orderBy('nom')->get(),
            'matieres'=>matiere::orderBy((App::getLocale()=='en')?'nom_anglais':'nom')->get(),
            'programmeMatieres'=>$this->programmeMatieresData(),
            'plages'=>PlageHoraire::where('statut','=','actif')->whereIn('type_personnel',['tous','enseignant','vacataire'])->orderBy('ordre')->get(),
            'semestres'=>$this->semestresData(),
            'jours'=>$this->jours,
        ],$extra);
    }

    private function coursQuery(Request $request)
    {
        $query=CoursEnseignant::with(['personnel','cycle','filiere','nniveau','classe','matiere','trimestre','seances.plage'])->whereAnneeScolaire(session('year'));
        if($request->input('id_personnel')) $query->where('id_personnel','=',$request->input('id_personnel'));
        if($request->input('id_classe')) $query->where('id_classe','=',$request->input('id_classe'));
        elseif($request->input('id_niveau') || $request->input('id_cycle') || $request->input('id_nniveau') || $request->input('semestre')) $query->whereIn('id_classe',$this->filteredClasseIds($request));
        if($request->input('id_cycle')) $query->where('id_cycle','=',$request->input('id_cycle'));
        if($request->input('id_niveau')) $query->where('id_niveau','=',$request->input('id_niveau'));
        if($request->input('id_nniveau')) $query->where('id_nniveau','=',$request->input('id_nniveau'));
        if($request->input('semestre')) $query->where('semestre','=',$request->input('semestre'));
        if($request->input('date_debut')) $query->where('date_fin','>=',$request->input('date_debut'));
        if($request->input('date_fin')) $query->where('date_debut','<=',$request->input('date_fin'));
        return $query;
    }

    private function volumeCoursQuery(Request $request)
    {
        $query=CoursEnseignant::whereAnneeScolaire(session('year'));
        if($request->input('id_classe')) $query->where('id_classe','=',$request->input('id_classe'));
        elseif($request->input('id_niveau') || $request->input('id_cycle') || $request->input('id_nniveau') || $request->input('semestre')) $query->whereIn('id_classe',$this->filteredClasseIds($request));
        if($request->input('id_cycle')) $query->where('id_cycle','=',$request->input('id_cycle'));
        if($request->input('id_niveau')) $query->where('id_niveau','=',$request->input('id_niveau'));
        if($request->input('id_nniveau')) $query->where('id_nniveau','=',$request->input('id_nniveau'));
        if($request->input('semestre')) $query->where('semestre','=',$request->input('semestre'));
        return $query;
    }

    private function volumeHeuresQuery(Request $request)
    {
        $query=HeureRealiseeEnseignant::whereAnneeScolaire(session('year'));
        if($request->input('id_classe')) $query->where('id_classe','=',$request->input('id_classe'));
        elseif($request->input('id_niveau') || $request->input('id_cycle') || $request->input('id_nniveau') || $request->input('semestre')) $query->whereIn('id_classe',$this->filteredClasseIds($request));
        if($request->input('id_cycle')) $query->where('id_cycle','=',$request->input('id_cycle'));
        if($request->input('id_niveau')) $query->where('id_niveau','=',$request->input('id_niveau'));
        if($request->input('id_nniveau')) $query->where('id_nniveau','=',$request->input('id_nniveau'));
        if($request->input('semestre')) $query->where('semestre','=',$request->input('semestre'));
        return $query;
    }

    private function emploiRows(Request $request)
    {
        $rows=[];
        foreach($this->coursQuery($request)->where('statut','=','actif')->get() as $cours){
            foreach($cours->seances as $seance){
                $rows[$seance->jour_semaine][$seance->id_plage_horaire][]=['cours'=>$cours,'seance'=>$seance];
            }
        }
        return $rows;
    }

    private function validateCours(Request $request)
    {
        $this->validate($request,['id_personnel'=>'required','id_cycle'=>'required','id_niveau'=>'required','id_nniveau'=>'required','id_classe'=>'required','id_matiere'=>'required','semestre'=>'required|in:1,2','date_debut'=>'required','date_fin'=>'required','seances'=>'required']);
    }

    private function coursData(Request $request)
    {
        $semestre=$request->input('semestre',1);
        return ['id_personnel'=>$request->input('id_personnel'),'id_cycle'=>$request->input('id_cycle'),'id_niveau'=>$request->input('id_niveau'),'id_nniveau'=>$request->input('id_nniveau'),'id_classe'=>$request->input('id_classe'),'id_matiere'=>$request->input('id_matiere'),'id_taux'=>$request->input('id_taux'),'id_trimestre'=>$semestre,'date_debut'=>$request->input('date_debut'),'date_fin'=>$request->input('date_fin'),'semestre'=>$semestre,'volume_horaire_prevu'=>$request->input('volume_horaire_prevu',0),'annee_scolaire'=>session('year'),'statut'=>$request->input('statut','actif'),'id_user'=>$this->userId()];
    }

    private function programmeError(Request $request)
    {
        $classe=classe::find($request->input('id_classe'));
        if(!$classe) return 'Specialite introuvable.';
        if((int)$classe->id_niveau !== (int)$request->input('id_niveau')) return 'La specialite choisie ne correspond pas a la filiere selectionnee.';
        $cycleClasse=$this->cycleIdForClasse($classe);
        if($cycleClasse && (int)$cycleClasse !== (int)$request->input('id_cycle')) return 'La specialite choisie ne correspond pas au cycle selectionne.';
        $programme=classeMatiere::where('id_cycle','=',$request->input('id_cycle'))
            ->where('id_classe','=',$request->input('id_classe'))
            ->where('id_nniveau','=',$request->input('id_nniveau'))
            ->where('semestre','=',$request->input('semestre'))
            ->where('id_matiere','=',$request->input('id_matiere'))
            ->count();
        if($programme<=0) return 'Cette matiere ne fait pas partie du programme pour ce cycle, ce niveau, cette filiere/specialite et ce semestre.';
        return null;
    }

    private function syncSeances(CoursEnseignant $cours,$seances)
    {
        foreach($seances as $seance){
            if(empty($seance['jour_semaine']) || empty($seance['id_plage_horaire'])) continue;
            $plage=PlageHoraire::find($seance['id_plage_horaire']);
            SeanceCours::create(['id_cours_enseignant'=>$cours->id,'id_plage_horaire'=>$seance['id_plage_horaire'],'jour_semaine'=>$seance['jour_semaine'],'duree_heures'=>$plage?($plage->duree_payable?$plage->duree_payable:$this->hoursBetween($plage->heure_debut,$plage->heure_fin)):0,'statut'=>'programme','id_user'=>$this->userId()]);
        }
    }

    private function syncLegacyDispenses(CoursEnseignant $cours)
    {
        $cours=CoursEnseignant::with('seances.plage')->find($cours->id);
        foreach($cours->seances as $seance){
            $legacyHoraire=$this->legacyHoraireForPlage($seance->plage);
            if(!$legacyHoraire) continue;
            dispense::firstOrCreate(['id_personnel'=>$cours->id_personnel,'id_classe'=>$cours->id_classe,'id_matiere'=>$cours->id_matiere,'jour'=>$seance->jour_semaine,'id_horaire'=>$legacyHoraire->id,'annee_scolaire'=>session('year')]);
        }
    }

    private function legacyHoraireForPlage($plage)
    {
        if(!$plage) return null;
        $intervalle=substr($plage->heure_debut,0,5).' - '.substr($plage->heure_fin,0,5);
        $h=horaire::where('intervalle','=',$intervalle)->first();
        if(!$h) $h=horaire::create(['intervalle'=>$intervalle,'type'=>$plage->type_plage=='pause'?1:0,'ordre'=>$plage->ordre]);
        return $h;
    }

    private function teacherConflict($data,$seances,$exceptId=0)
    {
        foreach($seances as $seance){
            $query=CoursEnseignant::where('id_personnel','=',$data['id_personnel'])->where('statut','=','actif')->where('date_debut','<=',$data['date_fin'])->where('date_fin','>=',$data['date_debut']);
            if($exceptId) $query->where('id','<>',$exceptId);
            foreach($query->get() as $cours){
                $exists=SeanceCours::where('id_cours_enseignant','=',$cours->id)->where('jour_semaine','=',$seance['jour_semaine'])->where('id_plage_horaire','=',$seance['id_plage_horaire'])->count();
                if($exists>0) return 'Cet enseignant est deja occupe sur cette plage horaire pour une periode qui se chevauche.';
            }
        }
        return null;
    }

    private function plageData(Request $request)
    {
        return ['libelle'=>$request->input('libelle'),'heure_debut'=>$request->input('heure_debut'),'heure_fin'=>$request->input('heure_fin'),'duree_payable'=>$request->input('duree_payable'),'type_plage'=>$request->input('type_plage','cours'),'type_personnel'=>$request->input('type_personnel','tous'),'periode_journee'=>$request->input('periode_journee','jour'),'format_plage'=>$request->input('format_plage','mixte'),'ordre'=>$request->input('ordre',0),'statut'=>$request->input('statut','actif'),'id_user'=>$this->userId()];
    }

    private function seedDefaultPlages()
    {
        if(PlageHoraire::count()>0) return;
        $plages=[['Permanent jour','08:00','16:00',8,'cours','permanent','jour','bloc_8h',10],['Permanent soir','15:00','21:00',6,'cours','permanent','soir','bloc_6h',20],['Enseignant matin','08:00','12:00',4,'cours','enseignant','jour','bloc_4h',30],['Pause','12:00','12:45',0,'pause','tous','jour','pause',31],['Enseignant apres-midi','12:45','16:45',4,'cours','enseignant','jour','bloc_4h',32],['Vacataire soir','16:00','21:00',5,'cours','vacataire','soir','bloc_5h',40]];
        foreach($plages as $p) PlageHoraire::create(['libelle'=>$p[0],'heure_debut'=>$p[1],'heure_fin'=>$p[2],'duree_payable'=>$p[3],'type_plage'=>$p[4],'type_personnel'=>$p[5],'periode_journee'=>$p[6],'format_plage'=>$p[7],'ordre'=>$p[8],'statut'=>'actif','id_user'=>0]);
    }

    private function hoursBetween($start,$end){ return max((strtotime($end)-strtotime($start))/3600,0); }

    private function plannedDuration($seance)
    {
        if($seance->plage && $seance->plage->duree_payable!==null) return (float)$seance->plage->duree_payable;
        if($seance->duree_heures) return (float)$seance->duree_heures;
        return $seance->plage?$this->hoursBetween($seance->plage->heure_debut,$seance->plage->heure_fin):0;
    }

    private function calculHeureRealisee($dateSeance,$seance,$debutReel,$finReel,$observation=null)
    {
        $plannedStart=strtotime($dateSeance.' '.$seance->plage->heure_debut);
        $plannedEnd=strtotime($dateSeance.' '.$seance->plage->heure_fin);
        $realStartInput=strtotime($dateSeance.' '.$debutReel);
        $realEndInput=strtotime($dateSeance.' '.$finReel);
        $realStart=max($realStartInput,$plannedStart);
        $realEnd=min($realEndInput,$plannedEnd);
        $dureePrevue=$this->plannedDuration($seance);
        $dureeRealisee=$realEnd>$realStart?min($dureePrevue,round(($realEnd-$realStart)/3600,2)):0;
        $missing=round($dureePrevue-$dureeRealisee,2);
        if($missing>0){
            $observation=trim(($observation?$observation.' ':'').'Saisie partielle : '.number_format($missing,2,',',' ').' h non comptabilisee(s).');
        }
        return [$dureePrevue,$dureeRealisee,date('H:i:s',$realStart),date('H:i:s',$realEnd),$observation];
    }

    private function heuresRealiseesCollection(Request $request)
    {
        $query=HeureRealiseeEnseignant::with(['personnel','cycle','filiere','nniveau','classe','matiere','plage','cours.matiere','cours.classe','seance.plage','import']);
        if($request->input('id_biometrie_import')) $query->where('id_biometrie_import','=',$request->input('id_biometrie_import'));
        if($request->input('id_personnel')) $query->where('id_personnel','=',$request->input('id_personnel'));
        if($request->input('id_cycle')) $query->where('id_cycle','=',$request->input('id_cycle'));
        if($request->input('id_niveau')) $query->where('id_niveau','=',$request->input('id_niveau'));
        if($request->input('id_nniveau')) $query->where('id_nniveau','=',$request->input('id_nniveau'));
        if($request->input('id_classe')) $query->where('id_classe','=',$request->input('id_classe'));
        if($request->input('semestre')) $query->where('semestre','=',$request->input('semestre'));
        if($request->input('search_personnel')){
            $ids=personnel::whereRaw('lower(nom) LIKE ?',['%'.strtolower($request->input('search_personnel')).'%'])->lists('id');
            $query->whereIn('id_personnel',$ids);
        }
        if($request->input('date_debut')) $query->where('date_seance','>=',$request->input('date_debut'));
        if($request->input('date_fin')) $query->where('date_seance','<=',$request->input('date_fin'));
        return $query->orderBy('id_personnel')->orderBy('date_seance','desc')->get();
    }

    private function totauxParEnseignant($heures)
    {
        $rows=[];
        foreach($heures->groupBy('id_personnel') as $items){
            $first=$items->first();
            $prevues=$items->sum('duree_prevue');
            $realisees=$items->sum('duree_realisee');
            $rows[]=[
                'enseignant'=>($first && $first->personnel)?$first->personnel->nom:'-',
                'heures_prevues'=>$prevues,
                'heures_realisees'=>$realisees,
                'heures_non_comptabilisees'=>max($prevues-$realisees,0),
                'montant_total'=>$items->sum('montant_total'),
            ];
        }
        return $rows;
    }

    private function refreshImportTotals($import)
    {
        $import=BiometrieImport::find($import->id);
        if(!$import) return;
        $import->total_consolidees=HeureRealiseeEnseignant::where('id_biometrie_import','=',$import->id)->count();
        $import->total_lignes=max($import->total_lignes,$import->total_consolidees);
        $import->total_non_associees=BiometriePointage::where('id_biometrie_import','=',$import->id)->whereNull('id_personnel')->count();
        $import->save();
    }

    private function readBiometrieFile($path)
    {
        $rows=[];
        if(($handle=fopen($path,'r'))!==false){
            $first=fgetcsv($handle,0,';');
            if($first && count($first)<2){
                rewind($handle);
                $first=fgetcsv($handle,0,',');
                $separator=',';
            }else{
                $separator=';';
            }
            $headers=$first?$this->normalizeHeaders($first):[];
            while(($data=fgetcsv($handle,0,$separator))!==false){
                $row=[];
                foreach($headers as $index=>$header) $row[$header]=isset($data[$index])?$data[$index]:null;
                if(isset($row['date_time']) && $row['date_time']) $rows[]=$row;
            }
            fclose($handle);
        }
        if(count($rows)>0) return $rows;

        $content=file_get_contents($path);
        $text=preg_replace('/[^\x20-\x7E\r\n\t\/:-]/',' ',$content);
        preg_match_all('/@\s*([A-Z][A-Z ]{2,})\s+@\s*([A-Za-z .\'-]{2,}?)\s+@\s*(\d{1,10})\s+@\s*(\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2}:\d{2})/s',$text,$matches,PREG_SET_ORDER);
        foreach($matches as $row){
            $rows[]=['department'=>trim($row[1]),'name'=>trim($row[2]),'no'=>trim($row[3]),'date_time'=>trim($row[4])];
        }
        return $rows;
    }

    private function normalizeHeaders($headers)
    {
        $normalized=[];
        foreach($headers as $header){
            $key=strtolower(trim((string)$header));
            $key=str_replace(['/',' ','.','-'],'_',$key);
            if(in_array($key,['datetime','date_time'])) $key='date_time';
            elseif($key==='verifycode') $key='verify_code';
            elseif($key==='cardno') $key='card_no';
            $normalized[]=$key;
        }
        return $normalized;
    }

    private function parseDateTime($value)
    {
        if(!$value) return null;
        $value=trim((string)$value);
        $formats=['d/m/Y H:i:s','d/m/Y H:i','Y-m-d H:i:s','Y-m-d H:i'];
        foreach($formats as $format){
            $date=\DateTime::createFromFormat($format,$value);
            if($date) return $date->format('Y-m-d H:i:s');
        }
        $time=strtotime($value);
        return $time?date('Y-m-d H:i:s',$time):null;
    }

    private function matchPersonnel($name,$personnels)
    {
        $needle=$this->normalizeName($name);
        if($needle==='') return null;
        foreach($personnels as $personnel){
            $haystack=$this->normalizeName($personnel->nom);
            if($haystack===$needle || strpos($haystack,$needle)!==false || strpos($needle,$haystack)!==false) return $personnel;
        }
        return null;
    }

    private function normalizeName($name)
    {
        $ascii=@iconv('UTF-8','ASCII//TRANSLIT//IGNORE',(string)$name);
        if($ascii===false) $ascii=(string)$name;
        return trim(preg_replace('/[^a-z0-9]/','',strtolower($ascii)));
    }

    private function reassocierPointages($import)
    {
        $personnels=personnel::all();
        $mappings=DB::table('biometrie_personnel_mappings')->get();
        foreach(BiometriePointage::where('id_biometrie_import','=',$import->id)->whereNull('id_personnel')->get() as $pointage){
            $personnelId=null;
            foreach($mappings as $mapping){
                if(($mapping->numero_biometrie && $mapping->numero_biometrie===$pointage->numero_biometrie) || ($mapping->nom_biometrie && $mapping->nom_biometrie===$pointage->nom_biometrie)){
                    $personnelId=$mapping->id_personnel;
                    break;
                }
            }
            if(!$personnelId){
                $personnel=$this->matchPersonnel($pointage->nom_biometrie,$personnels);
                $personnelId=$personnel?$personnel->id:null;
            }
            if($personnelId){
                $pointage->id_personnel=$personnelId;
                $pointage->save();
            }
        }
    }

    private function pointagesForPersonnelDay($import,$personnelId,$date)
    {
        $numbers=DB::table('biometrie_personnel_mappings')->where('id_personnel','=',$personnelId)->whereNotNull('numero_biometrie')->lists('numero_biometrie');
        $names=DB::table('biometrie_personnel_mappings')->where('id_personnel','=',$personnelId)->whereNotNull('nom_biometrie')->lists('nom_biometrie');
        return BiometriePointage::where('id_biometrie_import','=',$import->id)
            ->where(function($q) use ($personnelId,$numbers,$names){
                $q->where('id_personnel','=',$personnelId);
                if(count($numbers)>0) $q->orWhereIn('numero_biometrie',$numbers);
                if(count($names)>0) $q->orWhereIn('nom_biometrie',$names);
            })
            ->where('date_heure_pointage','>=',$date.' 00:00:00')
            ->where('date_heure_pointage','<=',$date.' 23:59:59')
            ->orderBy('date_heure_pointage')
            ->get();
    }

    private function intervalForPlage($pointages,$date,$plage)
    {
        $times=[];
        foreach($pointages as $pointage) $times[]=strtotime($pointage->date_heure_pointage);
        sort($times);
        if(count($times)<2) return null;
        $plannedStart=strtotime($date.' '.$plage->heure_debut);
        $plannedEnd=strtotime($date.' '.$plage->heure_fin);
        $best=null;
        $bestOverlap=0;
        for($i=0;$i<count($times)-1;$i++){
            for($j=$i+1;$j<count($times);$j++){
                $entry=$times[$i];
                $exit=$times[$j];
                if($exit<strtotime('-30 minutes',$plannedEnd)) continue;
                $overlap=max(0,min($exit,$plannedEnd)-max($entry,$plannedStart));
                if($overlap>$bestOverlap){
                    $bestOverlap=$overlap;
                    $best=[$entry,$exit];
                }
            }
        }
        return $best;
    }
    private function filteredClasseIds(Request $request)
    {
        $query=classe::query();
        if($request->input('id_niveau')) $query->where('id_niveau','=',$request->input('id_niveau'));
        if($request->input('id_cycle')){
            if(Schema::hasColumn('classe','id_cycle')) $query->where('id_cycle','=',$request->input('id_cycle'));
            elseif(Schema::hasColumn('niveau','id_cycle')) $query->whereIn('id_niveau',niveau::where('id_cycle','=',$request->input('id_cycle'))->lists('id'));
        }
        $classeIds=$query->lists('id');
        if($request->input('id_nniveau') || $request->input('semestre')){
            $programme=classeMatiere::query();
            if($request->input('id_cycle')) $programme->where('id_cycle','=',$request->input('id_cycle'));
            if($request->input('id_nniveau')) $programme->where('id_nniveau','=',$request->input('id_nniveau'));
            if($request->input('semestre')) $programme->where('semestre','=',$request->input('semestre'));
            if(count($classeIds)>0) $programme->whereIn('id_classe',$classeIds);
            $classeIds=$programme->lists('id_classe');
        }
        return $classeIds;
    }

    private function semestresData()
    {
        return [(object)['id'=>1,'nom'=>'Semestre 1'],(object)['id'=>2,'nom'=>'Semestre 2']];
    }

    private function matieresForProgramme(Request $request,$classeId)
    {
        $programme=classeMatiere::where('id_classe','=',$classeId);
        if($request->input('id_cycle')) $programme->where('id_cycle','=',$request->input('id_cycle'));
        if($request->input('id_nniveau')) $programme->where('id_nniveau','=',$request->input('id_nniveau'));
        if($request->input('semestre')) $programme->where('semestre','=',$request->input('semestre'));
        $matiereIds=$programme->lists('id_matiere');
        $query=matiere::orderBy((App::getLocale()=='en')?'nom_anglais':'nom');
        if(count($matiereIds)>0) $query->whereIn('id',$matiereIds);
        return $query->get();
    }

    private function programmeMatieresData()
    {
        $rows=[];
        foreach(classeMatiere::all() as $cm){
            $key=$cm->id_cycle.'-'.$cm->id_classe.'-'.$cm->id_nniveau.'-'.$cm->semestre;
            if(!isset($rows[$key])) $rows[$key]=[];
            $rows[$key][]=$cm->id_matiere;
        }
        return json_encode($rows);
    }

    private function universiteDataForCours($cours)
    {
        return [
            'id_cycle'=>isset($cours->id_cycle)?$cours->id_cycle:0,
            'id_niveau'=>isset($cours->id_niveau)?$cours->id_niveau:0,
            'id_nniveau'=>isset($cours->id_nniveau)?$cours->id_nniveau:0,
            'semestre'=>isset($cours->semestre)?$cours->semestre:1,
        ];
    }

    private function cycleIdForClasse($classe)
    {
        if(!$classe) return 0;
        if(isset($classe->id_cycle) && $classe->id_cycle) return $classe->id_cycle;
        if($classe->niveau && isset($classe->niveau->id_cycle)) return $classe->niveau->id_cycle;
        return 0;
    }

    private function cycleSelectionAvailable()
    {
        return Schema::hasColumn('classe','id_cycle') || Schema::hasColumn('niveau','id_cycle') || Schema::hasColumn('n_niveaus','id_cycle');
    }

    private function userId(){ return Auth::user()?Auth::user()->id:0; }
    private function personnelQuery(){ $query=personnel::query(); if(Schema::hasColumn('personnel','actif')) $query->where('actif','=',1); return $query; }
    private function enseignantsQuery(){ $query=$this->personnelQuery(); if(Schema::hasColumn('personnel','type_personnel')) $query->whereIn('type_personnel',['enseignant','vacataire']); elseif(Schema::hasColumn('personnel','type')) $query->where('type','=',0); return $query; }
    private function typePersonnelQuery($type){ $query=$this->personnelQuery(); if(Schema::hasColumn('personnel','type_personnel')) $query->where('type_personnel','=',$type); elseif(Schema::hasColumn('personnel','type')) $query->where('type','=',$type=='permanent'?1:0); return $query; }
}
