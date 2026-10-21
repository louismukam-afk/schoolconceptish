@extends('skeleton')
@section('sub-content')
<div class="col-md-12">
    @if(isset($success))<div class="alert alert-success">{{ trans('admin.succes') }}</div>@endif
    <div class="btn-group" style="margin-bottom:10px">
        <a class="btn btn-default" href="{{ url('/admin') }}"><span class="fa fa-arrow-left"></span> Tableau de bord</a>
        <a class="btn btn-info" href="{{ url('/utilisateurs') }}"><span class="fa fa-users"></span> Utilisateurs</a>
        <a class="btn btn-primary" href="{{ url('/admin/tutelles') }}"><span class="fa fa-institution"></span> Tutelles</a>
    </div>
    <h3>{{ $titre }}</h3>
    <form method="post" action="{{ url('/admin/campus/store') }}" class="panel panel-default" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-3"><label>Nom</label><input name="nom" class="form-control" required></div>
                <div class="col-md-2"><label>Sigle</label><input name="sigle" class="form-control"></div>
                <div class="col-md-2"><label>Ville</label><input name="ville" class="form-control"></div>
                <div class="col-md-3"><label>Localisation</label><input name="localisation" class="form-control"></div>
                <div class="col-md-2"><label>Date creation</label><input type="date" name="date_creation" class="form-control"></div>
            </div>
            <div class="row" style="margin-top:10px">
                <div class="col-md-3"><label>Numero agrement</label><input name="numero_agrement" class="form-control"></div>
                <div class="col-md-3"><label>Reference</label><input name="reference" class="form-control"></div>
                <div class="col-md-2"><label>Telephone</label><input name="telephone" class="form-control"></div>
                <div class="col-md-2"><label>Email</label><input type="email" name="email" class="form-control"></div>
                <div class="col-md-2"><label>Site web</label><input name="site_web" class="form-control"></div>
            </div>
            <div class="row" style="margin-top:10px">
                <div class="col-md-4"><label>Boite postale</label><input name="boite_postale" class="form-control"></div>
                <div class="col-md-4"><label>Logo</label><input type="file" name="logo_file" class="form-control" accept="image/*"></div>
                <div class="col-md-4"><label>Chemin logo existant</label><input name="logo" class="form-control" placeholder="img/logo.png"></div>
            </div>
            <div class="row" style="margin-top:10px">
                <div class="col-md-6"><label>Autorisation d'ouverture</label><textarea name="autorisation_ouverture" class="form-control" rows="3"></textarea></div>
                <div class="col-md-6"><label>Arrete de creation</label><textarea name="arrete_creation" class="form-control" rows="3"></textarea></div>
            </div>
            <div class="row" style="margin-top:10px">
                <div class="col-md-4"><label>&nbsp;</label><button class="btn btn-success form-control">Creer le campus</button></div>
            </div>
        </div>
    </form>
    <table class="table table-bordered table-striped table-condensed">
        <thead>
        <tr>
            <th>#</th>
            <th>Logo</th>
            <th>Nom</th>
            <th>Sigle</th>
            <th>Ville</th>
            <th>Localisation</th>
            <th>BP</th>
            <th>N agrement</th>
            <th>Reference</th>
            <th>Autorisation ouverture</th>
            <th>Arrete creation</th>
            <th>Contact</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php $i = 1; ?>
        @foreach($campus as $c)
            <tr>
                <td>{{ $i++ }}</td>
                <td>@if($c->logo)<img src="{{ asset($c->logo) }}" style="max-height:35px; max-width:80px">@endif</td>
                <td><strong>{{ $c->nom }}</strong></td>
                <td>{{ $c->sigle }}</td>
                <td>{{ $c->ville }}</td>
                <td>{{ $c->localisation }}</td>
                <td>{{ $c->boite_postale }}</td>
                <td>{{ $c->numero_agrement }}</td>
                <td>{{ $c->reference }}</td>
                <td title="{{ $c->autorisation_ouverture }}">{{ strlen($c->autorisation_ouverture) > 45 ? substr($c->autorisation_ouverture, 0, 45).'...' : $c->autorisation_ouverture }}</td>
                <td title="{{ $c->arrete_creation }}">{{ strlen($c->arrete_creation) > 45 ? substr($c->arrete_creation, 0, 45).'...' : $c->arrete_creation }}</td>
                <td>{{ $c->telephone }} {{ $c->email }}</td>
                <td>
                    <a class="btn btn-info btn-xs" href="{{ url('/admin/campus/'.$c->id.'/users') }}">Utilisateurs</a>
                    <a class="btn btn-primary btn-xs" href="{{ url('/admin/campus/'.$c->id.'/specialites') }}">Specialites</a>
                    <a class="btn btn-danger btn-xs" href="{{ url('/admin/campus/destroy/'.$c->id) }}">Supprimer</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="paginate" style="text-align:center"><?php echo(str_replace('/?', '?', $campus->render())); ?></div>
</div>
@endsection

@section('breadcrumb')
<li><strong><a href="{{ url('admin') }}">@lang('admin.dashboard')</a></strong></li>
<li class="active"><strong>{{ $titre }}</strong></li>
@endsection
