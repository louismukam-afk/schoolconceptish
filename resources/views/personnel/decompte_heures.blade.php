@extends('skeleton')
@section('sub-content')
<?php
    $idPersonnel=\Illuminate\Support\Facades\Input::get('id_personnel');
    $idImport=\Illuminate\Support\Facades\Input::get('id_biometrie_import');
    $idCycle=\Illuminate\Support\Facades\Input::get('id_cycle');
    $idNiveau=\Illuminate\Support\Facades\Input::get('id_niveau');
    $idNniveau=\Illuminate\Support\Facades\Input::get('id_nniveau');
    $idClasse=\Illuminate\Support\Facades\Input::get('id_classe');
    $semestre=\Illuminate\Support\Facades\Input::get('semestre');
    $dateDebut=\Illuminate\Support\Facades\Input::get('date_debut');
    $dateFin=\Illuminate\Support\Facades\Input::get('date_fin');
?>

@if(session('success') || isset($success))
    <div class="alert alert-success">{{trans('admin.succes')}}</div>
@endif

@if(count($errors)>0)
    <div class="alert alert-danger">
        @foreach($errors->all() as $error)
            <div>{{$error}}</div>
        @endforeach
    </div>
@endif

<div class="row">
    <div class="col-md-8">
        <div class="panel panel-primary">
            <div class="panel-heading"><strong>Import biometrie</strong></div>
            <div class="panel-body">
                <form method="POST" action="{{route('biometrie_heures_store')}}" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{csrf_token()}}">
                    <div class="row">
                        <div class="col-md-4">
                            <label>Libelle</label>
                            <input type="text" name="libelle" class="form-control" placeholder="Presence enseignants">
                        </div>
                        <div class="col-md-3">
                            <label>Date debut</label>
                            <input type="date" name="date_debut" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label>Date fin</label>
                            <input type="date" name="date_fin" class="form-control" required>
                        </div>
                        <div class="col-md-2">
                            <label>Fichier</label>
                            <input type="file" name="fichier" class="form-control" required>
                        </div>
                    </div>
                    <div class="text-right" style="margin-top:12px">
                        <button class="btn btn-success">Importer les pointages</button>
                        <a href="{{route('biometrie_heures_manual_create')}}" class="btn btn-default">Saisie manuelle</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="panel panel-info">
            <div class="panel-heading"><strong>Exports</strong></div>
            <div class="panel-body">
                <a class="btn btn-primary btn-block" href="{{route('biometrie_heures_csv', \Illuminate\Support\Facades\Input::all())}}">Exporter CSV</a>
                <button type="button" class="btn btn-default btn-block" onclick="window.print()">Imprimer / PDF</button>
            </div>
        </div>
    </div>
</div>

