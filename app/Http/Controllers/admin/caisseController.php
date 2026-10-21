<?php namespace gesecole\Http\Controllers\admin;

use gesecole\Caisse;
use gesecole\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class caisseController extends Controller {
    public function __construct()
    {
        session(array('big_title' => trans('admin.dashboard')));
        $this->renewSchoolYear();
    }

    public function index()
    {
        $values = array('titre' => 'Gestion des caisses');
        $values['caisses'] = Caisse::orderBy('type_caisse')->orderBy('nom')->paginate(20);
        if (session('success')) {
            Session::forget('success');
            $values['success'] = true;
        }
        return view('admin.caisses', $values);
    }

    public function store(Request $request)
    {
        $this->validate($request, array('nom' => 'required', 'type_caisse' => 'required', 'solde' => 'numeric'));
        $caisse = new Caisse();
        $this->fillCaisse($caisse, $request);
        $this->setAuditFields($caisse, true);
        $caisse->save();
        session(array('success' => true));
        return redirect()->to(URL::previous());
    }

    public function update(Request $request)
    {
        $this->validate($request, array('id' => 'required|numeric', 'nom' => 'required', 'type_caisse' => 'required', 'solde' => 'numeric'));
        $caisse = Caisse::find($request->input('id'));
        if (!$caisse) {
            return redirect()->to(URL::previous());
        }
        $this->fillCaisse($caisse, $request);
        $this->setAuditFields($caisse, false);
        $caisse->save();
        session(array('success' => true));
        return redirect()->to(URL::previous());
    }

    public function destroy($id)
    {
        $caisse = Caisse::find($id);
        if ($caisse) {
            $caisse->delete();
            session(array('success' => true));
        }
        return redirect()->to(URL::previous());
    }

    private function fillCaisse(Caisse $caisse, Request $request)
    {
        $caisse->nom = $request->input('nom');
        $caisse->type_caisse = $request->input('type_caisse');
        $caisse->solde = $request->input('solde', 0);
        $caisse->description = $request->input('description');
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
}
