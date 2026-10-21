@extends('skeleton')
@section('sub-content')
<?php $idPersonnel=\Illuminate\Support\Facades\Input::get('id_personnel'); $idCycle=\Illuminate\Support\Facades\Input::get('id_cycle'); $idNiveau=\Illuminate\Support\Facades\Input::get('id_niveau'); $idNniveau=\Illuminate\Support\Facades\Input::get('id_nniveau'); $idClasse=\Illuminate\Support\Facades\Input::get('id_classe'); $semestre=\Illuminate\Support\Facades\Input::get('semestre'); $dateDebut=\Illuminate\Support\Facades\Input::get('date_debut'); $dateFin=\Illuminate\Support\Facades\Input::get('date_fin'); ?>
@if(session('success')||isset($success))<div class="alert alert-success">{{trans('admin.succes')}}</div>@endif
<a href="{{route('cours_enseignants_create')}}" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span> Programmer un cours</a>
<form method="GET" class="well" style="margin-top:15px">
    <div class="row">
        <div class="col-md-2"><label>Enseignant</label><select name="id_personnel" class="form-control"><option value="">Tous</option>@foreach($personnels as $p)<option value="{{$p->id}}" @if($idPersonnel==$p->id) selected @endif>{{$p->nom}}</option>@endforeach</select></div>
        @if($cycleSelectionAvailable)<div class="col-md-2"><label>Cycle</label><select name="id_cycle" id="id_cycle" class="form-control"><option value="">Tous</option>@foreach($cycles as $cycle)<option value="{{$cycle->id}}" @if($idCycle==$cycle->id) selected @endif>{{$cycle->nom}}</option>@endforeach</select></div>@endif
        <div class="col-md-2"><label>Niveau</label><select name="id_nniveau" id="id_nniveau" class="form-control"><option value="">Tous</option>@foreach($nniveaux as $nn)<option value="{{$nn->id}}" data-cycle="{{$nn->id_cycle}}" @if($idNniveau==$nn->id) selected @endif>{{$nn->nom}}</option>@endforeach</select></div>
        <div class="col-md-2"><label>Filiere</label><select name="id_niveau" id="id_niveau" class="form-control"><option value="">Toutes</option>@foreach($niveaux as $niveau)<option value="{{$niveau->id}}" data-cycle="{{isset($niveau->id_cycle)?$niveau->id_cycle:''}}" @if($idNiveau==$niveau->id) selected @endif>{{$niveau->nom}}</option>@endforeach</select></div>
        <div class="{{$cycleSelectionAvailable?'col-md-2':'col-md-4'}}"><label>Specialite</label><select name="id_classe" id="id_classe" class="form-control"><option value="">Toutes</option>@foreach($classes as $c)<option value="{{$c->id}}" data-niveau="{{$c->id_niveau}}" data-cycle="{{isset($c->id_cycle)?$c->id_cycle:($c->niveau&&isset($c->niveau->id_cycle)?$c->niveau->id_cycle:'')}}" @if($idClasse==$c->id) selected @endif>{{$c->nom}}</option>@endforeach</select></div>
        <div class="col-md-2"><label>Semestre</label><select name="semestre" class="form-control"><option value="">Tous</option>@foreach($semestres as $s)<option value="{{$s->id}}" @if($semestre==$s->id) selected @endif>{{$s->nom}}</option>@endforeach</select></div>
        <div class="col-md-2"><label>Date debut</label><input type="date" name="date_debut" value="{{$dateDebut}}" class="form-control"></div>
        <div class="col-md-2"><label>Date fin</label><input type="date" name="date_fin" value="{{$dateFin}}" class="form-control"></div>
        <div class="col-md-12"><label>&nbsp;</label><button class="btn btn-primary">Filtrer</button></div>
    </div>
</form>
<table class="table table-bordered table-striped">
    <thead><tr><th>Enseignant</th><th>UE / Matiere</th><th>Parcours</th><th>Semestre</th><th>Periode</th><th>Seances</th><th>Volume</th><th>Statut</th><th></th></tr></thead>
    <tbody>
    @forelse($cours as $c)
        <tr>
            <td>{{$c->personnel?$c->personnel->nom:'-'}}</td>
            <td>{{$c->matiere?$c->matiere->nom:'-'}}</td>
            <td>{{$c->cycle?$c->cycle->nom:'-'}} / {{$c->filiere?$c->filiere->nom:'-'}} / {{$c->nniveau?$c->nniveau->nom:'-'}} / {{$c->classe?$c->classe->nom:'-'}}</td>
            <td>Semestre {{$c->semestre}}</td>
            <td>{{$c->date_debut}} - {{$c->date_fin}}</td>
            <td>@foreach($c->seances as $s)<span class="label label-info">{{$jours[$s->jour_semaine]}} {{substr($s->plage?$s->plage->heure_debut:'',0,5)}}-{{substr($s->plage?$s->plage->heure_fin:'',0,5)}}</span> @endforeach</td>
            <td>{{$c->volume_horaire_prevu}} h</td>
            <td>{{$c->statut}}</td>
            <td><a href="{{route('cours_enseignants_edit',$c->id)}}" class="btn btn-primary btn-xs">Modifier</a> <a href="{{route('cours_enseignants_destroy',$c->id)}}" class="btn btn-danger btn-xs">Supprimer</a></td>
        </tr>
    @empty
        <tr><td colspan="9" class="text-center">Aucun cours programme.</td></tr>
    @endforelse
    </tbody>
</table>
<script>
(function(){
    var cycle=document.getElementById('id_cycle'), niveau=document.getElementById('id_niveau'), nniveau=document.getElementById('id_nniveau'), classe=document.getElementById('id_classe');
    function filterOptions(select, matcher){
        Array.prototype.forEach.call(select.options,function(option){
            if(option.value===''){ option.style.display=''; return; }
            option.style.display=matcher(option)?'':'none';
            if(option.selected && option.style.display==='') return;
            if(option.selected) select.value='';
        });
    }
    function applyFilters(){
        var cycleValue=cycle?cycle.value:'', niveauValue=niveau.value;
        if(cycle) filterOptions(nniveau,function(option){ return !cycleValue || option.getAttribute('data-cycle')===cycleValue; });
        if(cycle) filterOptions(niveau,function(option){ return !cycleValue || option.getAttribute('data-cycle')===cycleValue || option.getAttribute('data-cycle')===''; });
        filterOptions(classe,function(option){
            var byCycle=!cycleValue || option.getAttribute('data-cycle')===cycleValue || option.getAttribute('data-cycle')==='';
            var byNiveau=!niveauValue || option.getAttribute('data-niveau')===niveauValue;
            return byCycle && byNiveau;
        });
    }
    if(cycle) cycle.onchange=applyFilters; niveau.onchange=applyFilters; nniveau.onchange=applyFilters; applyFilters();
})();
</script>
@endsection
@section('breadcrumb')<li><a href="{{url('emploi-du-temps')}}"><strong>Gestion des ressources humaines</strong></a></li><li class="active"><strong>{{$titre}}</strong></li>@endsection
