@extends('skeleton')
@section('sub-content')
@if(session('success')||isset($success))<div class="alert alert-success">{{trans('admin.succes')}}</div>@endif
<form method="POST" action="{{route('plages_horaires_store')}}" class="well">
    <input type="hidden" name="_token" value="{{csrf_token()}}">
    <div class="row">
        <div class="col-md-3"><label>Libelle</label><input name="libelle" class="form-control" required></div>
        <div class="col-md-2"><label>Debut</label><input type="time" name="heure_debut" class="form-control" required></div>
        <div class="col-md-2"><label>Fin</label><input type="time" name="heure_fin" class="form-control" required></div>
        <div class="col-md-2"><label>Type</label><select name="type_plage" class="form-control"><option value="cours">Cours</option><option value="pause">Pause</option></select></div>
        <div class="col-md-2"><label>Personnel</label><select name="type_personnel" class="form-control"><option value="tous">Tous</option><option value="enseignant">Enseignant</option><option value="permanent">Permanent</option><option value="vacataire">Vacataire</option></select></div>
        <div class="col-md-2"><label>Periode</label><select name="periode_journee" class="form-control"><option value="jour">Jour</option><option value="soir">Soir</option></select></div>
        <div class="col-md-2"><label>Format</label><select name="format_plage" class="form-control"><option value="mixte">Mixte</option><option value="bloc_4h">Bloc 4h</option><option value="bloc_8h">Bloc 8h</option><option value="bloc_6h">Bloc 6h</option><option value="bloc_5h">Bloc 5h</option><option value="pause">Pause</option></select></div>
        <div class="col-md-1"><label>Duree</label><input type="number" step="0.01" name="duree_payable" class="form-control"></div>
        <div class="col-md-1"><label>Ordre</label><input type="number" name="ordre" value="0" class="form-control"></div>
        <div class="col-md-2"><label>Statut</label><select name="statut" class="form-control"><option value="actif">Actif</option><option value="inactif">Inactif</option></select></div>
        <div class="col-md-2"><label>&nbsp;</label><button class="btn btn-success form-control">Ajouter</button></div>
    </div>
</form>
<table class="table table-bordered table-striped table-condensed">
    <thead><tr><th>Libelle</th><th>Debut</th><th>Fin</th><th>Duree</th><th>Type</th><th>Personnel</th><th>Periode</th><th>Format</th><th>Ordre</th><th>Statut</th><th></th></tr></thead>
    <tbody>@foreach($plages as $p)<tr><form method="POST" action="{{route('plages_horaires_update',$p->id)}}"><input type="hidden" name="_token" value="{{csrf_token()}}"><td><input name="libelle" value="{{$p->libelle}}" class="form-control"></td><td><input type="time" name="heure_debut" value="{{substr($p->heure_debut,0,5)}}" class="form-control"></td><td><input type="time" name="heure_fin" value="{{substr($p->heure_fin,0,5)}}" class="form-control"></td><td><input type="number" step="0.01" name="duree_payable" value="{{$p->duree_payable}}" class="form-control"></td><td><select name="type_plage" class="form-control"><option value="cours" @if($p->type_plage=='cours') selected @endif>Cours</option><option value="pause" @if($p->type_plage=='pause') selected @endif>Pause</option></select></td><td><input name="type_personnel" value="{{$p->type_personnel}}" class="form-control"></td><td><input name="periode_journee" value="{{$p->periode_journee}}" class="form-control"></td><td><input name="format_plage" value="{{$p->format_plage}}" class="form-control"></td><td><input type="number" name="ordre" value="{{$p->ordre}}" class="form-control"></td><td><input name="statut" value="{{$p->statut}}" class="form-control"></td><td><button class="btn btn-primary btn-xs">Modifier</button> <a href="{{route('plages_horaires_destroy',$p->id)}}" class="btn btn-danger btn-xs">Supprimer</a></td></form></tr>@endforeach</tbody>
</table>
@endsection
@section('breadcrumb')<li><a href="{{url('emploi-du-temps')}}"><strong>Gestion des ressources humaines</strong></a></li><li class="active"><strong>{{$titre}}</strong></li>@endsection
