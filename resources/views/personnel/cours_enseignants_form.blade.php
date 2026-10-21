@extends('skeleton')
@section('sub-content')
<?php $selectedCycle=old('id_cycle',isset($selectedCycle)?$selectedCycle:0); $selectedNiveau=old('id_niveau',isset($selectedNiveau)?$selectedNiveau:0); $selectedNniveau=old('id_nniveau',isset($selectedNniveau)?$selectedNniveau:0); $selectedSemestre=old('semestre',isset($selectedSemestre)?$selectedSemestre:1); ?>
<form method="POST" action="{{$cours?route('cours_enseignants_update',$cours->id):route('cours_enseignants_store')}}" class="well">
    <input type="hidden" name="_token" value="{{csrf_token()}}">
    <div class="row">
        <div class="col-md-4"><label>Enseignant</label><select name="id_personnel" class="form-control" required><option value="">--</option>@foreach($personnels as $p)<option value="{{$p->id}}" @if(old('id_personnel',$selectedPersonnel)==$p->id) selected @endif>{{$p->nom}} ({{$p->type_personnel}})</option>@endforeach</select></div>
        @if($cycleSelectionAvailable)<div class="col-md-2"><label>Cycle</label><select name="id_cycle" id="id_cycle" class="form-control" required><option value="">--</option>@foreach($cycles as $cycle)<option value="{{$cycle->id}}" @if($selectedCycle==$cycle->id) selected @endif>{{$cycle->nom}}</option>@endforeach</select></div>@endif
        <div class="col-md-2"><label>Niveau</label><select name="id_nniveau" id="id_nniveau" class="form-control" required><option value="">--</option>@foreach($nniveaux as $nn)<option value="{{$nn->id}}" data-cycle="{{$nn->id_cycle}}" @if($selectedNniveau==$nn->id) selected @endif>{{$nn->nom}}</option>@endforeach</select></div>
        <div class="col-md-2"><label>Filiere</label><select name="id_niveau" id="id_niveau" class="form-control" required><option value="">--</option>@foreach($niveaux as $niveau)<option value="{{$niveau->id}}" data-cycle="{{isset($niveau->id_cycle)?$niveau->id_cycle:''}}" @if($selectedNiveau==$niveau->id) selected @endif>{{$niveau->nom}}</option>@endforeach</select></div>
        <div class="{{$cycleSelectionAvailable?'col-md-2':'col-md-4'}}"><label>Specialite</label><select name="id_classe" id="id_classe" class="form-control" required><option value="">--</option>@foreach($classes as $c)<option value="{{$c->id}}" data-niveau="{{$c->id_niveau}}" data-cycle="{{isset($c->id_cycle)?$c->id_cycle:($c->niveau&&isset($c->niveau->id_cycle)?$c->niveau->id_cycle:'')}}" @if(old('id_classe',$selectedClasse)==$c->id) selected @endif>{{$c->nom}}</option>@endforeach</select></div>
        <div class="col-md-2"><label>Semestre</label><select name="semestre" id="semestre" class="form-control" required>@foreach($semestres as $s)<option value="{{$s->id}}" @if($selectedSemestre==$s->id) selected @endif>{{$s->nom}}</option>@endforeach</select></div>
        <div class="col-md-4"><label>UE / Matiere</label><select name="id_matiere" id="id_matiere" class="form-control" required><option value="">--</option>@foreach($matieres as $m)<option value="{{$m->id}}" @if(old('id_matiere',$cours?$cours->id_matiere:0)==$m->id) selected @endif>{{$m->nom}}</option>@endforeach</select></div>
        <div class="col-md-2"><label>Date debut</label><input type="date" name="date_debut" value="{{old('date_debut',$cours?$cours->date_debut:'')}}" class="form-control" required></div>
        <div class="col-md-2"><label>Date fin</label><input type="date" name="date_fin" value="{{old('date_fin',$cours?$cours->date_fin:'')}}" class="form-control" required></div>
        <div class="col-md-2"><label>Volume prevu</label><input type="number" step="0.01" name="volume_horaire_prevu" value="{{old('volume_horaire_prevu',$cours?$cours->volume_horaire_prevu:0)}}" class="form-control"></div>
        <div class="col-md-2"><label>Statut</label><select name="statut" class="form-control"><option value="actif">Actif</option><option value="inactif" @if($cours&&$cours->statut=='inactif') selected @endif>Inactif</option></select></div>
    </div>
    <hr>
    <h4>Seances hebdomadaires</h4>
    <div id="seances">
        <?php $rows=old('seances')?old('seances'):($cours?$cours->seances->toArray():[['jour_semaine'=>1,'id_plage_horaire'=>'']]); ?>
        @foreach($rows as $i=>$row)
            <div class="row seance-row" style="margin-bottom:8px">
                <div class="col-md-4"><select name="seances[{{$i}}][jour_semaine]" class="form-control">@foreach($jours as $k=>$j)<option value="{{$k}}" @if((isset($row['jour_semaine'])?$row['jour_semaine']:0)==$k) selected @endif>{{$j}}</option>@endforeach</select></div>
                <div class="col-md-6"><select name="seances[{{$i}}][id_plage_horaire]" class="form-control">@foreach($plages as $p)<option value="{{$p->id}}" @if((isset($row['id_plage_horaire'])?$row['id_plage_horaire']:0)==$p->id) selected @endif>{{$p->libelle}} ({{substr($p->heure_debut,0,5)}} - {{substr($p->heure_fin,0,5)}})</option>@endforeach</select></div>
                <div class="col-md-2"><button type="button" class="btn btn-danger remove-seance">Retirer</button></div>
            </div>
        @endforeach
    </div>
    <button type="button" id="add-seance" class="btn btn-info">Ajouter une seance</button>
    <button class="btn btn-success pull-right">Enregistrer</button>
