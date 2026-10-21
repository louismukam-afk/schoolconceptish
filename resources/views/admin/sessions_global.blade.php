@extends('skeleton')

@section('sub-content')
    <div class="col-md-12">
        @if(isset($success))
            <div class="alert alert-success">
                {{ trans('admin.succes') }}
            </div>
        @endif

        @if(isset($global_session_success))
            <div class="alert alert-success">
                {{ $global_session_success }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="row">
            <div class="col-md-12">
                <a class="btn btn-default" href="{{ url('/admin/programmes') }}">
                    <span class="glyphicon glyphicon-arrow-left"></span> Retour aux programmes
                </a>
            </div>
        </div>

        <form method="GET" action="{{ url('/admin/sessions/global') }}" class="panel panel-default" id="global-session-filter-form" style="margin-top: 15px;">
            <div class="panel-heading">
                <strong>Cible globale des sessions examens</strong>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-3">
                        <label>Annee academique</label>
                        <select name="annee_academique" class="form-control js-global-session-filter">
                            @foreach($annees as $a)
                                <option value="{{ $a->annee_scolaire }}" @if($selected['annee_academique'] == $a->annee_scolaire) selected @endif>
                                    {{ $a->annee_scolaire }}
                                </option>
                            @endforeach
                            @if(count($annees) == 0)
                                <option value="{{ $annee }}">{{ $annee }}</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Tutelle</label>
                        <select name="id_tutelle" class="form-control js-global-session-filter">
                            <option value="">Selectionner une tutelle</option>
                            @foreach($tutelles as $tutelle)
                                <option value="{{ $tutelle->id }}" @if($selected['id_tutelle'] == $tutelle->id) selected @endif>{{ $tutelle->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Specialite</label>
                        <select name="id_classe" class="form-control js-global-session-filter">
                            <option value="">Toutes les specialites</option>
                            @foreach($classes as $classeItem)
                                <option value="{{ $classeItem->id }}" @if($selected['id_classe'] == $classeItem->id) selected @endif>{{ $classeItem->getNom() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Cycle</label>
                        <select name="id_cycle" class="form-control js-global-session-filter">
                            <option value="">Tous les cycles</option>
                            @foreach($cycles as $cycleItem)
                                <option value="{{ $cycleItem->id }}" @if($selected['id_cycle'] == $cycleItem->id) selected @endif>{{ $cycleItem->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row" style="margin-top: 10px;">
                    <div class="col-md-3">
                        <label>Filiere</label>
                        <select name="id_filiere" class="form-control js-global-session-filter">
                            <option value="">Toutes les filieres</option>
                            @foreach($filieres as $filiere)
                                <option value="{{ $filiere->id }}" @if($selected['id_filiere'] == $filiere->id) selected @endif>{{ $filiere->getNom() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Niveau</label>
                        <select name="id_nniveau" class="form-control js-global-session-filter">
                            <option value="">Tous les niveaux</option>
                            @foreach($nniveaux as $nniveau)
                                <option value="{{ $nniveau->id }}" @if($selected['id_nniveau'] == $nniveau->id) selected @endif>{{ $nniveau->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Semestre</label>
                        <select name="semestre" class="form-control js-global-session-filter">
                            <option value="">Tous les semestres</option>
                            <option value="1" @if($selected['semestre'] === '1' || $selected['semestre'] === 1) selected @endif>Semestre 1</option>
                            <option value="2" @if($selected['semestre'] === '2' || $selected['semestre'] === 2) selected @endif>Semestre 2</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary form-control">
                            <span class="glyphicon glyphicon-search"></span> Voir la cible
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <div class="alert alert-info">
            Specialites ciblees : <strong>{{ $target_specialites }}</strong>
            &nbsp;|&nbsp; Cycles cibles : <strong>{{ $target_cycles }}</strong>
            &nbsp;|&nbsp; Matieres ciblees : <strong>{{ $target_programmes }}</strong>
            &nbsp;|&nbsp; Sessions deja existantes pour {{ $selected['annee_academique'] }} : <strong>{{ $existing_sessions }}</strong>
        </div>
        @if($selected['id_tutelle'])
            <div class="panel panel-default">
                <div class="panel-heading">
                    <strong>Specialites affectees a la tutelle selectionnee</strong>
                </div>
                <div class="panel-body">
                    @if(count($selected_tutelle_specialites) > 0)
                        @foreach($selected_tutelle_specialites as $specialite)
                            <span class="label label-info" style="display:inline-block; margin: 2px;">{{ $specialite->getNom() }}</span>
                        @endforeach
                    @else
                        <span class="text-warning">Aucune specialite n'est affectee a cette tutelle. Les matieres seront donc ciblees uniquement avec les filtres cycle, filiere, niveau et specialite.</span>
                    @endif
                </div>
            </div>
        @endif
        <div class="alert alert-warning">
            Choisissez une tutelle avant d'appliquer. Les sessions deja existantes dans cette cible seront mises a jour pour cette tutelle, et les matieres sans session recevront une nouvelle session pour l'annee selectionnee.
        </div>

        <form method="POST" action="{{ url('/admin/sessions/global/store') }}" class="panel panel-default">
            <div class="panel-heading">
                <strong>Parametres a appliquer a toutes les matieres ciblees</strong>
            </div>
            <div class="panel-body">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="annee_academique" value="{{ $selected['annee_academique'] }}">
                <input type="hidden" name="id_tutelle" value="{{ $selected['id_tutelle'] }}">
                <input type="hidden" name="id_classe" value="{{ $selected['id_classe'] }}">
                <input type="hidden" name="id_cycle" value="{{ $selected['id_cycle'] }}">
                <input type="hidden" name="id_filiere" value="{{ $selected['id_filiere'] }}">
                <input type="hidden" name="id_nniveau" value="{{ $selected['id_nniveau'] }}">
                <input type="hidden" name="semestre" value="{{ $selected['semestre'] }}">

                <div class="row">
                    <div class="col-md-3">
                        <label>CC1 (%)</label>
                        <input class="form-control" name="cc1" type="number" min="0" max="100" value="{{ old('cc1', 0) }}">
                    </div>
                    <div class="col-md-3">
                        <label>TPE1 (%)</label>
                        <input class="form-control" name="tpe1" type="number" min="0" max="100" value="{{ old('tpe1', 0) }}">
                    </div>
                    <div class="col-md-3">
                        <label>SN1 (%)</label>
                        <input class="form-control" name="sn1" type="number" min="0" max="100" value="{{ old('sn1', 0) }}">
                    </div>
                    <div class="col-md-3">
                        <label>Rattrapage1 (%)</label>
                        <input class="form-control" name="ratrappage1" type="number" min="0" max="100" value="{{ old('ratrappage1', 0) }}">
                    </div>
                </div>

                <div class="row" style="margin-top: 10px;">
                    <div class="col-md-3">
                        <label>CC2 (%)</label>
                        <input class="form-control" name="cc2" type="number" min="0" max="100" value="{{ old('cc2', 0) }}">
                    </div>
                    <div class="col-md-3">
                        <label>TPE2 (%)</label>
                        <input class="form-control" name="tpe2" type="number" min="0" max="100" value="{{ old('tpe2', 0) }}">
                    </div>
                    <div class="col-md-3">
                        <label>SN2 (%)</label>
                        <input class="form-control" name="sn2" type="number" min="0" max="100" value="{{ old('sn2', 0) }}">
                    </div>
                    <div class="col-md-3">
                        <label>Rattrapage2 (%)</label>
                        <input class="form-control" name="ratrappage2" type="number" min="0" max="100" value="{{ old('ratrappage2', 0) }}">
                    </div>
                </div>

                <button type="submit" class="btn btn-success" id="apply-global-sessions" style="margin-top: 15px;">
                    <span class="glyphicon glyphicon-ok"></span> Appliquer a toutes les matieres ciblees
                </button>
            </div>
        </form>
        <script>
            (function () {
                var form = document.getElementById('global-session-filter-form');
                if (!form) {
                    return;
                }

                var selects = form.querySelectorAll('.js-global-session-filter');
                for (var i = 0; i < selects.length; i++) {
                    selects[i].addEventListener('change', function () {
                        form.submit();
                    });
                }
            })();
        </script>
    </div>
@endsection

@section('breadcrumb')
    <li><strong><a href="{{ url('admin') }}">@lang('admin.dashboard')</a></strong></li>
    <li><strong><a href="{{ url('admin/programmes') }}">@lang('admin.selectionner_classe')</a></strong></li>
    <li class="active"><strong>{{ $titre }}</strong></li>
@endsection
