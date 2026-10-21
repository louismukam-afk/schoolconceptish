@extends('skeleton')
@section('sub-content')
<?php $idCycle=\Illuminate\Support\Facades\Input::get('id_cycle'); $idNiveau=\Illuminate\Support\Facades\Input::get('id_niveau'); $idNniveau=\Illuminate\Support\Facades\Input::get('id_nniveau'); $idClasse=\Illuminate\Support\Facades\Input::get('id_classe'); ?>
<form method="GET" class="well">
    <div class="row">
        <div class="col-md-2"><label>Enseignant</label><select name="id_personnel" class="form-control"><option value="">Tous</option>@foreach($personnels as $p)<option value="{{$p->id}}" @if($selectedPersonnel==$p->id) selected @endif>{{$p->nom}}</option>@endforeach</select></div>
        @if($cycleSelectionAvailable)<div class="col-md-2"><label>Cycle</label><select name="id_cycle" id="id_cycle" class="form-control"><option value="">Tous</option>@foreach($cycles as $cycle)<option value="{{$cycle->id}}" @if($idCycle==$cycle->id) selected @endif>{{$cycle->nom}}</option>@endforeach</select></div>@endif
        <div class="col-md-2"><label>Niveau</label><select name="id_nniveau" id="id_nniveau" class="form-control"><option value="">Tous</option>@foreach($nniveaux as $nn)<option value="{{$nn->id}}" data-cycle="{{$nn->id_cycle}}" @if($idNniveau==$nn->id) selected @endif>{{$nn->nom}}</option>@endforeach</select></div>
        <div class="col-md-2"><label>Filiere</label><select name="id_niveau" id="id_niveau" class="form-control"><option value="">Toutes</option>@foreach($niveaux as $niveau)<option value="{{$niveau->id}}" data-cycle="{{isset($niveau->id_cycle)?$niveau->id_cycle:''}}" @if($idNiveau==$niveau->id) selected @endif>{{$niveau->nom}}</option>@endforeach</select></div>
        <div class="col-md-2"><label>Specialite</label><select name="id_classe" id="id_classe" class="form-control"><option value="">Toutes</option>@foreach($classes as $c)<option value="{{$c->id}}" data-niveau="{{$c->id_niveau}}" data-cycle="{{isset($c->id_cycle)?$c->id_cycle:($c->niveau&&isset($c->niveau->id_cycle)?$c->niveau->id_cycle:'')}}" @if($idClasse==$c->id) selected @endif>{{$c->nom}}</option>@endforeach</select></div>
        <div class="col-md-2"><label>Semestre</label><select name="semestre" class="form-control"><option value="">Tous</option>@foreach($semestres as $s)<option value="{{$s->id}}" @if($selectedSemestre==$s->id) selected @endif>{{$s->nom}}</option>@endforeach</select></div>
        <div class="col-md-2"><label>Date debut</label><input type="date" name="date_debut" value="{{$selectedDateDebut}}" class="form-control"></div>
        <div class="col-md-2"><label>Date fin</label><input type="date" name="date_fin" value="{{$selectedDateFin}}" class="form-control"></div>
        <div class="col-md-2"><label>&nbsp;</label><button class="btn btn-primary form-control">Filtrer</button></div>
        <div class="col-md-3"><label>&nbsp;</label>@if($selectedPersonnel)<a href="{{route('definir_emploi_enseignant',$selectedPersonnel)}}" class="btn btn-success form-control">Definir les horaires</a>@endif</div>
    </div>
</form>
<button onclick="window.print()" class="btn btn-warning"><span class="glyphicon glyphicon-print"></span> Imprimer</button>
<table class="table table-bordered table-striped table-condensed" style="margin-top:20px;background:#fff">
    <thead><tr><th>Jour</th>@foreach($plages as $p)<th class="text-center">{{substr($p->heure_debut,0,5)}} - {{substr($p->heure_fin,0,5)}}</th>@endforeach</tr></thead>
    <tbody>
    @foreach($jours as $jourIndex=>$jour)
        <tr><th>{{$jour}}</th>
        @foreach($plages as $p)
            <td @if($p->type_plage=='pause') style="background:#eee;text-align:center" @endif>
                @if($p->type_plage=='pause')<strong>Pause</strong>@endif
                @foreach(isset($rows[$jourIndex][$p->id])?$rows[$jourIndex][$p->id]:[] as $item)
                    <?php $c=$item['cours']; ?>
                    <div style="border-left:4px solid #337ab7;background:#f9fbfd;margin-bottom:6px;padding:7px">
                        <strong>{{$c->matiere?$c->matiere->nom:'-'}}</strong>
                        <small>{{$c->cycle?$c->cycle->nom:'-'}} / {{$c->filiere?$c->filiere->nom:'-'}} / {{$c->nniveau?$c->nniveau->nom:'-'}} / {{$c->classe?$c->classe->nom:'-'}}</small><br>
                        @if(!$selectedPersonnel)<small>{{$c->personnel?$c->personnel->nom:'-'}}</small><br>@endif
                        <small>Semestre {{$c->semestre}}</small><br>
                        <small>{{$c->date_debut}} - {{$c->date_fin}}</small><br>
                        <small>{{$item['seance']->duree_heures}} h / total {{$c->volume_horaire_prevu}} h</small>
                    </div>
                @endforeach
            </td>
        @endforeach
        </tr>
    @endforeach
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
