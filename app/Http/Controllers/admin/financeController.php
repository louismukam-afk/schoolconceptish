<?php namespace gesecole\Http\Controllers\admin;

use gesecole\Banque;
use gesecole\Campus;
use gesecole\Caisse;
use gesecole\Http\Controllers\Controller;
use gesecole\MouvementFinancier;
use gesecole\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class financeController extends Controller {
    public function __construct()
    {
        session(array('big_title' => trans('admin.dashboard')));
        $this->renewSchoolYear();
    }

    public function etat(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user && $user->isAdmin();
        $campusOnly = $request->attributes->get('campus_only', false);
        $dateDebut = $request->input('date_debut', date('Y-m-01'));
        $dateFin = $request->input('date_fin', date('Y-m-d'));
        $source = $request->input('source', 'all');
        $idCampus = $campusOnly ? $this->currentCampusId() : ($isAdmin ? $request->input('id_campus', $this->currentCampusId()) : $this->currentCampusId());
        $idUser = $isAdmin ? $request->input('id_user') : ($user ? $user->id : null);
        $idCaisse = $request->input('id_caisse');
        $idBanque = $request->input('id_banque');

        $caisses = $this->availableCaisses($isAdmin, $idUser, $idCaisse);
        $banques = $this->availableBanques($isAdmin, $idUser, $idBanque);
        $caisseIds = $this->collectionIds($caisses);
        $banqueIds = $this->collectionIds($banques);

        $query = MouvementFinancier::whereBetween('date_operation', array($dateDebut, $dateFin));
        if ($idCampus) {
            $query->whereRaw($this->campusVisibilitySql('id_campus'), array($idCampus));
        }
        $this->applyFinancialScope($query, $source, $caisseIds, $banqueIds);

        $summaries = $this->financialSummaries($dateDebut, $dateFin, $idCampus, $source, $caisses, $banques);
        $totals = $this->summaryTotals($summaries);

        $values = array(
            'titre' => 'Etat financier',
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'source' => $source,
            'id_campus' => $idCampus,
            'id_user' => $idUser,
            'id_caisse' => $idCaisse,
            'id_banque' => $idBanque,
            'is_admin' => $isAdmin,
            'can_filter_admin' => $isAdmin && !$campusOnly,
            'campus_only' => $campusOnly,
            'campus_actif' => $this->currentCampus(),
            'campus' => $isAdmin ? Campus::orderBy('nom')->get() : array(),
            'users' => $isAdmin ? User::orderBy('fullname')->orderBy('name')->get() : array(),
            'caisses' => $caisses,
            'banques' => $banques,
            'summaries' => $summaries,
            'mouvements' => $query->with('creator')->orderBy('date_operation', 'desc')->paginate(50),
            'total_entree' => $totals['entree'],
            'total_sortie' => $totals['sortie']
        );

        return view('admin.finance_etat', $values);
    }

    public function etatCampus(Request $request)
    {
        $request->attributes->set('campus_only', true);
        return $this->etat($request);
    }

    public function transfert()
    {
        $values = array('titre' => 'Transferts financiers');
        $values['caisses_entree'] = Caisse::where('type_caisse', '=', 'entree')->orderBy('nom')->get();
        $values['caisses_sortie'] = Caisse::where('type_caisse', '=', 'sortie')->orderBy('nom')->get();
        $values['caisses'] = Caisse::orderBy('nom')->get();
        $values['banques'] = Banque::orderBy('nom')->get();
        if (session('success')) {
            Session::forget('success');
            $values['success'] = true;
        }
        return view('admin.finance_transfert', $values);
    }

    public function storeTransfert(Request $request)
    {
        $this->validate($request, array(
            'type_transfert' => 'required',
            'montant' => 'required|numeric',
            'date_operation' => 'required'
        ));

        $type = $request->input('type_transfert', 'caisse_caisse');
        $montant = (float) $request->input('montant');
        if ($montant <= 0) {
            return redirect()->to(URL::previous());
        }

        if ($type == 'caisse_banque') {
            $source = Caisse::find($request->input('id_caisse_source'));
            $destination = Banque::find($request->input('id_banque_destination'));
            if ($source && $destination) {
                $source->solde = (float) $source->solde - $montant;
                $destination->solde = (float) $destination->solde + $montant;
                $source->save();
                $destination->save();
                $this->saveTransferMovement('caisse_banque', $montant, $request, $source->id, null, null, $destination->id);
            }
        }
        elseif ($type == 'banque_banque') {
            $source = Banque::find($request->input('id_banque_source'));
            $destination = Banque::find($request->input('id_banque_destination'));
            if ($source && $destination && $source->id != $destination->id) {
                $source->solde = (float) $source->solde - $montant;
                $destination->solde = (float) $destination->solde + $montant;
                $source->save();
                $destination->save();
                $this->saveTransferMovement('banque_banque', $montant, $request, null, null, $source->id, $destination->id);
            }
        }
        else {
            $source = Caisse::find($request->input('id_caisse_source'));
            $destination = Caisse::find($request->input('id_caisse_destination'));
            if ($source && $destination && $source->id != $destination->id) {
                $source->solde = (float) $source->solde - $montant;
                $destination->solde = (float) $destination->solde + $montant;
                $source->save();
                $destination->save();
                $this->saveTransferMovement('caisse_caisse', $montant, $request, $source->id, $destination->id, null, null);
            }
        }

        session(array('success' => true));
        return redirect()->to(URL::previous());
    }

    public function affectations()
    {
        $values = array('titre' => 'Affectations caisses et banques');
        $values['users'] = User::orderBy('fullname')->orderBy('name')->get();
        $values['caisses'] = Caisse::orderBy('nom')->get();
        $values['banques'] = Banque::orderBy('nom')->get();
        $values['selected_caisses'] = $this->getSelectedFinancialAssignments('caisse_user', 'id_caisse');
        $values['selected_banques'] = $this->getSelectedFinancialAssignments('banque_user', 'id_banque');
        if (session('success')) {
            Session::forget('success');
            $values['success'] = true;
        }
        return view('admin.finance_affectations', $values);
    }

    public function storeAffectations(Request $request)
    {
        $this->validate($request, array(
            'id_user' => 'required|numeric'
        ));

        $idUser = $request->input('id_user');
        $this->syncUserFinancialPivot('caisse_user', 'id_caisse', $idUser, (array) $request->input('caisses', array()));
        $this->syncUserFinancialPivot('banque_user', 'id_banque', $idUser, (array) $request->input('banques', array()));
        session(array('success' => true));
        return redirect()->to(URL::previous());
    }

    private function saveTransferMovement($source, $montant, Request $request, $idCaisseSource, $idCaisseDestination, $idBanqueSource, $idBanqueDestination)
    {
        $m = new MouvementFinancier();
        $m->type_operation = 'transfert';
        $m->source = $source;
        $m->id_caisse_source = $idCaisseSource;
        $m->id_caisse_destination = $idCaisseDestination;
        $m->id_banque_source = $idBanqueSource;
        $m->id_banque_destination = $idBanqueDestination;
        $m->id_campus = $this->currentCampusId();
        $m->montant = $montant;
        $m->date_operation = $request->input('date_operation');
        $m->motif = $request->input('motif', 'Transfert financier');
        $m->reference = $request->input('reference');
        $m->id_user = Auth::user() ? Auth::user()->id : null;
        $m->id_creator = Auth::user() ? Auth::user()->id : null;
        $m->id_last_editor = Auth::user() ? Auth::user()->id : null;
        $m->save();
    }

    private function syncUserFinancialPivot($table, $resourceColumn, $idUser, $ids)
    {
        $ids = array_values(array_filter($ids));
        $now = date('Y-m-d H:i:s');
        $currentUserId = Auth::user() ? Auth::user()->id : null;

        if(count($ids) > 0) {
            DB::table($table)->where('id_user', '=', $idUser)->whereNotIn($resourceColumn, $ids)->delete();
        }
        else {
            DB::table($table)->where('id_user', '=', $idUser)->delete();
        }

        foreach($ids as $id) {
            $pivot = DB::table($table)->where('id_user', '=', $idUser)->where($resourceColumn, '=', $id)->first();
            if($pivot) {
                DB::table($table)->where('id', '=', $pivot->id)->update(array(
                    'id_last_editor' => $currentUserId,
                    'updated_at' => $now
                ));
            }
            else {
                DB::table($table)->insert(array(
                    'id_user' => $idUser,
                    $resourceColumn => $id,
                    'id_creator' => $currentUserId,
                    'id_last_editor' => $currentUserId,
                    'created_at' => $now,
                    'updated_at' => $now
                ));
            }
        }
    }

    private function getSelectedFinancialAssignments($table, $resourceColumn)
    {
        $rows = DB::table($table)->get();
        $selected = array();
        foreach($rows as $row) {
            if(!isset($selected[$row->id_user])) {
                $selected[$row->id_user] = array();
            }
            $selected[$row->id_user][] = $row->$resourceColumn;
        }
        return $selected;
    }

    private function availableCaisses($isAdmin, $idUser, $idCaisse)
    {
        $query = Caisse::orderBy('nom');
        if (!$isAdmin || $idUser) {
            $ids = DB::table('caisse_user')->where('id_user', '=', $idUser)->lists('id_caisse');
            if (count($ids) <= 0) {
                return Caisse::whereRaw('1=0')->get();
            }
            $query->whereIn('id', $ids);
        }
        if ($idCaisse) {
            $query->where('id', '=', $idCaisse);
        }
        return $query->get();
    }

    private function availableBanques($isAdmin, $idUser, $idBanque)
    {
        $query = Banque::orderBy('nom');
        if (!$isAdmin || $idUser) {
            $ids = DB::table('banque_user')->where('id_user', '=', $idUser)->lists('id_banque');
            if (count($ids) <= 0) {
                return Banque::whereRaw('1=0')->get();
            }
            $query->whereIn('id', $ids);
        }
        if ($idBanque) {
            $query->where('id', '=', $idBanque);
        }
        return $query->get();
    }

    private function collectionIds($collection)
    {
        $ids = array();
        foreach ($collection as $item) {
            $ids[] = $item->id;
        }
        return $ids;
    }

    private function applyFinancialScope($query, $source, $caisseIds, $banqueIds)
    {
        if (($source == 'all' || $source == 'caisse') && count($caisseIds) <= 0 && ($source == 'caisse')) {
            $query->whereRaw('1=0');
            return;
        }
        if (($source == 'all' || $source == 'banque') && count($banqueIds) <= 0 && ($source == 'banque')) {
            $query->whereRaw('1=0');
            return;
        }
        if ($source == 'all' && count($caisseIds) <= 0 && count($banqueIds) <= 0) {
            $query->whereRaw('1=0');
            return;
        }

        $query->where(function($q) use ($source, $caisseIds, $banqueIds) {
            if (($source == 'all' || $source == 'caisse') && count($caisseIds) > 0) {
                $q->orWhere(function($sub) use ($caisseIds) {
                    $sub->where('source', '=', 'caisse')->whereIn('id_caisse', $caisseIds);
                });
                $q->orWhere(function($sub) use ($caisseIds) {
                    $sub->whereIn('source', array('caisse_caisse', 'caisse_banque'))
                        ->where(function($inside) use ($caisseIds) {
                            $inside->whereIn('id_caisse_source', $caisseIds)
                                ->orWhereIn('id_caisse_destination', $caisseIds);
                        });
                });
            }
            if (($source == 'all' || $source == 'banque') && count($banqueIds) > 0) {
                $q->orWhere(function($sub) use ($banqueIds) {
                    $sub->where('source', '=', 'banque')->whereIn('id_banque', $banqueIds);
                });
                $q->orWhere(function($sub) use ($banqueIds) {
                    $sub->whereIn('source', array('caisse_banque', 'banque_banque'))
                        ->where(function($inside) use ($banqueIds) {
                            $inside->whereIn('id_banque_source', $banqueIds)
                                ->orWhereIn('id_banque_destination', $banqueIds);
                        });
                });
            }
        });
    }

    private function financialSummaries($dateDebut, $dateFin, $idCampus, $source, $caisses, $banques)
    {
        $summaries = array();
        if ($source == 'all' || $source == 'caisse') {
            foreach ($caisses as $caisse) {
                $entree = $this->resourceSum('caisse', $caisse->id, 'entree', $dateDebut, $dateFin, $idCampus);
                $sortie = $this->resourceSum('caisse', $caisse->id, 'sortie', $dateDebut, $dateFin, $idCampus);
                $summaries[] = array(
                    'type' => 'Caisse',
                    'nom' => $caisse->nom,
                    'nature' => $caisse->type_caisse,
                    'entree' => $entree,
                    'sortie' => $sortie,
                    'solde_periode' => $entree - $sortie,
                    'solde_actuel' => $caisse->solde
                );
            }
        }
        if ($source == 'all' || $source == 'banque') {
            foreach ($banques as $banque) {
                $entree = $this->resourceSum('banque', $banque->id, 'entree', $dateDebut, $dateFin, $idCampus);
                $sortie = $this->resourceSum('banque', $banque->id, 'sortie', $dateDebut, $dateFin, $idCampus);
                $summaries[] = array(
                    'type' => 'Banque',
                    'nom' => $banque->nom,
                    'nature' => $banque->numero_compte,
                    'entree' => $entree,
                    'sortie' => $sortie,
                    'solde_periode' => $entree - $sortie,
                    'solde_actuel' => $banque->solde
                );
            }
        }
        return $summaries;
    }

    private function resourceSum($resourceType, $resourceId, $movementType, $dateDebut, $dateFin, $idCampus)
    {
        $base = DB::table('mouvements_financiers')->whereBetween('date_operation', array($dateDebut, $dateFin));
        if ($idCampus) {
            $base->whereRaw($this->campusVisibilitySql('id_campus'), array($idCampus));
        }

        $direct = clone $base;
        $direct->where('type_operation', '=', $movementType);
        if ($resourceType == 'caisse') {
            $direct->where('source', '=', 'caisse')->where('id_caisse', '=', $resourceId);
        } else {
            $direct->where('source', '=', 'banque')->where('id_banque', '=', $resourceId);
        }

        $transfer = clone $base;
        $transfer->where('type_operation', '=', 'transfert');
        if ($resourceType == 'caisse' && $movementType == 'entree') {
            $transfer->where('source', '=', 'caisse_caisse')->where('id_caisse_destination', '=', $resourceId);
        } elseif ($resourceType == 'caisse') {
            $transfer->whereIn('source', array('caisse_caisse', 'caisse_banque'))->where('id_caisse_source', '=', $resourceId);
        } elseif ($resourceType == 'banque' && $movementType == 'entree') {
            $transfer->whereIn('source', array('caisse_banque', 'banque_banque'))->where('id_banque_destination', '=', $resourceId);
        } else {
            $transfer->where('source', '=', 'banque_banque')->where('id_banque_source', '=', $resourceId);
        }

        return (float) $direct->sum('montant') + (float) $transfer->sum('montant');
    }

    private function summaryTotals($summaries)
    {
        $totals = array('entree' => 0, 'sortie' => 0);
        foreach ($summaries as $summary) {
            $totals['entree'] += $summary['entree'];
            $totals['sortie'] += $summary['sortie'];
        }
        return $totals;
    }
}
