<?php namespace gesecole\Http\Controllers\admin;

use gesecole\Campus;
use gesecole\classe;
use gesecole\Http\Controllers\Controller;
use gesecole\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class campusController extends Controller {
    public function __construct()
    {
        session(array('big_title' => trans('admin.dashboard')));
        $this->renewSchoolYear();
    }

    public function index()
    {
        $values = array('titre' => 'Gestion des campus');
        $values['campus'] = Campus::orderBy('nom')->paginate(20);
        if (session('success')) {
            Session::forget('success');
            $values['success'] = true;
        }
        return view('admin.campus', $values);
    }

    public function store(Request $request)
    {
        $this->validate($request, array('nom' => 'required', 'logo_file' => 'image'));
        $campus = new Campus();
        $this->fillCampus($campus, $request);
        $this->setAuditFields($campus, true);
        $campus->save();
        session(array('success' => true));
        return redirect()->to(URL::previous());
    }

    public function update(Request $request)
    {
        $this->validate($request, array('id' => 'required|numeric', 'nom' => 'required', 'logo_file' => 'image'));
        $campus = Campus::find($request->input('id'));
        $this->fillCampus($campus, $request);
        $this->setAuditFields($campus, false);
        $campus->save();
        session(array('success' => true));
        return redirect()->to(URL::previous());
    }

    public function destroy($id)
    {
        $campus = Campus::find($id);
        if ($campus) {
            $campus->delete();
            session(array('success' => true));
        }
        return redirect()->to(URL::previous());
    }

    public function users($id)
    {
        $campus = Campus::find($id);
        $values = array('titre' => 'Utilisateurs du campus', 'campus' => $campus);
        $values['users'] = User::orderBy('fullname')->orderBy('name')->get();
        $values['selected'] = $campus ? DB::table('campus_user')->where('id_campus', '=', $campus->id)->lists('id_user') : array();
        if (session('success')) {
            Session::forget('success');
            $values['success'] = true;
        }
        return view('admin.campus_users', $values);
    }

    public function storeUsers(Request $request)
    {
        $this->validate($request, array('id_campus' => 'required|numeric'));
        $campus = Campus::find($request->input('id_campus'));
        if ($campus) {
            $this->syncPivotWithAudit('campus_user', 'id_campus', $campus->id, 'id_user', (array) $request->input('users', array()));
            session(array('success' => true));
        }
        return redirect()->to(URL::previous());
    }

    public function specialites($id)
    {
        $campus = Campus::find($id);
        $values = array('titre' => 'Specialites du campus', 'campus' => $campus);
        $values['classes'] = classe::orderBy('nom')->get();
        $values['selected'] = $campus ? DB::table('campus_specialite')->where('id_campus', '=', $campus->id)->lists('id_classe') : array();
        $values['specialites_affectees'] = $campus ? $campus->specialites()->orderBy('nom')->get() : array();
        if (session('success')) {
            Session::forget('success');
            $values['success'] = true;
        }
        return view('admin.campus_specialites', $values);
    }

    public function storeSpecialites(Request $request)
    {
        $this->validate($request, array('id_campus' => 'required|numeric'));
        $campus = Campus::find($request->input('id_campus'));
        if ($campus) {
            $this->syncPivotWithAudit('campus_specialite', 'id_campus', $campus->id, 'id_classe', (array) $request->input('classes', array()));
            session(array('success' => true));
        }
        return redirect()->to(URL::previous());
    }

    public function detachSpecialite($idCampus, $idClasse)
    {
        DB::table('campus_specialite')
            ->where('id_campus', '=', $idCampus)
            ->where('id_classe', '=', $idClasse)
            ->delete();
        session(array('success' => true));
        return redirect()->to(URL::previous());
    }

    private function fillCampus(Campus $campus, Request $request)
    {
        $fields = array('nom', 'sigle', 'localisation', 'ville', 'numero_agrement', 'reference', 'date_creation', 'telephone', 'email', 'site_web', 'autorisation_ouverture', 'arrete_creation', 'boite_postale');
        foreach ($fields as $field) {
            $campus->$field = $request->input($field);
        }
        if ($request->hasFile('logo_file') && $request->file('logo_file')->isValid()) {
            $file = $request->file('logo_file');
            $extension = strtolower($file->getClientOriginalExtension());
            $name = 'campus_'.date('YmdHis').'_'.uniqid().'.'.$extension;
            $destination = public_path('img/campus');
            if (!is_dir($destination)) {
                mkdir($destination, 0775, true);
            }
            $file->move($destination, $name);
            $campus->logo = 'img/campus/'.$name;
        } elseif ($request->input('logo')) {
            $campus->logo = $request->input('logo');
        }
    }

    private function setAuditFields($model, $isNew)
    {
        $user = \Auth::user();
        if ($user) {
            if ($isNew || !$model->id_creator) {
                $model->id_creator = $user->id;
            }
            $model->id_last_editor = $user->id;
        }
    }

    private function syncPivotWithAudit($table, $ownerColumn, $ownerId, $relatedColumn, $ids)
    {
        $ids = array_values(array_filter($ids));
        $now = date('Y-m-d H:i:s');
        $user = \Auth::user();
        $userId = $user ? $user->id : null;

        if (count($ids) > 0) {
            DB::table($table)->where($ownerColumn, '=', $ownerId)->whereNotIn($relatedColumn, $ids)->delete();
        } else {
            DB::table($table)->where($ownerColumn, '=', $ownerId)->delete();
        }

        foreach ($ids as $id) {
            $exists = DB::table($table)->where($ownerColumn, '=', $ownerId)->where($relatedColumn, '=', $id)->first();
            if ($exists) {
                DB::table($table)->where('id', '=', $exists->id)->update(array(
                    'id_last_editor' => $userId,
                    'updated_at' => $now
                ));
            } else {
                DB::table($table)->insert(array(
                    $ownerColumn => $ownerId,
                    $relatedColumn => $id,
                    'id_creator' => $userId,
                    'id_last_editor' => $userId,
                    'created_at' => $now,
                    'updated_at' => $now
                ));
            }
        }
    }
}
