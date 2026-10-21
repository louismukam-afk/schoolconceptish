@extends('skeleton')
@section('sub-content')
<?php $idCycle=\Illuminate\Support\Facades\Input::get('id_cycle'); $idNiveau=\Illuminate\Support\Facades\Input::get('id_niveau'); $idNniveau=\Illuminate\Support\Facades\Input::get('id_nniveau'); $idClasse=\Illuminate\Support\Facades\Input::get('id_classe'); $semestre=\Illuminate\Support\Facades\Input::get('semestre'); ?>
<form method="GET" class="well">
    <div class="row">
        @if($cycleSelectionAvailable)
            <div class="col-md-2"><label>Cycle</label><select name="id_cycle" id="id_cycle" class="form-control"><option value="">Tous</option>@foreach($cycles as $cycle)<option value="{{$cycle->id}}" @if($idCycle==$cycle->id) selected @endif>{{$cycle->nom}}</option>@endforeach</select></div>
        @endif
        <div class="col-md-2"><label>Niveau</label><select name="id_nniveau" id="id_nniveau" class="form-control"><option value="">Tous</option>@foreach($nniveaux as $nn)<option value="{{$nn->id}}" data-cycle="{{$nn->id_cycle}}" @if($idNniveau==$nn->id) selected @endif>{{$nn->nom}}</option>@endforeach</select></div>
        <div class="col-md-2"><label>Filiere</label><select name="id_niveau" id="id_niveau" class="form-control"><option value="">Toutes</option>@foreach($niveaux as $niveau)<option value="{{$niveau->id}}" data-cycle="{{isset($niveau->id_cycle)?$niveau->id_cycle:''}}" @if($idNiveau==$niveau->id) selected @endif>{{$niveau->nom}}</option>@endforeach</select></div>
        <div class="{{$cycleSelectionAvailable?'col-md-2':'col-md-4'}}"><label>Specialite</label><select name="id_classe" id="id_classe" class="form-control"><option value="">Toutes</option>@foreach($classes as $c)<option value="{{$c->id}}" data-niveau="{{$c->id_niveau}}" data-cycle="{{isset($c->id_cycle)?$c->id_cycle:($c->niveau&&isset($c->niveau->id_cycle)?$c->niveau->id_cycle:'')}}" @if($idClasse==$c->id) selected @endif>{{$c->nom}}</option>@endforeach</select></div>
        <div class="col-md-2"><label>Semestre</label><select name="semestre" class="form-control"><option value="">Tous</option>@foreach($semestres as $s)<option value="{{$s->id}}" @if($semestre==$s->id) selected @endif>{{$s->nom}}</option>@endforeach</select></div>
        <div class="col-md-2"><label>&nbsp;</label><button class="btn btn-primary form-control">Filtrer</button></div>
    </div>
</form>
<button onclick="window.print()" class="btn btn-warning"><span class="glyphicon glyphicon-print"></span> Imprimer</button>
<table class="table table-bordered table-striped" style="margin-top:15px">
    <thead><tr><th>Specialite</th><th>UE / Matiere</th><th>Heures prevues</th><th>Heures realisees</th><th>Reste</th></tr></thead>
    <tbody>@forelse($rows as $r)<tr><td>{{$r['classe']->nom}}</td><td>{{$r['matiere']->nom}}</td><td>{{number_format($r['prevu'],1,',',' ')}} h</td><td>{{number_format($r['realise'],1,',',' ')}} h</td><td>{{number_format(max($r['prevu']-$r['realise'],0),1,',',' ')}} h</td></tr>@empty<tr><td colspan="5" class="text-center">Aucun volume horaire trouve.</td></tr>@endforelse</tbody>
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