<form method="GET" class="well">
    <div class="row">
        <div class="col-md-3">
            <label>Enseignant</label>
            <select name="id_personnel" class="form-control">
                <option value="">Tous</option>
                @foreach($personnels as $p)
                    <option value="{{$p->id}}" @if($idPersonnel==$p->id) selected @endif>{{$p->nom}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label>Import</label>
            <select name="id_biometrie_import" class="form-control">
                <option value="">Tous</option>
                @foreach($imports as $import)
                    <option value="{{$import->id}}" @if($idImport==$import->id) selected @endif>
                        #{{$import->id}} - {{$import->libelle}} ({{$import->date_debut}} au {{$import->date_fin}})
                    </option>
                @endforeach
            </select>
        </div>
        @if($cycleSelectionAvailable)
            <div class="col-md-2">
                <label>Cycle</label>
                <select name="id_cycle" id="id_cycle" class="form-control">
                    <option value="">Tous</option>
                    @foreach($cycles as $cycle)
                        <option value="{{$cycle->id}}" @if($idCycle==$cycle->id) selected @endif>{{$cycle->nom}}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="col-md-2">
            <label>Niveau</label>
            <select name="id_nniveau" id="id_nniveau" class="form-control">
                <option value="">Tous</option>
                @foreach($nniveaux as $nn)
                    <option value="{{$nn->id}}" data-cycle="{{$nn->id_cycle}}" @if($idNniveau==$nn->id) selected @endif>{{$nn->nom}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label>Filiere</label>
            <select name="id_niveau" id="id_niveau" class="form-control">
                <option value="">Toutes</option>
                @foreach($niveaux as $niveau)
                    <option value="{{$niveau->id}}" data-cycle="{{isset($niveau->id_cycle)?$niveau->id_cycle:''}}" @if($idNiveau==$niveau->id) selected @endif>{{$niveau->nom}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label>Specialite</label>
            <select name="id_classe" id="id_classe" class="form-control">
                <option value="">Toutes</option>
                @foreach($classes as $c)
                    <option value="{{$c->id}}" data-niveau="{{$c->id_niveau}}" data-cycle="{{isset($c->id_cycle)?$c->id_cycle:($c->niveau&&isset($c->niveau->id_cycle)?$c->niveau->id_cycle:'')}}" @if($idClasse==$c->id) selected @endif>{{$c->nom}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label>Semestre</label>
            <select name="semestre" class="form-control">
                <option value="">Tous</option>
                @foreach($semestres as $s)
                    <option value="{{$s->id}}" @if($semestre==$s->id) selected @endif>{{$s->nom}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label>Date debut</label>
            <input type="date" name="date_debut" value="{{$dateDebut}}" class="form-control">
        </div>
        <div class="col-md-2">
            <label>Date fin</label>
            <input type="date" name="date_fin" value="{{$dateFin}}" class="form-control">
        </div>
        <div class="col-md-2">
            <label>&nbsp;</label>
            <button class="btn btn-primary form-control">Filtrer</button>
        </div>
    </div>
</form>

<div class="row">
    <div class="col-md-7">
        <div class="panel panel-default">
            <div class="panel-heading"><strong>Imports recents</strong></div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Libelle</th>
                        <th>Periode</th>
                        <th>Statut</th>
                        <th>Lignes</th>
                        <th>Consolidees</th>
                        <th>Non associees</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($imports as $import)
                        <tr>
                            <td>{{$import->id}}</td>
                            <td>{{$import->libelle}}</td>
                            <td>{{$import->date_debut}}<br>{{$import->date_fin}}</td>
                            <td>{{$import->statut}}</td>
                            <td>{{$import->total_lignes}}</td>
                            <td>{{$import->total_consolidees}}</td>
                            <td>{{$import->total_non_associees}}</td>
                            <td>
                                @if($import->type_import!='manuel')
                                    <form method="POST" action="{{route('biometrie_heures_consolider',$import->id)}}" style="display:inline">
                                        <input type="hidden" name="_token" value="{{csrf_token()}}">
                                        <button class="btn btn-xs btn-success">Consolider</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{route('biometrie_heures_clear',$import->id)}}" style="display:inline">
                                    <input type="hidden" name="_token" value="{{csrf_token()}}">
                                    <button class="btn btn-xs btn-warning">Vider</button>
                                </form>
                                <form method="POST" action="{{route('biometrie_heures_destroy',$import->id)}}" style="display:inline" onsubmit="return confirm('Supprimer cet import ?')">
                                    <input type="hidden" name="_token" value="{{csrf_token()}}">
                                    <button class="btn btn-xs btn-danger">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center">Aucun import.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="panel panel-default">
            <div class="panel-heading"><strong>Associations biometrie</strong></div>
            <div class="panel-body">
                <form method="POST" action="{{route('biometrie_heures_mapping')}}">
                    <input type="hidden" name="_token" value="{{csrf_token()}}">
                    <div class="row">
                        <div class="col-md-5">
                            <label>Identite biometrie</label>
                            <select name="nom_biometrie" class="form-control">
                                @foreach($biometrieIdentites as $identite)
                                    <option value="{{$identite->nom_biometrie}}" data-numero="{{$identite->numero_biometrie}}">
                                        {{$identite->nom_biometrie}} @if($identite->numero_biometrie) ({{$identite->numero_biometrie}}) @endif
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="numero_biometrie" id="numero-biometrie">
                        </div>
                        <div class="col-md-5">
                            <label>Personnel</label>
                            <select name="id_personnel" class="form-control" required>
                                <option value="">-- Choisir --</option>
                                @foreach($personnels as $p)
                                    <option value="{{$p->id}}">{{$p->nom}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label>
                            <button class="btn btn-primary form-control">Lier</button>
                        </div>
                    </div>
                </form>
                @if(count($unmatchedPointages)>0)
                    <hr>
                    <strong>Pointages non associes</strong>
                    <ul>
                        @foreach($unmatchedPointages as $item)
                            <li>{{$item->nom_biometrie}} @if($item->numero_biometrie) ({{$item->numero_biometrie}}) @endif</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading"><strong>Totaux par enseignant</strong></div>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
            <tr>
                <th>Enseignant</th>
                <th>Heures prevues</th>
                <th>Heures realisees</th>
                <th>Non comptabilisees</th>
                <th>Montant</th>
            </tr>
            </thead>
            <tbody>
            @forelse($totaux as $row)
                <tr>
                    <td>{{$row['enseignant']}}</td>
                    <td>{{number_format($row['heures_prevues'],2,',',' ')}}</td>
                    <td>{{number_format($row['heures_realisees'],2,',',' ')}}</td>
                    <td>{{number_format($row['heures_non_comptabilisees'],2,',',' ')}}</td>
                    <td>{{number_format($row['montant_total'],0,',',' ')}}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">Aucune heure consolidee.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading"><strong>Detail des heures realisees</strong></div>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
            <tr>
                <th>Date</th>
                <th>Enseignant</th>
                <th>Parcours</th>
                <th>Matiere</th>
                <th>Plage prevue</th>
                <th>Heures realisees</th>
                <th>Non comptabilisees</th>
                <th>Taux</th>
                <th>Montant</th>
                <th>Observation</th>
            </tr>
            </thead>
            <tbody>
            @forelse($heures as $h)
                <tr>
                    <td>{{$h->date_seance}}</td>
                    <td>{{$h->personnel?$h->personnel->nom:'-'}}</td>
                    <td>{{$h->cycle?$h->cycle->nom:'-'}} / {{$h->filiere?$h->filiere->nom:'-'}} / {{$h->nniveau?$h->nniveau->nom:'-'}} / {{$h->classe?$h->classe->nom:'-'}}<br>Semestre {{$h->semestre}}</td>
                    <td>{{$h->matiere?$h->matiere->nom:'-'}}</td>
                    <td>{{substr($h->heure_debut_prevue,0,5)}} - {{substr($h->heure_fin_prevue,0,5)}}<br>{{number_format($h->duree_prevue,2,',',' ')}} h</td>
                    <td>
                        @if($h->heure_debut_reelle && $h->heure_fin_reelle)
                            {{substr($h->heure_debut_reelle,0,5)}} - {{substr($h->heure_fin_reelle,0,5)}}<br>
                        @endif
                        {{number_format($h->duree_realisee,2,',',' ')}} h
                    </td>
                    <td>{{number_format(max($h->duree_prevue-$h->duree_realisee,0),2,',',' ')}} h</td>
                    <td>{{number_format($h->montant_taux,0,',',' ')}}</td>
                    <td>{{number_format($h->montant_total,0,',',' ')}}</td>
                    <td>{{$h->observation}}</td>
                </tr>
            @empty
                <tr><td colspan="10" class="text-center">Aucune heure realisee.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    (function(){
        var select=document.querySelector('select[name="nom_biometrie"]');
        var input=document.getElementById('numero-biometrie');
        var cycle=document.getElementById('id_cycle'), niveau=document.getElementById('id_niveau'), nniveau=document.getElementById('id_nniveau'), classe=document.getElementById('id_classe');
        function syncNumero(){
            if(!select || !input) return;
            var option=select.options[select.selectedIndex];
            input.value=option ? option.getAttribute('data-numero') : '';
        }
        if(select){
            select.onchange=syncNumero;
            syncNumero();
        }
        function filterOptions(control, matcher){
            if(!control) return;
            Array.prototype.forEach.call(control.options,function(option){
                if(option.value===''){ option.style.display=''; return; }
                option.style.display=matcher(option)?'':'none';
                if(option.selected && option.style.display==='none') control.value='';
            });
        }
        function applyFilters(){
            var cycleValue=cycle?cycle.value:'', niveauValue=niveau?niveau.value:'';
            filterOptions(nniveau,function(option){ return !cycleValue || option.getAttribute('data-cycle')===cycleValue; });
            filterOptions(niveau,function(option){ return !cycleValue || option.getAttribute('data-cycle')===cycleValue || option.getAttribute('data-cycle')===''; });
            filterOptions(classe,function(option){
                var byCycle=!cycleValue || option.getAttribute('data-cycle')===cycleValue || option.getAttribute('data-cycle')==='';
                var byNiveau=!niveauValue || option.getAttribute('data-niveau')===niveauValue;
                return byCycle && byNiveau;
            });
        }
        if(cycle) cycle.onchange=applyFilters;
        if(niveau) niveau.onchange=applyFilters;
        if(nniveau) nniveau.onchange=applyFilters;
        applyFilters();
    })();
</script>
@endsection
@section('breadcrumb')
    <li><a href="{{url('emploi-du-temps')}}"><strong>Gestion des ressources humaines</strong></a></li>
    <li class="active"><strong>{{$titre}}</strong></li>
@endsection
