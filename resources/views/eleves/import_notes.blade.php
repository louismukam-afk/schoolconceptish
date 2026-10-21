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
                <h3 class="panel-title">Importation Excel des notes</h3>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-5">
                        <a class="btn btn-success btn-lg" href="{{ route('notes_import_template', array('id_classe' => $selected_classe, 'id_cycle' => $selected_cycle, 'id_nniveau' => $selected_nniveau)) }}">
                            <span class="fa fa-download"></span> Telecharger le template Excel
                        </a>
                    </div>
                    <div class="col-md-7">
                        <form accept-charset="UTF-8" role="form" method="POST" action="{{ route('notes_import_store') }}" enctype="multipart/form-data">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="context_id_classe" value="{{ $selected_classe }}">
                            <input type="hidden" name="context_id_cycle" value="{{ $selected_cycle }}">
                            <input type="hidden" name="context_id_nniveau" value="{{ $selected_nniveau }}">
                            <div class="input-group">
                                <input class="form-control" id="fichier_notes" name="fichier_notes" type="file" accept=".csv,.txt,.xlsx" @if(!$selected_classe) disabled @endif required>
                                <span class="input-group-btn">
                                    <button class="btn btn-warning" type="submit" @if(!$selected_classe) disabled @endif>
                                        <span class="fa fa-file-excel-o"></span> Importer
                                    </button>
                                </span>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="alert alert-info" style="margin-top: 15px;">
                    Choisir d abord une specialite avec le bouton Importer notes. Les matieres, programmes et sessions affiches concernent uniquement la specialite selectionnee. Le campus reste celui de la session active.
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">Selection du contexte</h3></div>
            <div class="panel-body">
                <form method="GET" action="{{ route('notes_import') }}" class="form-inline">
                    <input type="hidden" name="id_classe" value="{{ $selected_classe }}">
                    <div class="form-group">
                        <label for="id_cycle">Cycle</label>
                        <select class="form-control" name="id_cycle" id="id_cycle">
                            <option value="">Tous</option>
                            @foreach($cycles as $cycle)
                                <option value="{{ $cycle->id }}" @if((string)$selected_cycle === (string)$cycle->id) selected @endif>{{ $cycle->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="id_nniveau">Niveau academique</label>
                        <select class="form-control" name="id_nniveau" id="id_nniveau">
                            <option value="">Tous</option>
                            @foreach($nniveaux as $nniveau)
                                <option value="{{ $nniveau->id }}" @if((string)$selected_nniveau === (string)$nniveau->id) selected @endif>{{ $nniveau->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <span class="fa fa-filter"></span> Appliquer
                    </button>
                    @if($selected_classe_model)
                        <span class="label label-info" style="margin-left: 10px;">Specialite: {{ $selected_classe_model->nom }}</span>
                    @endif
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="panel panel-default">
                    <div class="panel-heading"><h3 class="panel-title">Campus actif</h3></div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-condensed">
                                <thead><tr><th>ID</th><th>Nom</th></tr></thead>
                                <tbody>
                                @foreach($campus as $camp)
                                    <tr><td>{{ $camp->id }}</td><td>{{ $camp->nom }}</td></tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-default">
                    <div class="panel-heading"><h3 class="panel-title">Cycles disponibles</h3></div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-condensed">
                                <thead><tr><th>ID</th><th>Nom</th></tr></thead>
                                <tbody>
                                @foreach($cycles as $cycle)
                                    <tr><td>{{ $cycle->id }}</td><td>{{ $cycle->nom }}</td></tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-default">
                    <div class="panel-heading"><h3 class="panel-title">Filieres disponibles</h3></div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-condensed">
                                <thead><tr><th>ID</th><th>Nom</th></tr></thead>
                                <tbody>
                                @foreach($niveaux as $niveau)
                                    <tr><td>{{ $niveau->id }}</td><td>{{ $niveau->nom }}</td></tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-default">
                    <div class="panel-heading"><h3 class="panel-title">Niveaux academiques</h3></div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-condensed">
                                <thead><tr><th>ID</th><th>Nom</th></tr></thead>
                                <tbody>
                                @foreach($nniveaux as $nniveau)
                                    <tr><td>{{ $nniveau->id }}</td><td>{{ $nniveau->nom }}</td></tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">Champs a remplir dans le fichier</h3></div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table id="table-import-notes" class="table table-bordered table-striped table-condensed">
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
                    <div class="panel-heading"><h3 class="panel-title">Specialites disponibles</h3></div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-condensed">
                                <thead><tr><th>ID</th><th>Nom</th><th>Filiere</th><th>Action</th></tr></thead>
                                <tbody>
                                @foreach($classes as $classe)
                                    <tr>
                                        <td>{{ $classe->id }}</td>
                                        <td>{{ $classe->nom }}</td>
                                        <td>{{ $classe->niveau ? $classe->niveau->nom : '' }}</td>
                                        <td>
                                            <a class="btn btn-xs btn-primary" href="{{ route('notes_import', array('id_classe' => $classe->id, 'id_cycle' => $selected_cycle, 'id_nniveau' => $selected_nniveau)) }}">
                                                <span class="fa fa-file-excel-o"></span> Importer notes
                                            </a>
                                        </td>
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
                    <div class="panel-heading"><h3 class="panel-title">Matieres de la specialite selectionnee</h3></div>
                    <div class="panel-body">
                        @if(!$selected_classe)
                            <div class="alert alert-warning">Selectionner une specialite pour afficher ses matieres.</div>
                        @endif
                        <div class="table-responsive">
                            <table class="table table-bordered table-condensed">
                                <thead><tr><th>ID</th><th>Nom</th></tr></thead>
                                <tbody>
                                @foreach($matieres as $matiere)
                                    <tr><td>{{ $matiere->id }}</td><td>{{ $matiere->nom }}</td></tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">Programmes classe-matiere de la specialite selectionnee</h3></div>
            <div class="panel-body">
                @if(!$selected_classe)
                    <div class="alert alert-warning">Selectionner une specialite pour afficher les programmes classe-matiere.</div>
                @endif
                <div class="table-responsive">
                    <table class="table table-bordered table-condensed">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Specialite</th>
                            <th>Matiere</th>
                            <th>Cycle</th>
                            <th>Niveau</th>
                            <th>Coef</th>
                            <th>Semestre</th>
                            <th>Type</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($programmes as $programme)
                            <tr>
                                <td>{{ $programme->id }}</td>
                                <td>{{ $programme->classe_nom }}</td>
                                <td>{{ $programme->matiere_nom }}</td>
                                <td>{{ $programme->cycle_nom }}</td>
                                <td>{{ $programme->nniveau_nom }}</td>
                                <td>{{ $programme->coef }}</td>
                                <td>{{ $programme->semestre }}</td>
                                <td>{{ $programme->type }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">Sessions examens de la specialite selectionnee</h3></div>
            <div class="panel-body">
                @if(!$selected_classe)
                    <div class="alert alert-warning">Selectionner une specialite pour afficher ses sessions examens.</div>
                @endif
                <div class="table-responsive">
                    <table class="table table-bordered table-condensed">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Programme</th>
                            <th>Specialite</th>
                            <th>Matiere</th>
                            <th>Annee</th>
                            <th>CC1</th>
                            <th>CC2</th>
                            <th>SN1</th>
                            <th>SN2</th>
                            <th>TPE1</th>
                            <th>TPE2</th>
                            <th>RAT1</th>
                            <th>RAT2</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($sessions_examens as $sessionExam)
                            <tr>
                                <td>{{ $sessionExam->id }}</td>
                                <td>{{ $sessionExam->id_classe_matiere }}</td>
                                <td>{{ $sessionExam->classe_nom }}</td>
                                <td>{{ $sessionExam->matiere_nom }}</td>
                                <td>{{ $sessionExam->annee_academique }}</td>
                                <td>{{ $sessionExam->cc1 }}</td>
                                <td>{{ $sessionExam->cc2 }}</td>
                                <td>{{ $sessionExam->sn1 }}</td>
                                <td>{{ $sessionExam->sn2 }}</td>
                                <td>{{ $sessionExam->tpe1 }}</td>
                                <td>{{ $sessionExam->tpe2 }}</td>
                                <td>{{ $sessionExam->ratrappage1 }}</td>
                                <td>{{ $sessionExam->ratrappage2 }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(function () {
            $('#table-import-notes').DataTable({
                paging: false,
                searching: true,
                ordering: false,
                info: false
            });
        });
    </script>
@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('eleves')}}">@lang('menu.gestion_notes')</a></strong></li>
    <li class="active"><strong>Importation notes</strong></li>
@endsection
