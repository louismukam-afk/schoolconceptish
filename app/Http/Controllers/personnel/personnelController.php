<?php namespace gesecole\Http\Controllers\personnel;

use gesecole\fonction;
use gesecole\fonction_personnel;
use gesecole\Http\Requests;
use gesecole\Http\Controllers\Controller;
use gesecole\personnel;
use gesecole\parametres;
use gesecole\taux;
use gesecole\titulaire;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class personnelController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
    public function __construct()
    {

        session(['big_title'=>trans('menu.gestion_personnel')]);
        $this->renewSchoolYear();
    }
	public function index()
	{
        return view('personnel/index');
	}

     public function index_personnel(){
         $titre=trans('admin.personnel');
         $m=personnel::whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))->orderBy('nom')->get();
         $values= array('titre'=>$titre,'personnel'=>$m);
            if(session('success')){
                $values['success']=true;
                Session::forget('success');
            }
            if(session('import_success')){
                $values['import_success']=session('import_success');
                Session::forget('import_success');
            }


         return view('personnel/personne',$values);
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
	public function store(Request $req)
	{
        $this->validate($req,[
            'nom'=>'required',
            'date_naiss'=>'required|date',
            'lieu_naiss'=>'required',
            'nationalite'=>'required',
            'sexe'=>'required',
            'tel1'=>'required',
            'adresse'=>'required',
            'type'=>'required|numeric',
            'date_rec'=>'date',


        ]);
        $date_rec=$req->input('date_rec');
        $taux=$req->input('taux');

        $c=new personnel();
        $c->id_campus=$this->currentCampusId();
        $c->nom=$req->input('nom');
        $c->nationalite=$req->input('nationalite');
        $c->adresse=$req->input('adresse');
        $c->sexe=$req->input('sexe');
        $c->diplome=$req->input('diplome');
        $c->tel1=$req->input('tel1');
        $c->tel2=$req->input('tel2');
        $c->date_naiss=$req->input('date_naiss');
        $c->lieu_naiss=$req->input('lieu_naiss');
        if($date_rec==null)
            $c->date_entree=date('Y-m-d');
        else
            $c->date_entree=$date_rec;
        $c->email=$req->input('email');
        $c->type=$req->input('type');
        $c->type_personnel=$req->input('type_personnel','enseignant');
        $c->mode_horaire=$req->input('mode_horaire','strict');
        $c->categorie_horaire=$req->input('categorie_horaire','standard');
        $c->horaire_travail=$req->input('horaire_travail');
        $c->matieres=$req->input('matieres');
        $c->autre_information=$req->input('autre_information');
        $c->num_contribuable=$req->input('num_contribuable');
        $c->num_ss=$req->input('num_ss');
        $c->categorie=$req->input('categorie');
        $c->echelon=$req->input('echelon');

        $c->save();

        if($taux)
        {
            $t=new taux();
            $t->id_personnel=$c->id;
            $t->taux=$taux;
            $t->save();
        }

        $param = parametres::all();
        $pa = $param->where('nom', 'initiales_etablissement', false)->first();

        $this->setPersonnelMatricule($c, $pa);

        session(['success'=>true]);
        return redirect()->action('personnel\personnelController@index_personnel');
	}

    public function downloadImportTemplate()
    {
        $headers = array(
            'nom',
            'date_naiss',
            'lieu_naiss',
            'nationalite',
            'sexe',
            'tel1',
            'adresse',
            'type',
            'date_rec',
            'diplome',
            'email',
            'tel2',
            'type_personnel',
            'mode_horaire',
            'categorie_horaire',
            'horaire_travail',
            'matieres',
            'autre_information',
            'num_contribuable',
            'num_ss',
            'categorie',
            'echelon',
            'taux'
        );

        $example = array(
            'Jean Dupont',
            '1985-05-20',
            'Yaounde',
            'Camerounaise',
            'H',
            '699000000',
            'Quartier Exemple',
            '0',
            date('Y-m-d'),
            'Licence',
            'jean@example.com',
            '',
            'enseignant',
            'strict',
            'standard',
            'enseignant_jour',
            'Mathematiques',
            '',
            '',
            '',
            '',
            '',
            '2500'
        );

        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, $headers, ';');
        fputcsv($stream, $example, ';');
        rewind($stream);
        $content = "\xEF\xBB\xBF".stream_get_contents($stream);
        fclose($stream);

        return Response::make($content, 200, array(
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_personnel.csv"',
        ));
    }

    public function importFromExcel(Request $req)
    {
        $this->validate($req, array(
            'fichier_personnel' => 'required'
        ));

        $file = $req->file('fichier_personnel');
        if(!$file || !$file->isValid()) {
            return redirect()->to(URL::previous())->withErrors(array('fichier_personnel' => 'Le fichier est invalide.'));
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if(!in_array($extension, array('csv', 'txt', 'xlsx'))) {
            return redirect()->to(URL::previous())->withErrors(array('fichier_personnel' => 'Utilisez le template CSV telecharge depuis cette page ou un fichier Excel .xlsx.'));
        }

        $rows = $this->readPersonnelImportRows($file->getRealPath(), $extension);
        if(!$rows) {
            return redirect()->to(URL::previous())->withErrors(array('fichier_personnel' => 'Impossible de lire le fichier.'));
        }

        $created = 0;
        $lineNumber = 0;
        $headers = array();
        $param = parametres::all();
        $pa = $param->where('nom', 'initiales_etablissement', false)->first();

        foreach($rows as $row) {
            $lineNumber++;
            if($lineNumber == 1) {
                $headers = $this->normalizeImportHeaders($row);
                continue;
            }

            if(count(array_filter($row, 'strlen')) == 0) {
                continue;
            }

            $data = $this->combineImportRow($headers, $row);
            $error = $this->validatePersonnelImportRow($data, $lineNumber);
            if($error) {
                return redirect()->to(URL::previous())->withErrors(array('fichier_personnel' => $error));
            }

            $c = new personnel();
            $this->fillPersonnelFromImportRow($c, $data);
            $c->id_campus = $this->currentCampusId();
            $c->save();
            $this->setPersonnelMatricule($c, $pa);

            if(isset($data['taux']) && trim($data['taux']) !== '') {
                $t = new taux();
                $t->id_personnel = $c->id;
                $t->taux = $data['taux'];
                $t->save();
            }

            $created++;
        }

        session(array('import_success' => $created.' personnel importe(s) avec succes.'));

        return redirect()->action('personnel\personnelController@index_personnel');
    }

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show()
	{
        $titre=trans('admin.search_personnel');
        $values= array('titre'=>$titre);
        return view('personnel/search_personnel',$values);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
        $titre=trans('admin.infos_personnel');
        $p=personnel::where('id', '=', $id)->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))->first();
        if(!$p) { abort(404); }
        $f2=fonction::all();
        $fp=fonction_personnel::whereIdPersonnel($id)->get();
        $t=titulaire::whereAnneeScolaire(\session('year'))->where('id_personnel','=',$id)->get()->first();
        $values= array('titre'=>$titre,'personnel'=>$p,'fonctions'=>$fp,'titulaire'=>$t,'fonction'=>$f2);

        if(session('success')){
            $values['success']=true;
            Session::forget('success');
        }

        return view('personnel/edit_personnel',$values);

    }

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update(Request $req,$id)
	{
        $this->validate($req,[
            'nom'=>'required',
            'date_naiss'=>'required',
            'lieu_naiss'=>'required',
            'sexe'=>'required',
            'nationalite'=>'required',
            'tel1'=>'required',
            'adresse'=>'required',
            'type'=>'required|numeric',
            'date_rec'=>'date',


        ]);
        $date_rec=$req->input('date_rec');


        $c=personnel::where('id', '=', $id)->where('id_campus', '=', $this->currentCampusId())->first();
        if(!$c) { abort(404); }
        $c->nom=$req->input('nom');
        $c->nationalite=$req->input('nationalite');
        $c->adresse=$req->input('adresse');
        $c->sexe=$req->input('sexe');
        $c->diplome=$req->input('diplome');
        if($date_rec==null)
        {}
        else
            $c->date_entree=$date_rec;
        $c->tel1=$req->input('tel1');
        $c->tel2=$req->input('tel2');
        $c->date_naiss=$req->input('date_naiss');
        $c->lieu_naiss=$req->input('lieu_naiss');
        $c->email=$req->input('email');
        $c->type=$req->input('type');
        $c->type_personnel=$req->input('type_personnel','enseignant');
        $c->mode_horaire=$req->input('mode_horaire','strict');
        $c->categorie_horaire=$req->input('categorie_horaire','standard');
        $c->horaire_travail=$req->input('horaire_travail');
        $c->matieres=$req->input('matieres');
        $c->autre_information=$req->input('autre_information');
        $c->num_contribuable=$req->input('num_contribuable');
        $c->num_ss=$req->input('num_ss');
        $c->categorie=$req->input('categorie');
        $c->echelon=$req->input('echelon');

        $c->save();
        session(['success'=>true]);
        return redirect()->action('personnel\personnelController@edit',[$id]);
	}

    public function changeActiveState($id){
        $p=personnel::where('id', '=', $id)->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))->first();
        if($p!=null){
            if ($p->actif==1)
                $p->actif=0;
            else
                $p->actif=1;
            $p->save();
            session(['success'=>true]);
        }

        return redirect()->action('personnel\personnelController@index_personnel');
    }

    public function activate($id){
        $p=personnel::where('id', '=', $id)->where('id_campus', '=', $this->currentCampusId())->first();
        if($p!=null){
            $p->actif=1;
            $p->save();
            session(['success'=>true]);
        }

        return redirect()->action('personnel\personnelController@index_personnel');
    }

    public function deactivate($id){
        $p=personnel::where('id', '=', $id)->where('id_campus', '=', $this->currentCampusId())->first();
        if($p!=null){
            $p->actif=0;
            $p->save();
            session(['success'=>true]);
        }

        return redirect()->action('personnel\personnelController@index_personnel');
    }

    private function normalizeImportHeaders($headers)
    {
        $normalized = array();
        foreach($headers as $header) {
            $normalized[] = strtolower(trim($header, " \t\n\r\0\x0B\xEF\xBB\xBF"));
        }

        return $normalized;
    }

    private function readPersonnelImportRows($path, $extension)
    {
        if(in_array($extension, array('csv', 'txt'))) {
            return $this->readPersonnelCsvRows($path);
        }

        return $this->readPersonnelXlsxRows($path);
    }

    private function readPersonnelCsvRows($path)
    {
        $handle = fopen($path, 'r');
        if(!$handle) {
            return null;
        }

        $rows = array();
        while(($row = fgetcsv($handle, 0, ';')) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
    }

    private function readPersonnelXlsxRows($path)
    {
        if(!class_exists('\ZipArchive')) {
            return null;
        }

        $zip = new \ZipArchive();
        if($zip->open($path) !== true) {
            return null;
        }

        $sharedStrings = $this->readXlsxSharedStrings($zip);
        $sheetContent = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if(!$sheetContent) {
            return null;
        }

        $sheet = simplexml_load_string($sheetContent);
        if(!$sheet || !isset($sheet->sheetData)) {
            return null;
        }

        $rows = array();
        foreach($sheet->sheetData->row as $sheetRow) {
            $row = array();
            foreach($sheetRow->c as $cell) {
                $attributes = $cell->attributes();
                $cellRef = isset($attributes['r']) ? (string) $attributes['r'] : '';
                $index = $this->xlsxColumnIndex($cellRef);
                $type = isset($attributes['t']) ? (string) $attributes['t'] : '';
                $value = '';

                if($type == 's') {
                    $sharedIndex = (int) $cell->v;
                    $value = isset($sharedStrings[$sharedIndex]) ? $sharedStrings[$sharedIndex] : '';
                } elseif($type == 'inlineStr' && isset($cell->is->t)) {
                    $value = (string) $cell->is->t;
                } elseif(isset($cell->v)) {
                    $value = (string) $cell->v;
                }

                $row[$index] = trim($value);
            }

            if(count($row) > 0) {
                ksort($row);
                $rows[] = $this->normalizeXlsxRowIndexes($row);
            }
        }

        return $rows;
    }

    private function readXlsxSharedStrings(\ZipArchive $zip)
    {
        $content = $zip->getFromName('xl/sharedStrings.xml');
        if(!$content) {
            return array();
        }

        $xml = simplexml_load_string($content);
        if(!$xml) {
            return array();
        }

        $strings = array();
        foreach($xml->si as $si) {
            if(isset($si->t)) {
                $strings[] = (string) $si->t;
                continue;
            }

            $text = '';
            if(isset($si->r)) {
                foreach($si->r as $run) {
                    if(isset($run->t)) {
                        $text .= (string) $run->t;
                    }
                }
            }
            $strings[] = $text;
        }

        return $strings;
    }

    private function xlsxColumnIndex($cellRef)
    {
        $letters = preg_replace('/[^A-Z]/', '', strtoupper($cellRef));
        $index = 0;
        for($i = 0; $i < strlen($letters); $i++) {
            $index = ($index * 26) + (ord($letters[$i]) - 64);
        }

        return $index - 1;
    }

    private function normalizeXlsxRowIndexes($row)
    {
        $normalized = array();
        $max = max(array_keys($row));
        for($i = 0; $i <= $max; $i++) {
            $normalized[] = isset($row[$i]) ? $row[$i] : '';
        }

        return $normalized;
    }

    private function combineImportRow($headers, $row)
    {
        $data = array();
        foreach($headers as $index => $header) {
            if($header !== '') {
                $data[$header] = isset($row[$index]) ? trim($row[$index]) : '';
            }
        }

        return $data;
    }

    private function validatePersonnelImportRow($data, $lineNumber)
    {
        $required = array('nom', 'date_naiss', 'lieu_naiss', 'nationalite', 'sexe', 'tel1', 'adresse', 'type');
        foreach($required as $field) {
            if(!isset($data[$field]) || trim($data[$field]) === '') {
                return 'Ligne '.$lineNumber.' : le champ '.$field.' est obligatoire.';
            }
        }

        if(!$this->normalizeImportDate($data['date_naiss'])) {
            return 'Ligne '.$lineNumber.' : date_naiss doit etre au format AAAA-MM-JJ ou JJ/MM/AAAA.';
        }

        if(isset($data['date_rec']) && trim($data['date_rec']) !== '' && !$this->normalizeImportDate($data['date_rec'])) {
            return 'Ligne '.$lineNumber.' : date_rec doit etre au format AAAA-MM-JJ ou JJ/MM/AAAA.';
        }

        $sexe = strtoupper($data['sexe']);
        if(!in_array($sexe, array('H', 'F'))) {
            return 'Ligne '.$lineNumber.' : sexe doit etre H ou F.';
        }

        if(!is_numeric($data['type'])) {
            return 'Ligne '.$lineNumber.' : type doit etre 0 pour enseignant ou 1 pour autre.';
        }

        if(isset($data['taux']) && trim($data['taux']) !== '' && !is_numeric($data['taux'])) {
            return 'Ligne '.$lineNumber.' : taux doit etre numerique.';
        }

        return null;
    }

    private function normalizeImportDate($date)
    {
        $date = trim($date);
        $formats = array('Y-m-d', 'd/m/Y', 'd-m-Y');
        foreach($formats as $format) {
            $parsed = \DateTime::createFromFormat($format, $date);
            if($parsed && $parsed->format($format) === $date) {
                return $parsed->format('Y-m-d');
            }
        }

        if(is_numeric($date)) {
            $timestamp = ((int) $date - 25569) * 86400;
            if($timestamp > 0) {
                return gmdate('Y-m-d', $timestamp);
            }
        }

        return null;
    }

    private function fillPersonnelFromImportRow(personnel $c, $data)
    {
        $c->nom = $data['nom'];
        $c->nationalite = $data['nationalite'];
        $c->adresse = $data['adresse'];
        $c->sexe = strtoupper($data['sexe']);
        $c->diplome = isset($data['diplome']) ? $data['diplome'] : '';
        $c->tel1 = $data['tel1'];
        $c->tel2 = isset($data['tel2']) ? $data['tel2'] : '';
        $c->date_naiss = $this->normalizeImportDate($data['date_naiss']);
        $c->lieu_naiss = $data['lieu_naiss'];
        $c->date_entree = isset($data['date_rec']) && $data['date_rec'] ? $this->normalizeImportDate($data['date_rec']) : date('Y-m-d');
        $c->email = isset($data['email']) ? $data['email'] : '';
        $c->type = $data['type'];
        $c->type_personnel = isset($data['type_personnel']) && $data['type_personnel'] ? $data['type_personnel'] : 'enseignant';
        $c->mode_horaire = isset($data['mode_horaire']) && $data['mode_horaire'] ? $data['mode_horaire'] : 'strict';
        $c->categorie_horaire = isset($data['categorie_horaire']) && $data['categorie_horaire'] ? $data['categorie_horaire'] : 'standard';
        $c->horaire_travail = isset($data['horaire_travail']) ? $data['horaire_travail'] : '';
        $c->matieres = isset($data['matieres']) ? $data['matieres'] : '';
        $c->autre_information = isset($data['autre_information']) ? $data['autre_information'] : '';
        $c->num_contribuable = isset($data['num_contribuable']) ? $data['num_contribuable'] : '';
        $c->num_ss = isset($data['num_ss']) ? $data['num_ss'] : '';
        $c->categorie = isset($data['categorie']) ? $data['categorie'] : '';
        $c->echelon = isset($data['echelon']) ? $data['echelon'] : '';
    }

    private function setPersonnelMatricule(personnel $c, $pa)
    {
        $front = session('year');
        $front = substr($front, 2, 2);
        $number = sprintf('%03d', $c->id);
        $initiales = $pa ? $pa->valeur : '';
        $c->matricule = $front . $initiales .'P'.$number;
        $c->save();
    }

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        try {
            $p=personnel::where('id', '=', $id)->where('id_campus', '=', $this->currentCampusId())->first();
            if($p) {
                $p->delete();
            }
            session(['success'=>true]);
        } catch (QueryException $fe) {
            return  redirect()->to(URL::previous())->withErrors(['constraint_violation' => trans('admin.cannot_delete')]);
        }
        return redirect()->to(\URL::previous());
	}

}
