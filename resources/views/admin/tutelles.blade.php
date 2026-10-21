@extends('skeleton')
@section('sub-content')
<div class="col-md-12">
    @if(isset($success))<div class="alert alert-success">{{ trans('admin.succes') }}</div>@endif
    <div class="btn-group" style="margin-bottom:10px">
        <a class="btn btn-default" href="{{ url('/admin') }}"><span class="fa fa-arrow-left"></span> Tableau de bord</a>
        <a class="btn btn-primary" href="{{ url('/admin/campus') }}"><span class="fa fa-university"></span> Campus</a>
        <a class="btn btn-info" href="{{ url('/admin/programmes') }}"><span class="fa fa-calendar-o"></span> Programmes</a>
    </div>
    <h3>{{ $titre }}</h3>
    <form method="post" action="{{ url('/admin/tutelles/store') }}" class="panel panel-default" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-3"><label>Nom</label><input name="nom" class="form-control" required></div>
                <div class="col-md-2"><label>Ville</label><input name="ville" class="form-control"></div>
                <div class="col-md-3"><label>Ministere tutelle</label><input name="ministere_tutelle" class="form-control"></div>
                <div class="col-md-2"><label>Faculte</label><input name="faculte" class="form-control"></div>
            </div>
            <div class="row" style="margin-top:10px">
                <div class="col-md-2"><label>Telephone</label><input name="telephone" class="form-control"></div>
                <div class="col-md-2"><label>Email</label><input name="email" class="form-control"></div>
                <div class="col-md-3"><label>Localisation</label><input name="localisation" class="form-control"></div>
                <div class="col-md-3">
                    <label>Logo</label>
                    <input type="file" name="logo_file" class="form-control">
                    <input type="hidden" name="logo">
                </div>
                <div class="col-md-2"><label>&nbsp;</label><button class="btn btn-success form-control">Creer la tutelle</button></div>
            </div>
            <div class="row" style="margin-top:10px">
                <div class="col-md-3"><label>Description 1</label><input name="description1" class="form-control"></div>
                <div class="col-md-3"><label>Description 2</label><input name="description2" class="form-control"></div>
                <div class="col-md-3">
                    <label>Logo faculte</label>
                    <input type="file" name="logo_faculte_file" class="form-control">
                    <input type="hidden" name="logo_faculte">
                </div>
            </div>
            <div class="row" style="margin-top:10px">
                <div class="col-md-3"><label>Republique francophone</label><input name="republique_francophone" class="form-control"></div>
                <div class="col-md-3"><label>Republique anglophone</label><input name="republique_anglophone" class="form-control"></div>
                <div class="col-md-3"><label>Patrie francophone</label><input name="patrie_francophone" class="form-control"></div>
                <div class="col-md-3"><label>Patrie anglophone</label><input name="patrie_anglophone" class="form-control"></div>
            </div>
            <div class="row" style="margin-top:10px">
                <div class="col-md-4"><label>Ministere anglophone</label><input name="ministere_tutelle_anglophone" class="form-control"></div>
                <div class="col-md-4"><label>Nom faculte anglophone</label><input name="faculte_anglophone" class="form-control"></div>
                <div class="col-md-4"><label>Tutelle anglophone</label><input name="tutelle_anglophone" class="form-control"></div>
            </div>
        </div>
    </form>
    <table class="table table-bordered table-striped table-condensed">
        <thead><tr><th>#</th><th>Logos</th><th>Nom</th><th>Faculte</th><th>Ville</th><th>Contact</th><th>Actions</th></tr></thead>
        <tbody>
        <?php $i = 1; ?>
        @foreach($tutelles as $t)
            <tr>
                <td>{{ $i++ }}</td>
                <td style="white-space:nowrap">
                    @if($t->logo)
                        <img src="{{ URL::asset($t->logo) }}" alt="{{ $t->nom }}" style="height:42px;max-width:60px;object-fit:contain">
                    @endif
                    @if($t->logo_faculte)
                        <img src="{{ URL::asset($t->logo_faculte) }}" alt="{{ $t->faculte }}" style="height:42px;max-width:60px;object-fit:contain">
                    @endif
                </td>
                <td>{{ $t->nom }}</td>
                <td>{{ $t->faculte }}</td>
                <td>{{ $t->ville }}</td>
                <td>{{ $t->telephone }} {{ $t->email }}</td>
                <td>
                    <a class="btn btn-primary btn-xs" href="{{ url('/admin/tutelles/'.$t->id.'/specialites') }}">Specialites</a>
                    <a class="btn btn-info btn-xs" href="#edit_tutelle_{{ $t->id }}" data-toggle="modal">Details</a>
                    <a class="btn btn-danger btn-xs" href="{{ url('/admin/tutelles/destroy/'.$t->id) }}">Supprimer</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @foreach($tutelles as $t)
        <div class="modal fade" id="edit_tutelle_{{ $t->id }}">
            <div class="modal-dialog" style="width: 980px">
                <div class="modal-content">
                    <form method="post" action="{{ url('/admin/tutelles/update') }}" enctype="multipart/form-data">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="id" value="{{ $t->id }}">
                        <input type="hidden" name="logo" value="{{ $t->logo }}">
                        <input type="hidden" name="logo_faculte" value="{{ $t->logo_faculte }}">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">x</button>
                            <h4 class="modal-title">Details et modification de la tutelle</h4>
                        </div>
                        <div class="modal-body">
                            <table class="table table-bordered table-condensed">
                                <tr>
                                    <th>Nom</th>
                                    <td>{{ $t->nom }}</td>
                                    <th>Tutelle anglophone</th>
                                    <td>{{ $t->tutelle_anglophone }}</td>
                                </tr>
                                <tr>
                                    <th>Ministere</th>
                                    <td>{{ $t->ministere_tutelle }}</td>
                                    <th>Ministere anglophone</th>
                                    <td>{{ $t->ministere_tutelle_anglophone }}</td>
                                </tr>
                                <tr>
                                    <th>Faculte</th>
                                    <td>{{ $t->faculte }}</td>
                                    <th>Faculte anglophone</th>
                                    <td>{{ $t->faculte_anglophone }}</td>
                                </tr>
                                <tr>
                                    <th>Ville / localisation</th>
                                    <td>{{ $t->ville }} {{ $t->localisation }}</td>
                                    <th>Contact</th>
                                    <td>{{ $t->telephone }} {{ $t->email }}</td>
                                </tr>
                                <tr>
                                    <th>Republique FR</th>
                                    <td>{{ $t->republique_francophone }}</td>
                                    <th>Republic EN</th>
                                    <td>{{ $t->republique_anglophone }}</td>
                                </tr>
                                <tr>
                                    <th>Patrie FR</th>
                                    <td>{{ $t->patrie_francophone }}</td>
                                    <th>Patrie EN</th>
                                    <td>{{ $t->patrie_anglophone }}</td>
                                </tr>
                                <tr>
                                    <th>Description 1</th>
                                    <td>{{ $t->description1 }}</td>
                                    <th>Description 2</th>
                                    <td>{{ $t->description2 }}</td>
                                </tr>
                                <tr>
                                    <th>Logo tutelle</th>
                                    <td>
                                        @if($t->logo)
                                            <img src="{{ URL::asset($t->logo) }}" alt="{{ $t->nom }}" style="height:55px;max-width:110px;object-fit:contain">
                                        @endif
                                    </td>
                                    <th>Logo faculte</th>
                                    <td>
                                        @if($t->logo_faculte)
                                            <img src="{{ URL::asset($t->logo_faculte) }}" alt="{{ $t->faculte }}" style="height:55px;max-width:110px;object-fit:contain">
                                        @endif
                                    </td>
                                </tr>
                            </table>
                            <h4>Modifier les informations</h4>
                            <div class="row">
                                <div class="col-md-4"><label>Nom</label><input name="nom" value="{{ $t->nom }}" class="form-control" required></div>
                                <div class="col-md-3"><label>Ville</label><input name="ville" value="{{ $t->ville }}" class="form-control"></div>
                                <div class="col-md-5"><label>Ministere tutelle</label><input name="ministere_tutelle" value="{{ $t->ministere_tutelle }}" class="form-control"></div>
                            </div>
                            <div class="row" style="margin-top:10px">
                                <div class="col-md-4"><label>Faculte</label><input name="faculte" value="{{ $t->faculte }}" class="form-control"></div>
                                <div class="col-md-4"><label>Telephone</label><input name="telephone" value="{{ $t->telephone }}" class="form-control"></div>
                                <div class="col-md-4"><label>Email</label><input name="email" value="{{ $t->email }}" class="form-control"></div>
                            </div>
                            <div class="row" style="margin-top:10px">
                                <div class="col-md-4"><label>Localisation</label><input name="localisation" value="{{ $t->localisation }}" class="form-control"></div>
                                <div class="col-md-4">
                                    <label>Logo</label>
                                    <input type="file" name="logo_file" class="form-control">
                                    @if($t->logo)
                                        <div style="margin-top:5px"><img src="{{ URL::asset($t->logo) }}" alt="{{ $t->nom }}" style="height:45px;max-width:90px;object-fit:contain"></div>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <label>Logo faculte</label>
                                    <input type="file" name="logo_faculte_file" class="form-control">
                                    @if($t->logo_faculte)
                                        <div style="margin-top:5px"><img src="{{ URL::asset($t->logo_faculte) }}" alt="{{ $t->faculte }}" style="height:45px;max-width:90px;object-fit:contain"></div>
                                    @endif
                                </div>
                            </div>
                            <div class="row" style="margin-top:10px">
                                <div class="col-md-6"><label>Description 1</label><input name="description1" value="{{ $t->description1 }}" class="form-control"></div>
                                <div class="col-md-6"><label>Description 2</label><input name="description2" value="{{ $t->description2 }}" class="form-control"></div>
                            </div>
                            <div class="row" style="margin-top:10px">
                                <div class="col-md-3"><label>Republique francophone</label><input name="republique_francophone" value="{{ $t->republique_francophone }}" class="form-control"></div>
                                <div class="col-md-3"><label>Republique anglophone</label><input name="republique_anglophone" value="{{ $t->republique_anglophone }}" class="form-control"></div>
                                <div class="col-md-3"><label>Patrie francophone</label><input name="patrie_francophone" value="{{ $t->patrie_francophone }}" class="form-control"></div>
                                <div class="col-md-3"><label>Patrie anglophone</label><input name="patrie_anglophone" value="{{ $t->patrie_anglophone }}" class="form-control"></div>
                            </div>
                            <div class="row" style="margin-top:10px">
                                <div class="col-md-4"><label>Ministere anglophone</label><input name="ministere_tutelle_anglophone" value="{{ $t->ministere_tutelle_anglophone }}" class="form-control"></div>
                                <div class="col-md-4"><label>Nom faculte anglophone</label><input name="faculte_anglophone" value="{{ $t->faculte_anglophone }}" class="form-control"></div>
                                <div class="col-md-4"><label>Tutelle anglophone</label><input name="tutelle_anglophone" value="{{ $t->tutelle_anglophone }}" class="form-control"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
                            <button class="btn btn-success">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
    <div class="paginate" style="text-align:center"><?php echo(str_replace('/?', '?', $tutelles->render())); ?></div>
</div>
@endsection

@section('breadcrumb')
<li><strong><a href="{{ url('admin') }}">@lang('admin.dashboard')</a></strong></li>
<li class="active"><strong>{{ $titre }}</strong></li>
@endsection
