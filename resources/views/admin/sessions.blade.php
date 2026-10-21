@extends('skeleton')

@section('sub-content')
    @include('perso.functions')

    <div class="col-md-12">

        @if(isset($success))
            <div class="alert alert-success">
                {{ trans('admin.succes') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif

        <a class="btn btn-warning" href="{{ url('/admin/sessions/global') }}">
            <span class="glyphicon glyphicon-th-list"></span> Sessions examens globales
        </a>

        <?php
        // On suppose que $programmes contient UN seul élément (la matière concernée)
        $prog = (isset($programmes) && count($programmes) > 0) ? $programmes[0] : null;
        ?>

        @if(isset($has_session_tutelle) && $has_session_tutelle && $prog)
            <form method="GET" action="{{ url('/admin/sessions/'.$prog->id) }}" class="form-inline" style="margin-top: 10px;">
                <input type="hidden" name="id_classe" value="{{ $classe->id }}">
                <input type="hidden" name="id_matiere" value="{{ $id_matiere }}">
                <input type="hidden" name="id_classe_matiere" value="{{ $prog->id }}">
                <div class="form-group">
                    <label>Tutelle : </label>
                    <select name="id_tutelle" class="form-control">
                        <option value="">Toutes les tutelles</option>
                        @foreach($tutelles as $tutelle)
                            <option value="{{ $tutelle->id }}" @if($selected_tutelle == $tutelle->id) selected @endif>{{ $tutelle->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <span class="glyphicon glyphicon-filter"></span> Filtrer
                </button>
            </form>
        @endif

        <table class="table table-bordered table-striped table-condensed table-inverse" style="margin-top: 15px;">
            <thead>
            <caption style="font-size: 22px">
                {{ trans('admin.titre_table_sessions')
                    .' '.$classe->nom
                    .' - '.$libelle_matiere
                    .' '.trans('admin.nniveau').' '.(($niveau) ? $niveau->nom : '') }}
            </caption>

            <th>#</th>
            <th>CC1</th>
            <th>TPE1</th>
            <th>SN1</th>
            <th>Rattrapage1</th>
            <th>CC2</th>
            <th>TPE2</th>
            <th>SN2</th>
            <th>Rattrapage2</th>
            @if(isset($has_session_tutelle) && $has_session_tutelle)
                <th>Tutelle</th>
            @endif
            <th>Année académique</th>
            <th>Actions</th>
            </thead>

            <tbody>
            <?php $i = 1; ?>

            {{-- ⚠️ ICI : on boucle sur $sessions, pas sur $programmes --}}
            @forelse($sessions as $sess)
                <tr>
                    <td>{{ $i++ }}</td>

                    <td>{{ $sess->cc1 }}</td>
                    <td>{{ $sess->tpe1 }}</td>
                    <td>{{ $sess->sn1 }}</td>
                    <td>{{ $sess->ratrappage1 }}</td>
                    <td>{{ $sess->cc2 }}</td>
                    <td>{{ $sess->tpe2 }}</td>
                    <td>{{ $sess->sn2 }}</td>
                    <td>{{ $sess->ratrappage2 }}</td>
                    @if(isset($has_session_tutelle) && $has_session_tutelle)
                        <td>{{ $sess->tutelle ? $sess->tutelle->nom : 'Generique' }}</td>
                    @endif
                    <td>{{ $sess->annee_academique }}</td>

                    <td>
                        <div class="btn-group">
                            <a href="#" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                                Actions <b class="caret"></b>
                            </a>
                            <ul class="dropdown-menu">

                                {{-- Modifier cette session --}}
                                <li>
                                    <a style="color: white"
                                       class="btn-primary"
                                       data-toggle="modal"
                                       data-backdrop="false"
                                       href="#edit_session"
                                       onclick="editer_session(
                                               '{{ $sess->id }}',
                                               '{{ $sess->id_classe_matiere }}',
                                               '{{ $sess->id_matiere }}',
                                               '{{ $sess->annee_academique }}',
                                               '{{ $sess->cc1 }}',
                                               '{{ $sess->tpe1 }}',
                                               '{{ $sess->sn1 }}',
                                               '{{ $sess->ratrappage1 }}',
                                               '{{ $sess->cc2 }}',
                                               '{{ $sess->tpe2 }}',
                                               '{{ $sess->sn2 }}',
                                               '{{ $sess->ratrappage2 }}',
                                               '{{ isset($sess->id_tutelle) ? $sess->id_tutelle : '' }}'
                                               )">
                                        <span class="glyphicon glyphicon-pencil"></span>
                                        {{ trans('admin.edit_session') }}
                                    </a>
                                </li>

                                {{-- Supprimer --}}




                                <li><a style="color: white"
                                       href="{{ URL::to('/admin/sessions/destroy/'.$sess->id
        .'?id_classe='.$classe->id
        .'&id_matiere='.$sess->id_matiere
        .'&id_classe_matiere='.$sess->id_classe_matiere
        .'&id_tutelle='.(isset($sess->id_tutelle) ? $sess->id_tutelle : '')) }}"
                                       class="btn-danger">
                                        <span class="glyphicon glyphicon-trash"></span>
                                        {{ trans('admin.del') }}
                                    </a>





                                </li>

                            </ul>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ (isset($has_session_tutelle) && $has_session_tutelle) ? 12 : 11 }}" class="text-center">
                        {{ trans('admin.aucune_session') }}
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>

        {{-- Bouton NOUVELLE SESSION --}}
        <button class="btn btn-primary"
                data-toggle="modal"
                data-backdrop="false"
                href="#add_session">
            <span class="glyphicon glyphicon-plus"></span>
            {{ trans('admin.add_session') }}
        </button>


        {{-- MODAL AJOUT SESSION --}}
        <div class="modal fade" id="add_session">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">x</button>
                        <h4 class="modal-title">{{ trans('admin.add_session') }}</h4>
                    </div>

                    <div class="modal-body">
                        <form accept-charset="UTF-8" role="form" method="POST"
                              action="{{ url('/admin/sessions/store') }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}"/>

                            {{-- Contexte --}}
                            <input type="hidden" name="id_classe" value="{{ $classe->id }}"/>
                            <input type="hidden" name="id_cycle"  value="{{ $id_cycle }}"/>
                            <input type="hidden" name="id_nniveau" value="{{ $id_nniveau }}"/>

                            @if($prog)
                                <input type="hidden" name="id_classe_matiere" value="{{ $prog->id }}"/>
                                <input type="hidden" name="id_matiere" value="{{ $prog->id_matiere }}"/>
                            @endif

                            <fieldset>

                                {{-- Année académique --}}
                                <div class="form-group">
                                    <label for="annee_academique">@lang('admin.annee_academique') :</label>
                                    <select name="annee_academique" id="annee_academique" class="form-control">
                                        @foreach($annees as $a)
                                            <option value="{{ $a->annee_scolaire }}"
                                                    {{ $a->annee_scolaire == $annee ? 'selected' : '' }}>
                                                {{ $a->annee_scolaire }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                @if(isset($has_session_tutelle) && $has_session_tutelle)
                                    <div class="form-group">
                                        <label for="id_tutelle">Tutelle : <span class="required">*</span></label>
                                        <select name="id_tutelle" id="id_tutelle" class="form-control" required>
                                            <option value="">Selectionner une tutelle</option>
                                            @foreach($tutelles as $tutelle)
                                                <option value="{{ $tutelle->id }}" @if($selected_tutelle == $tutelle->id) selected @endif>{{ $tutelle->nom }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <div class="form-group">
                                    <label>CC1 (%) :</label>
                                    <input class="form-control" name="cc1" type="number" min="0" max="100" value="0">
                                </div>
                                <div class="form-group">
                                    <label>TPE1 (%) :</label>
                                    <input class="form-control" name="tpe1" type="number" min="0" max="100" value="0">
                                </div>
                                <div class="form-group">
                                    <label>SN1 (%) :</label>
                                    <input class="form-control" name="sn1" type="number" min="0" max="100" value="0">
                                </div>
                                <div class="form-group">
                                    <label>Rattrapage1 (%) :</label>
                                    <input class="form-control" name="ratrappage1" type="number" min="0" max="100" value="0">
                                </div>

                                <div class="form-group">
                                    <label>CC2 (%) :</label>
                                    <input class="form-control" name="cc2" type="number" min="0" max="100" value="0">
                                </div>
                                <div class="form-group">
                                    <label>TPE2 (%) :</label>
                                    <input class="form-control" name="tpe2" type="number" min="0" max="100" value="0">
                                </div>
                                <div class="form-group">
                                    <label>SN2 (%) :</label>
                                    <input class="form-control" name="sn2" type="number" min="0" max="100" value="0">
                                </div>
                                <div class="form-group">
                                    <label>Rattrapage2 (%) :</label>
                                    <input class="form-control" name="ratrappage2" type="number" min="0" max="100" value="0">
                                </div>

                                <input class="btn btn-success" type="submit" value="{{ trans('admin.confirmer') }}">
                            </fieldset>
                        </form>
                    </div>

                </div>
            </div>
        </div>


        {{-- MODAL MODIFICATION SESSION --}}
        <div class="modal fade" id="edit_session">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">x</button>
                        <h4 class="modal-title">{{ trans('admin.edit_session') }}</h4>
                    </div>

                    <div class="modal-body">
                        <form accept-charset="UTF-8" role="form" method="POST"
                              id="edit_session_form" action="{{ url('/admin/sessions/update') }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}"/>

                            <input type="hidden" name="id" id="session_id" value="0"/>
                            <input type="hidden" name="id_classe" value="{{ $classe->id }}"/>
                            <input type="hidden" name="id_cycle" value="{{ $id_cycle }}"/>
                            <input type="hidden" name="id_nniveau" value="{{ $id_nniveau }}"/>

                            <input type="hidden" name="id_classe_matiere" id="session_classe_matiere" value=""/>
                            <input type="hidden" name="id_matiere" id="session_matiere" value=""/>

                            <fieldset>

                                <div class="form-group">
                                    <label for="edit_annee_academique">@lang('admin.annee_academique') :</label>
                                    <select name="annee_academique" id="edit_annee_academique" class="form-control">
                                        @foreach($annees as $a)
                                            <option value="{{ $a->annee_scolaire }}">
                                                {{ $a->annee_scolaire }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                @if(isset($has_session_tutelle) && $has_session_tutelle)
                                    <div class="form-group">
                                        <label for="edit_id_tutelle">Tutelle : <span class="required">*</span></label>
                                        <select name="id_tutelle" id="edit_id_tutelle" class="form-control" required>
                                            <option value="">Selectionner une tutelle</option>
                                            @foreach($tutelles as $tutelle)
                                                <option value="{{ $tutelle->id }}">{{ $tutelle->nom }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <div class="form-group">
                                    <label>CC1 (%) :</label>
                                    <input class="form-control" id="cc1" name="cc1" type="number" min="0" max="100">
                                </div>
                                <div class="form-group">
                                    <label>TPE1 (%) :</label>
                                    <input class="form-control" id="tpe1" name="tpe1" type="number" min="0" max="100">
                                </div>
                                <div class="form-group">
                                    <label>SN1 (%) :</label>
                                    <input class="form-control" id="sn1" name="sn1" type="number" min="0" max="100">
                                </div>
                                <div class="form-group">
                                    <label>Rattrapage1 (%) :</label>
                                    <input class="form-control" id="ratrappage1" name="ratrappage1" type="number" min="0" max="100">
                                </div>

                                <div class="form-group">
                                    <label>CC2 (%) :</label>
                                    <input class="form-control" id="cc2" name="cc2" type="number" min="0" max="100">
                                </div>
                                <div class="form-group">
                                    <label>TPE2 (%) :</label>
                                    <input class="form-control" id="tpe2" name="tpe2" type="number" min="0" max="100">
                                </div>
                                <div class="form-group">
                                    <label>SN2 (%) :</label>
                                    <input class="form-control" id="sn2" name="sn2" type="number" min="0" max="100">
                                </div>
                                <div class="form-group">
                                    <label>Rattrapage2 (%) :</label>
                                    <input class="form-control" id="ratrappage2" name="ratrappage2" type="number" min="0" max="100">
                                </div>

                                <input class="btn btn-success" type="submit" value="{{ trans('admin.confirmer') }}">
                            </fieldset>
                        </form>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <script>
        function editer_session(id, id_classe_matiere, id_matiere, annee_academique,
                                cc1, tpe1, sn1, ratrappage1,
                                cc2, tpe2, sn2, ratrappage2, id_tutelle) {

            document.getElementById('session_id').value             = id;
            document.getElementById('session_classe_matiere').value = id_classe_matiere;
            document.getElementById('session_matiere').value        = id_matiere;

            // année académique
            var sel = document.getElementById('edit_annee_academique');
            if (sel) {
                sel.value = annee_academique;
            }
            var tutelleSelect = document.getElementById('edit_id_tutelle');
            if (tutelleSelect) {
                tutelleSelect.value = id_tutelle ? id_tutelle : '';
            }

            document.getElementById('cc1').value         = cc1;
            document.getElementById('tpe1').value        = tpe1;
            document.getElementById('sn1').value         = sn1;
            document.getElementById('ratrappage1').value = ratrappage1;

            document.getElementById('cc2').value         = cc2;
            document.getElementById('tpe2').value        = tpe2;
            document.getElementById('sn2').value         = sn2;
            document.getElementById('ratrappage2').value = ratrappage2;
        }
    </script>
@endsection

@section('breadcrumb')
    <li><strong><a href="{{ url('admin') }}">@lang('admin.dashboard')</a></strong></li>
    <li><strong><a href="{{ url('admin/programmes') }}">@lang('admin.selectionner_classe')</a></strong></li>
    {{--<li><strong><a href="{{ url('/admin/programmes/selection-matieres',$niveau) }}">Programme de spécialité</a></strong></li>--}}
    <li class="active"><strong>{{ $titre }}</strong></li>
@endsection





