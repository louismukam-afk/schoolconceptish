<?php namespace gesecole\Http\Controllers\admin;

use gesecole\Banque;
use gesecole\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class banqueController extends Controller {
    public function __construct()
    {
        session(array('big_title' => trans('admin.dashboard')));
        $this->renewSchoolYear();
    }

    public function index()
    {
        $values = array('titre' => 'Gestion des banques');
        $values['banques'] = Banque::orderBy('nom')->paginate(20);
        if (session('success')) {
            Session::forget('success');
            $values['success'] = true;
        }
        return view('admin.banques', $values);
    }

    public function store(Request $request)
    {
        $this->validate($request, array('nom' => 'required', 'solde' => 'numeric'));
        $banque = new Banque();
        $this->fillBanque($banque, $request);
        $this->setAuditFields($banque, true);
        $banque->save();
        session(array('success' => true));
        return redirect()->to(URL::previous());
    }

    public function update(Request $request)
    {
        $this->validate($request, array('id' => 'required|numeric', 'nom' => 'required', 'solde' => 'numeric'));
        $banque = Banque::find($request->input('id'));
        if (!$banque) {
            return redirect()->to(URL::previous());
        }
        $this->fillBanque($banque, $request);
        $this->setAuditFields($banque, false);
        $banque->save();
        session(array('success' => true));
        return redirect()->to(URL::previous());
    }

    public function destroy($id)
    {
        $banque = Banque::find($id);
        if ($banque) {
            $banque->delete();
            session(array('success' => true));
        }
        return redirect()->to(URL::previous());
    }

    private function fillBanque(Banque $banque, Request $request)
    {
        $banque->nom = $request->input('nom');
        $banque->localisation = $request->input('localisation');
        $banque->numero_compte = $request->input('numero_compte');
        $banque->ville = $request->input('ville');
        $banque->solde = $request->input('solde', 0);
        $banque->description = $request->input('description');
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