</form>
<script>
var seanceIndex=document.querySelectorAll('.seance-row').length;
document.getElementById('add-seance').onclick=function(){var first=document.querySelector('.seance-row');var clone=first.cloneNode(true);Array.prototype.forEach.call(clone.querySelectorAll('select'),function(s){s.name=s.name.replace(/seances\[\d+\]/,'seances['+seanceIndex+']');});document.getElementById('seances').appendChild(clone);seanceIndex++;};
document.addEventListener('click',function(e){if(e.target.className.indexOf('remove-seance')>=0&&document.querySelectorAll('.seance-row').length>1)e.target.parentNode.parentNode.remove();});
(function(){
    var cycle=document.getElementById('id_cycle'), niveau=document.getElementById('id_niveau'), nniveau=document.getElementById('id_nniveau'), classe=document.getElementById('id_classe'), semestre=document.getElementById('semestre'), matiere=document.getElementById('id_matiere');
    var programme={!! $programmeMatieres !!};
    function filterOptions(select, matcher){
        Array.prototype.forEach.call(select.options,function(option){
            if(option.value===''){ option.style.display=''; return; }
            option.style.display=matcher(option)?'':'none';
            if(option.selected && option.style.display==='') return;
            if(option.selected) select.value='';
        });
    }
    function visibleSelected(select){
        return select.value && select.options[select.selectedIndex] && select.options[select.selectedIndex].style.display!=='none';
    }
    function applyFilters(){
        var cycleValue=cycle?cycle.value:'';
        if(cycle) filterOptions(nniveau,function(option){ return cycleValue && option.getAttribute('data-cycle')===cycleValue; });
        if(cycle) filterOptions(niveau,function(option){ return cycleValue && (option.getAttribute('data-cycle')===cycleValue || option.getAttribute('data-cycle')===''); });
        var niveauValue=visibleSelected(niveau)?niveau.value:'';
        filterOptions(nniveau,function(option){ return !cycleValue || option.getAttribute('data-cycle')===cycleValue || option.getAttribute('data-cycle')===''; });
        filterOptions(classe,function(option){
            var byCycle=cycleValue && (option.getAttribute('data-cycle')===cycleValue || option.getAttribute('data-cycle')==='');
            var byNiveau=niveauValue && option.getAttribute('data-niveau')===niveauValue;
            return byCycle && byNiveau;
        });
        filterOptions(matiere,function(option){
            var key=(cycle?cycle.value:'')+'-'+classe.value+'-'+nniveau.value+'-'+semestre.value;
            if(!cycleValue || !nniveau.value || !niveauValue || !classe.value || !semestre.value) return false;
            if(!programme[key] || programme[key].length===0) return false;
            return programme[key].indexOf(parseInt(option.value,10))>=0;
        });
    }
    if(cycle) cycle.onchange=applyFilters; niveau.onchange=applyFilters; nniveau.onchange=applyFilters; classe.onchange=applyFilters; semestre.onchange=applyFilters; applyFilters();
})();
</script>
@endsection
@section('breadcrumb')<li><a href="{{url('emploi-du-temps')}}"><strong>Gestion des ressources humaines</strong></a></li><li><a href="{{route('cours_enseignants')}}"><strong>Cours enseignants</strong></a></li><li class="active"><strong>{{$titre}}</strong></li>@endsection
