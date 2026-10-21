@extends('skeleton')
@section('sub-content')
<?php $selectedImport=\Illuminate\Support\Facades\Input::get('id_biometrie_import'); ?>

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

<div class="text-right" style="margin-bottom:12px">
    <a href="{{route('decompte_heures')}}" class="btn btn-default">Retour au decompte</a>
</div>

<div class="panel panel-primary">
    <div class="panel-heading"><strong>Saisie manuelle des heures realisees</strong></div>
    <div class="panel-body">
        <form method="POST" action="{{route('biometrie_heures_manual_store')}}">
            <input type="hidden" name="_token" value="{{csrf_token()}}">
            <div class="row">
                <div class="col-md-4">
                    <label>Import existant</label>
                    <select name="id_biometrie_import" class="form-control">
                        <option value="">Creer un nouvel import manuel</option>
                        @foreach($imports as $import)
                            <option value="{{$import->id}}" @if($selectedImport==$import->id) selected @endif>
                                #{{$import->id}} - {{$import->libelle}} ({{$import->date_debut}} au {{$import->date_fin}})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Libelle nouvel import</label>
                    <input type="text" name="libelle" class="form-control" value="{{old('libelle')}}">
                </div>
                <div class="col-md-2">
                    <label>Date debut import</label>
                    <input type="date" name="date_debut_import" class="form-control" value="{{old('date_debut_import')}}">
                </div>
                <div class="col-md-2">
                    <label>Date fin import</label>
                    <input type="date" name="date_fin_import" class="form-control" value="{{old('date_fin_import')}}">
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-5">
                    <label>Cours enseignant</label>
                    <select name="id_cours_enseignant" id="cours-select" class="form-control" required>
                        <option value="">-- Choisir --</option>
                        @foreach($cours as $c)
                            <option value="{{$c->id}}" @if(old('id_cours_enseignant')==$c->id) selected @endif>
                                {{$c->personnel?$c->personnel->nom:'-'}} / {{$c->matiere?$c->matiere->nom:'-'}} / {{$c->classe?$c->classe->nom:'-'}} / {{$c->date_debut}} - {{$c->date_fin}}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Seance</label>
                    <select name="id_seance_cours" id="seance-select" class="form-control" required>
                        <option value="">-- Choisir un cours --</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Date seance</label>
                    <input type="date" name="date_seance" class="form-control" value="{{old('date_seance')}}" required>
                </div>
                <div class="col-md-1">
                    <label>Debut</label>
                    <input type="time" name="heure_debut_reelle" id="debut-reel" class="form-control" value="{{old('heure_debut_reelle')}}" required>
                </div>
                <div class="col-md-1">
                    <label>Fin</label>
                    <input type="time" name="heure_fin_reelle" id="fin-reel" class="form-control" value="{{old('heure_fin_reelle')}}" required>
                </div>
            </div>
            <div class="row" style="margin-top:12px">
                <div class="col-md-10">
                    <label>Observation</label>
                    <input type="text" name="observation" class="form-control" value="{{old('observation')}}">
                </div>
                <div class="col-md-2">
                    <label>&nbsp;</label>
                    <button class="btn btn-success form-control">Enregistrer</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading"><strong>Heures saisies manuellement</strong></div>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
            <tr>
                <th>Import</th>
                <th>Date</th>
                <th>Enseignant</th>
                <th>Parcours</th>
                <th>UE / Matiere</th>
                <th>Plage prevue</th>
                <th>Realise</th>
                <th>Montant</th>
                <th>Observation</th>
            </tr>
            </thead>
            <tbody>
            @forelse($heuresManuelles as $h)
                <tr>
                    <td>{{$h->import?$h->import->libelle:'-'}}</td>
                    <td>{{$h->date_seance}}</td>
                    <td>{{$h->personnel?$h->personnel->nom:'-'}}</td>
                    <td>{{$h->cycle?$h->cycle->nom:'-'}} / {{$h->filiere?$h->filiere->nom:'-'}} / {{$h->nniveau?$h->nniveau->nom:'-'}} / {{$h->classe?$h->classe->nom:'-'}}<br>Semestre {{$h->semestre}}</td>
                    <td>{{$h->matiere?$h->matiere->nom:'-'}}</td>
                    <td>{{substr($h->heure_debut_prevue,0,5)}} - {{substr($h->heure_fin_prevue,0,5)}} / {{number_format($h->duree_prevue,2,',',' ')}} h</td>
                    <td>{{substr($h->heure_debut_reelle,0,5)}} - {{substr($h->heure_fin_reelle,0,5)}} / {{number_format($h->duree_realisee,2,',',' ')}} h</td>
                    <td>{{number_format($h->montant_total,0,',',' ')}}</td>
                    <td>{{$h->observation}}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center">Aucune saisie manuelle.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    (function(){
        var coursData={!! $coursDataJson !!};
        var coursSelect=document.getElementById('cours-select');
        var seanceSelect=document.getElementById('seance-select');
        var oldSeance='{{old('id_seance_cours')}}';

        function fillSeances(){
            var coursId=coursSelect.value;
            seanceSelect.innerHTML='<option value="">-- Choisir --</option>';
            if(!coursData[coursId]) return;
            for(var i=0;i<coursData[coursId].seances.length;i++){
                var s=coursData[coursId].seances[i];
                var opt=document.createElement('option');
                opt.value=s.id;
                opt.innerHTML=s.jour_label+' - '+s.libelle;
                opt.setAttribute('data-debut',s.debut);
                opt.setAttribute('data-fin',s.fin);
                if(oldSeance && oldSeance==s.id) opt.selected=true;
                seanceSelect.appendChild(opt);
            }
            fillTimes();
        }

        function fillTimes(){
            var opt=seanceSelect.options[seanceSelect.selectedIndex];
            if(!opt) return;
            if(document.getElementById('debut-reel').value==='') document.getElementById('debut-reel').value=opt.getAttribute('data-debut');
            if(document.getElementById('fin-reel').value==='') document.getElementById('fin-reel').value=opt.getAttribute('data-fin');
        }

        if(coursSelect){
            coursSelect.onchange=fillSeances;
            seanceSelect.onchange=fillTimes;
            fillSeances();
        }
    })();
</script>
@endsection
@section('breadcrumb')
    <li><a href="{{url('emploi-du-temps')}}"><strong>Gestion des ressources humaines</strong></a></li>
    <li><a href="{{route('decompte_heures')}}"><strong>Decompte des heures</strong></a></li>
    <li class="active"><strong>{{$titre}}</strong></li>
@endsection
