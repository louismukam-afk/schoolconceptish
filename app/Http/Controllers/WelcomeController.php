<?php namespace gesecole\Http\Controllers;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Request;
use gesecole\Campus;
use gesecole\parametres;
use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Psy\Util\Json;

class WelcomeController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Welcome Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders the "marketing page" for the application and
	| is configured to only allow guests. Like most of the other sample
	| controllers, you are free to modify or remove it as you desire.
	|
	*/

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
    public function __construct()
    {

        session(['big_title'=>trans('menu.accueil')]);
        $this->renewSchoolYear();
    }

	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */
	public function index()
	{

      // $c=new Encrypter(config('env.key'));
       //dump($c->encrypt('schoooncept'));
        return view('home',['isHome'=>true]);
	}

    public function selectCampus()
    {
        $user = \Auth::user();
        $campus = $user && $user->isAdmin() ? Campus::orderBy('nom')->get() : ($user ? $user->campus()->orderBy('nom')->get() : array());
        return view('select_campus', array('titre' => 'Choix du campus', 'campus' => $campus));
    }

    public function setCampus(Request $request)
    {
        $this->validate($request, array('id_campus' => 'required|numeric'));
        $user = \Auth::user();
        $campus = null;
        if ($user && $user->isAdmin()) {
            $campus = Campus::find($request->input('id_campus'));
        } elseif ($user) {
            $campus = $user->campus()->where('campus.id', '=', $request->input('id_campus'))->first();
        }
        if ($campus) {
            $campusNom = $campus->nom;
            if ($campus->sigle) {
                $campusNom .= ' - '.$campus->sigle;
            }
            session(array(
                'campus_id' => $campus->id,
                'campus_nom' => $campusNom,
                'campus_sigle' => $campus->sigle,
                'campus_localisation' => $campus->localisation,
                'nom_etab' => $campusNom
            ));
        }
        return redirect()->to(url('/'));
    }

	public function editYear(Request $req){
        $this->validate($req,[
            'first_year'=>'required|numeric',
            'second_year'=>'required|numeric',
        ]);
        $fy=$req->input('first_year');
        $sy=$req->input('second_year');
        $school_year=$fy.'-'.$sy;
        $p=parametres::whereNom('annee_scolaire')->get()->first();
        $p->valeur=$school_year;
        $p->save();
        session(['year'=>$p->valeur]);
        return redirect()->to(url('/'));
    }
    public function blk(){
	    $p=parametres::firstOrNew(['nom'=>'auth']);
	    $p->valeur='OK';
	    $p->save();
	    return redirect()->action('WelcomeController@index');
    }

    public function unblk(){
        $p=parametres::whereNom('auth')->first();
        $p->delete();
        return redirect()->action('WelcomeController@index');
    }
    public function fr_locale(){

        $prevUrl=\URL::previous();
        //session(['locale'=>'fr']);
        $cook=cookie('locale','fr',432000);
        return redirect()->to($prevUrl)->withCookie($cook);
    }
    public function en_locale(){
        $prevUrl=\URL::previous();
        //session(['locale'=>'en']);
        $cook=cookie('locale','en',432000);
        return redirect()->to($prevUrl)->withCookie($cook);
    }
    public function change_password(Request $request){
        $validate_pass=Validator::make($request->all(),
            [
                'ancien_mot_de_passe'=>'required',
                'nouveau_mot_de_passe'=>'required|min:6',
                'password_again'=>'required|min:6',
            ]
            );



        if($validate_pass->fails()){
            $errors=$validate_pass->errors();
            $errors=$errors->all();

           return response()->json($errors);
        }
        $password=\Auth::user()->password;
        $apass=$request->input('ancien_mot_de_passe');
          $npass=$request->input('nouveau_mot_de_passe');
                    $cpass=$request->input('password_again');

         if (strcmp($npass,$cpass)!=0) {
            
                        return response()->json(['invalid'=>'Le mot de passe et la confirmation doivent etre identiques']);
        }

        if (!\Hash::check($apass,$password)){
            return response()->json(['invalid'=>trans('admin.apass_invalide')]);
        }

      
        else {
            \Auth::user()->password=bcrypt($npass);
           \Auth::user()->save();
            return response()->json(['success'=>trans('admin.succes')]);
        }
    }
}
