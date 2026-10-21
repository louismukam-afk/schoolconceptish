@extends('skeleton')
@section('sub-content')
@if(session('success')||isset($success))<div class="alert alert-success">{{trans('admin.succes')}}</div>@endif
<form method="POST" action="{{route('emplois_permanents_store')}}" class="well">
    <input type="hidden" name="_token" value="{{csrf_token()}}">
    <div class="row">
        <div class="col-md-3"><label>Personnel permanent</label><select name="id_personnel" class="form-control" required><option value="">--</option>@foreach($personnels as $p)<option value="{{$p->id}}">{{$p->nom}}</option>@endforeach</select></div>
        <div class="col-md-3"><label>Plage horaire</label><select name="id_plage_horaire" class="form-control" required><option value="">--</option>@foreach($plages as $p)<option value="{{$p->id}}">{{$p->libelle}} ({{substr($p->heure_debut,0,5)}} - {{substr($p->heure_fin,0,5)}})</option>@endforeach</select></div>
        <div class="col-md-2"><label>Date debut</label><input type="date" name="date_debut" class="form-control" required></div>
        <div class="col-md-2"><label>Date fin</label><input type="date" name="date_fin" class="form-control"></div>
        <div class="col-md-2"><label>Statut</label><select name="statut" class="form-control"><option value="actif">Actif</option><option value="inactif">Inactif</option></select></div>
        <div class="col-md-8"><label>Jours</label><br>@foreach($jours as $k=>$j)<label style="margin-right:12px"><input type="checkbox" name="jours[]" value="{{$k}}"> {{$j}}</label>@endforeach</div>
        <div class="col-md-2"><label>&nbsp;</label><button class="btn btn-success form-control">Affecter</button></div>
        <div class="col-md-12"><label>Observations</label><input name="observations" class="form-control"></div>
    </div>
</form>
<table class="table table-bordered table-striped">
    <thead><tr><th>Personnel</th><th>Jour</th><th>Plage</th><th>Periode</th><th>Statut</th><th>Observations</th><th></th></tr></thead>
    <tbody>@forelse($emplois as $e)<tr><td>{{$e->personnel?$e->personnel->nom:'-'}}</td><td>{{$jours[$e->jour_semaine]}}</td><td>{{$e->plage?$e->plage->libelle:''}} {{substr($e->plage?$e->plage->heure_debut:'',0,5)}}-{{substr($e->plage?$e->plage->heure_fin:'',0,5)}}</td><td>{{$e->date_debut}} - {{$e->date_fin}}</td><td>{{$e->statut}}</td><td>{{$e->observations}}</td><td><a href="{{route('emplois_permanents_destroy',$e->id)}}" class="btn btn-danger btn-xs">Supprimer</a></td></tr>@empty<tr><td colspan="7" class="text-center">Aucun emploi permanent.</td></tr>@endforelse</tbody>
</table>
@endsection
@section('breadcrumb')<li><a href="{{url('emploi-du-temps')}}"><strong>Gestion des ressources humaines</strong></a></li><li class="active"><strong>{{$titre}}</strong></li>@endsection
