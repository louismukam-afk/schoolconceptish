<?php namespace gesecole\Http\Controllers\admin;

use gesecole\Http\Requests;
use gesecole\Http\Controllers\Controller;
use gesecole\Campus;
use gesecole\verrouNote;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use gesecole\parametres;
use Illuminate\Http\Request;

class verrou_noteController extends Controller {

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

	public function index()
	{
        $this->ensureStudentPortalLockColumns();
        $titre=trans('admin.verrou_note');
        $v=verrouNote::whereAnneeScolaire(\session('year'))->get();
        $studentPortalLocks = array();
        if (DB::getSchemaBuilder()->hasColumn('verrou_note', 'target')) {
            $studentPortalLocks = verrouNote::whereAnneeScolaire(\session('year'))
                ->where('target', '=', 'student_portal')
                ->orderBy('id', 'desc')
                ->get();
        }
        $values= array(
            'titre'=>$titre,
            'verrous'=>$v,
            'student_portal_locks'=>$studentPortalLocks,
            'campus'=>Campus::orderBy('nom')->get(),
            'current_campus_id'=>$this->currentCampusId()
        );
        if(session('success')){
            $values['success']=true;
            Session::forget('success');
        }


        return view('admin/verrouNote',$values);
	}

    public function storeStudentPortalLock(Request $request)
    {
        if (!$this->ensureStudentPortalLockColumns()) {
            return redirect()->back()->withErrors(array('Les colonnes de blocage du portail etudiant ne sont pas disponibles dans la base.'));
        }

        $this->validate($request, array(
            'objectif' => 'required',
            'message' => 'required',
            'id_campus' => 'required|numeric',
            'starts_at' => 'sometimes|date',
            'ends_at' => 'sometimes|date'
        ));

        if ($request->input('ends_at') && strtotime($request->input('ends_at')) < time()) {
            return redirect()->back()->withErrors(array('La date de reouverture doit etre superieure a la date actuelle.'));
        }

        $lock = new verrouNote();
        $lock->annee_scolaire = session('year');
        $lock->sequence = 0;
        $lock->id_campus = $request->input('id_campus');
        $lock->target = 'student_portal';
        $lock->is_active = 1;
        $lock->starts_at = date('Y-m-d H:i:s');
        $lock->ends_at = $request->input('ends_at') ? date('Y-m-d H:i:s', strtotime($request->input('ends_at'))) : null;
        $lock->objectif = $request->input('objectif');
        $lock->message = $request->input('message');
        $lock->id_creator = Auth::check() ? Auth::user()->id : null;
        $lock->id_last_editor = Auth::check() ? Auth::user()->id : null;
        $lock->save();

        session(['success'=>true]);
        return redirect()->action('admin\verrou_noteController@index');
    }

    private function ensureStudentPortalLockColumns()
    {
        if (!Schema::hasTable('verrou_note')) {
            return false;
        }

        Schema::table('verrou_note', function (Blueprint $table) {
            if (!Schema::hasColumn('verrou_note', 'id_campus')) {
                $table->integer('id_campus')->nullable()->index();
            }
            if (!Schema::hasColumn('verrou_note', 'target')) {
                $table->string('target')->default('note_sequence')->index();
            }
            if (!Schema::hasColumn('verrou_note', 'is_active')) {
                $table->boolean('is_active')->default(1);
            }
            if (!Schema::hasColumn('verrou_note', 'starts_at')) {
                $table->dateTime('starts_at')->nullable();
            }
            if (!Schema::hasColumn('verrou_note', 'ends_at')) {
                $table->dateTime('ends_at')->nullable();
            }
            if (!Schema::hasColumn('verrou_note', 'objectif')) {
                $table->string('objectif')->nullable();
            }
            if (!Schema::hasColumn('verrou_note', 'message')) {
                $table->text('message')->nullable();
            }
            if (!Schema::hasColumn('verrou_note', 'id_creator')) {
                $table->integer('id_creator')->nullable();
            }
            if (!Schema::hasColumn('verrou_note', 'id_last_editor')) {
                $table->integer('id_last_editor')->nullable();
            }
            if (!Schema::hasColumn('verrou_note', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('verrou_note', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        return Schema::hasColumn('verrou_note', 'id_campus') && Schema::hasColumn('verrou_note', 'target');
    }

    public function openStudentPortal($id)
    {
        $lock = verrouNote::find($id);
        if ($lock && $lock->target == 'student_portal') {
            $lock->is_active = 0;
            $lock->id_last_editor = Auth::check() ? Auth::user()->id : null;
            $lock->save();
        }

        session(['success'=>true]);
        return redirect()->action('admin\verrou_noteController@index');
    }

    public function activate($sequence)
    {
       $v= verrouNote::whereAnneeScolaire(\session('year'))
           ->where('sequence','=',$sequence)
            ->get();
       foreach ($v as $verrous){
           $verrous->delete();
       }
       \session(['success'=>true]);
        return redirect()->action('admin\verrou_noteController@index');

    }
    public function deactivate($sequence)
    {
        $v=new verrouNote();
        $v->annee_scolaire=\session('year');
        $v->sequence=$sequence;
        if (DB::getSchemaBuilder()->hasColumn('verrou_note', 'target')) {
            $v->target = 'note_sequence';
            $v->is_active = 1;
        }
        $v->save();

        \session(['success'=>true]);
        return redirect()->action('admin\verrou_noteController@index');
    }

    public function deliberer($sequence)
    {
        $v= verrouNote::whereAnneeScolaire(\session('year'))
            ->where('sequence','=',$sequence)
            ->get();
        foreach ($v as $verrous){
            $verrous->delete();
        }
        \session(['success'=>true]);
        return redirect()->action('admin\verrou_noteController@index');

    }



	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
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

	}

}
