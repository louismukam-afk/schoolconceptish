@extends('skeleton')
@section('sub-content')
    <div class="col-md-12">
        @if(isset($import_success))
            <div class="alert alert-success">{{ $import_success }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">Importation Excel des inscriptions</h3>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-5">
                        <a class="btn btn-success btn-lg" href="{{ route('inscriptions_import_template') }}">
                            <span class="fa fa-download"></span> Telecharger le template Excel
                        </a>
                    </div>
                    <div class="col-md-7">
                        <form accept-charset="UTF-8" role="form" method="POST" action="{{ route('inscriptions_import_store') }}" enctype="multipart/form-data">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <div class="input-group">
                                <input class="form-control" id="fichier_inscriptions" name="fichier_inscriptions" type="file" accept=".csv,.txt,.xlsx" required>
                                <span class="input-group-btn">
                                    <button class="btn btn-warning" type="submit">
                                        <span class="fa fa-file-excel-o"></span> Importer
                                    </button>
                                </span>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="alert alert-info" style="margin-top: 15px;">
                    Remplir une ligne par inscription. Le fichier accepte le CSV telecharge ici ou un XLSX simple avec les memes colonnes sur la premiere feuille. Ne pas renommer les entetes.
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Cycles disponibles</h3>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-condensed">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($cycles as $cycle)
                                    <tr>
                                        <td>{{ $cycle->id }}</td>
                                        <td>{{ $cycle->nom }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Filieres disponibles</h3>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-condensed">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($niveaux as $niveau)
                                    <tr>
                                        <td>{{ $niveau->id }}</td>
                                        <td>{{ $niveau->nom }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Specialites disponibles</h3>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-condensed">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Filiere</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($classes as $classe)
                                    <tr>
                                        <td>{{ $classe->id }}</td>
                                        <td>{{ $classe->nom }}</td>
                                        <td>{{ $classe->niveau ? $classe->niveau->nom : '' }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Niveaux academiques</h3>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-condensed">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($nniveaux as $nniveau)
                                    <tr>
                                        <td>{{ $nniveau->id }}</td>
                                        <td>{{ $nniveau->nom }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Champs a remplir dans le fichier</h3>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table id="table-import-inscriptions" class="table table-bordered table-striped table-condensed">
                        <thead>
                        <tr>
                            <th>Colonne Excel</th>
                            <th>Table</th>
                            <th>Obligatoire</th>
                            <th>Donnees a remplir</th>
                            <th>Valeurs acceptees</th>
                            <th>Valeur par defaut</th>
                            <th>Exemple</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($fields as $field => $meta)
                            <tr>
                                <td><strong>{{ $field }}</strong></td>
                                <td>{{ $meta['table'] }}</td>
                                <td>{{ $meta['obligatoire'] }}</td>
                                <td>{{ $meta['description'] }}</td>
                                <td>{{ $meta['valeurs'] }}</td>
                                <td>{{ $meta['defaut'] }}</td>
                                <td>{{ $meta['exemple'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Caisses disponibles</h3>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-condensed">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Type</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($caisses as $caisse)
                                    <tr>
                                        <td>{{ $caisse->id }}</td>
                                        <td>{{ $caisse->nom }}</td>
                                        <td>{{ $caisse->type_caisse }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Banques disponibles</h3>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-condensed">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($banques as $banque)
                                    <tr>
                                        <td>{{ $banque->id }}</td>
                                        <td>{{ $banque->nom }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(function () {
            $('#table-import-inscriptions').DataTable({
                paging: false,
                searching: true,
                ordering: false,
                info: false
            });
        });
    </script>
@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('inscriptions')}}">@lang('menu.gestion_inscriptions')</a></strong></li>
    <li class="active"><strong>Importation inscriptions</strong></li>
@endsection
