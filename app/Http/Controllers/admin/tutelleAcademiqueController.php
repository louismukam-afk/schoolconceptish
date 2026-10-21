<?php namespace gesecole\Http\Controllers\admin;

use gesecole\classe;
use gesecole\Http\Controllers\Controller;
use gesecole\TutelleAcademique;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class tutelleAcademiqueController extends Controller {
    public function __construct()
    {
        session(array('big_title' => trans('admin.dashboard')));
        $this->renewSchoolYear();
    }

    public function index()
    {
        $values = array('titre' => 'Tutelles academiques');
        $values['tutelles'] = TutelleAcademique::orderBy('nom')->paginate(20);
        if (session('success')) {
            Session::forget('success');
            $values['success'] = true;
        }
        return view('admin.tutelles', $values);
    }

    public function store(Request $request)
    {
        $this->validate($request, array('nom' => 'required', 'logo_file' => 'image', 'logo_faculte_file' => 'image'));
        $tutelle = new TutelleAcademique();
        $this->fillTutelle($tutelle, $request);
        $this->setAuditFields($tutelle, true);
        $tutelle->save();
        session(array('success' => true));
        return redirect()->to(URL::previous());
    }

    public function update(Request $request)
    {
        $this->validate($request, array('id' => 'required|numeric', 'nom' => 'required', 'logo_file' => 'image', 'logo_faculte_file' => 'image'));
        $tutelle = TutelleAcademique::find($request->input('id'));
        $this->fillTutelle($tutelle, $request);
        $this->setAuditFields($tutelle, false);
        $tutelle->save();
        session(array('success' => true));
        return redirect()->to(URL::previous());
    }

    public function destroy($id)
    {
        $tutelle = TutelleAcademique::find($id);
        if ($tutelle) {
            $tutelle->delete();
            session(array('success' => true));
        }
        return redirect()->to(URL::previous());
    }

    public function specialites($id)
    {
        $tutelle = TutelleAcademique::find($id);
        $values = array('titre' => 'Specialites de la tutelle', 'tutelle' => $tutelle);
        $values['classes'] = classe::orderBy('nom')->get();
        $values['selected'] = $tutelle ? DB::table('specialite_tutelle')->where('id_tutelle', '=', $tutelle->id)->lists('id_classe') : array();
        if (session('success')) {
            Session::forget('success');
            $values['success'] = true;
        }
        return view('admin.tutelle_specialites', $values);
    }

    public function storeSpecialites(Request $request)
    {
        $this->validate($request, array('id_tutelle' => 'required|numeric'));
        $tutelle = TutelleAcademique::find($request->input('id_tutelle'));
        if ($tutelle) {
            $this->syncPivotWithAudit('specialite_tutelle', 'id_tutelle', $tutelle->id, 'id_classe', (array) $request->input('classes', array()));
            session(array('success' => true));
        }
        return redirect()->to(URL::previous());
    }

    private function fillTutelle(TutelleAcademique $tutelle, Request $request)
    {
        $fields = array(
            'nom', 'ville', 'localisation', 'telephone', 'email', 'description1',
            'description2', 'ministere_tutelle', 'faculte',
            'republique_francophone', 'republique_anglophone',
            'patrie_francophone', 'patrie_anglophone',
            'ministere_tutelle_anglophone', 'faculte_anglophone',
            'tutelle_anglophone'
        );
        foreach ($fields as $field) {
            $tutelle->$field = $request->input($field);
        }
        $this->fillLogo($tutelle, $request, 'logo_file', 'logo', 'tutelle');
        $this->fillLogo($tutelle, $request, 'logo_faculte_file', 'logo_faculte', 'faculte');
    }

    private function fillLogo(TutelleAcademique $tutelle, Request $request, $fileInput, $modelField, $prefix)
    {
        if ($request->hasFile($fileInput) && $request->file($fileInput)->isValid()) {
            $file = $request->file($fileInput);
            $extension = strtolower($file->getClientOriginalExtension());
            $name = $prefix.'_'.date('YmdHis').'_'.uniqid().'.'.$extension;
            $destination = public_path('img/tutelles');
            if (!is_dir($destination)) {
                mkdir($destination, 0775, true);
            }
            $file->move($destination, $name);
            $tutelle->$modelField = 'img/tutelles/'.$name;
        } elseif ($request->input($modelField)) {
            $tutelle->$modelField = $request->input($modelField);
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
