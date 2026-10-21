<?php namespace gesecole\Http\Controllers\user;

use gesecole\Action;
use gesecole\Campus;
use gesecole\Functions;
use gesecole\Http\Requests;
use gesecole\Http\Controllers\Controller;
use gesecole\parametres;
use gesecole\personnel;
use gesecole\Role;
use gesecole\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class userController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
    public function __construct()
    {
        session(['big_title'=>trans('admin.dashboard')]);
        $this->renewSchoolYear();
    }

	public function index(Request $request)
	{
        $values=array('titre'=>trans('admin.liste_utilisateurs'));
        $current_user = Auth::user();
        $search_term = $request->input('search');

        if(!$current_user->isCreator()) {
            $u_array=Action::whereRaw('action_name = ? OR action_name = ?', [Action::ACTION_ALL, Action::ACTION_ALMOST_ALL])->get(['id_user'])->fetch('id_user')->toArray();
        }
        else {
            $u_array=Action::whereRaw('action_name = ?', [Action::ACTION_ALL])->get(['id_user'])->fetch('id_user')->toArray();
        }
        array_push($u_array, $current_user->id);

        if($search_term) {
            $values['search_term']=$search_term;
            $search_term = strtolower($search_term);
            $u=User::with('personnel')
                ->leftJoin('personnel', 'users.id_personnel', '=', 'personnel.id')
                ->whereRaw('lower(name) LIKE ? OR lower(fullname) LIKE ? OR lower(nom) LIKE ?', ['%' . $search_term . '%', '%' . $search_term . '%', '%' . $search_term . '%'])
                ->whereNotIn('users.id',$u_array)
                ->select(['users.*'])
                ->orderBy('created_at','desc')
                ->paginate(30);
        } else {
            $u=User::with('personnel')
                ->whereNotIn('id',$u_array)
                ->orderBy('created_at','desc')
                ->paginate(30);
        }



        /*if(!Functions::contain(\Auth::user()->roles->fetch('value')->toArray(),0)){
            $u_array=Role::where('value','<=',1)->get()->fetch('id_user')->toArray();
            array_push($u_array, $current_user->id);
            $u=User::whereNotIn('id',$u_array)->orderBy('created_at','desc')->paginate(30);
        }
        else{
            $u_array=Role::where('value','<=',0)->get()->fetch('id_user')->toArray();
            array_push($u_array, $current_user->id);
            $u=User::whereNotIn('id',$u_array)->orderBy('created_at','desc')->paginate(30);

        }*/

        $p=personnel::orderBy('nom')->get();
        $campus=Campus::orderBy('nom')->get();

        $values['utilisateurs']=$u;
        $values['personnels']=$p;
        $values['campus']=$campus;
        if(session('success')){
            Session::forget('success');
            $values['success']=true;
        }
        return view('admin\utilisateurs',$values);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
    public function activate(Request $request)
    {

        $this->validate($request,[
           'id'=>'required|numeric',
            'personnel'=>'required|numeric',
        ]);
        $u=User::find($request->input('id'));
        $u->active=1;
        $personnel=$request->input('personnel');
        if($personnel!=0){
            $u->id_personnel=$personnel;
        }
        $u->save();
        $this->syncUserCampus($u->id, (array) $request->input('campus', array()));
        session(['success'=>true]);
        return redirect()->action('user\userController@index');
    }

    public function deactivate($id)
    {
        $u=User::find($id);
        $u->active=0;
        $u->id_personnel=0;
        $u->save();
        DB::table('campus_user')->where('id_user', '=', $u->id)->delete();
        session(['success'=>true]);

        return redirect()->action('user\userController@index');


    }

    public function resetPassword(Request $request)
    {
        $this->validate($request, array(
            'id' => 'required|numeric',
            'password' => 'required|min:6|confirmed',
        ));

        $u = User::find($request->input('id'));
        if(!$u) {
            return redirect()->action('user\userController@index');
        }

        $u->password = Hash::make($request->input('password'));
        $u->save();

        session(array('success' => true));
        return redirect()->action('user\userController@index');
    }

    private function syncUserCampus($idUser, $campusIds)
    {
        $campusIds = array_values(array_filter($campusIds));
        $now = date('Y-m-d H:i:s');
        $currentUser = Auth::user();
        $currentUserId = $currentUser ? $currentUser->id : null;

        if(count($campusIds) > 0) {
            DB::table('campus_user')
                ->where('id_user', '=', $idUser)
                ->whereNotIn('id_campus', $campusIds)
                ->delete();
        }
        else {
            DB::table('campus_user')
                ->where('id_user', '=', $idUser)
                ->delete();
        }

        foreach($campusIds as $idCampus) {
            $pivot = DB::table('campus_user')
                ->where('id_user', '=', $idUser)
                ->where('id_campus', '=', $idCampus)
                ->first();

            if($pivot) {
                DB::table('campus_user')
                    ->where('id', '=', $pivot->id)
                    ->update(array(
                        'id_last_editor' => $currentUserId,
                        'updated_at' => $now
                    ));
            }
            else {
                DB::table('campus_user')->insert(array(
                    'id_user' => $idUser,
                    'id_campus' => $idCampus,
                    'id_creator' => $currentUserId,
                    'id_last_editor' => $currentUserId,
                    'created_at' => $now,
                    'updated_at' => $now
                ));
            }
        }
    }
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

}
