@extends('skeleton')
@section('sub-content')
    @if(isset($success))

        <div class="alert alert-success">
            {{  trans('admin.succes')  }}
        </div>
        @if(!isset($inscription_edition))
            <div class="col-md-12">
                <a  class="btn btn-warning btn-lg" href="{{URL::to('/inscriptions/print/'.$success)}}"><span class="glyphicon glyphicon-print"></span> {{trans('inscription.print_receipe')}}</a>

            </div>
        @endif

    @endif

    @if(isset($custom_error))

        <div class="alert alert-danger">
            {{  $custom_error  }}
        </div>

    @endif

    @if(isset($old_record))
        <p style="font-size: 18px"> <strong>Matricule : {{$e->matricule}} </strong></p>
        <p style="font-size: 18px"> <strong>{{trans('admin.nom')}} : {{$e->nom.' '.$e->prenom}} </strong></p>

        <div class="col-md-8">
            <form class="form-horizontal" id="edit_inscription_form" method="post" action="{{URL::to('/inscriptions/update/')}}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                <input type="hidden" name="id_eleve" value="{{$e->id}}">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>  @lang('admin.cycle'): </label>
                        <select required form="edit_inscription_form" id="edit_cycle"  name="cycle" class="form-control" onchange="filterEditInscriptionFields()">
                            @foreach($cycles as $c)
                                <option value="{{$c->id}}" @if($inscription->id_cycle==$c->id) selected @endif>{{$c->nom}}</option>
                            @endforeach
                        </select>

                    </div>
                    <div class="form-group">
                        <label>@lang('admin.filiere')  : </label>
                        <select form="edit_inscription_form" id="edit_filiere" name="id_niveau" class="form-control" onchange="filterEditInscriptionFields()" required>
                            <option value=""> -- Choisir --</option>
                            @foreach($niveaux as $n)
                                <option value="{{$n->id}}" @if($inscription && $inscription->classe && $inscription->classe->id_niveau==$n->id) selected @endif>{{$n->getNom()}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>@lang('admin.specialites')  : </label>

                        <select required form="edit_inscription_form" id="edit_classe" name="classe" class="form-control autocomplete">
                            <option value=""> -- @lang('admin.choix_classe') --</option>
                            @foreach($classes as $c)
                                <option value="{{$c->id}}" data-filiere="{{$c->id_niveau}}" @if($inscription->id_classe==$c->id) selected @endif>{{$c->getNom()}}</option>
                            @endforeach
                        </select>


                    </div>
                    <div class="form-group">
                        <label>  @lang('admin.nniveau') </label>
                        <select form="edit_inscription_form" id="edit_nniveau" name="nniveau" class="form-control" required>
                            <option value=""> -- Choisir --</option>
                            @foreach($nniveaux as $nn)
                                <option value="{{$nn->id}}" data-cycle="{{$nn->id_cycle}}" @if($inscription->id_nniveau==$nn->id) selected @endif>{{$nn->nom}}</option>
                            @endforeach
                        </select>

                    </div>
                    <div class="form-group">
                        <label for="edit_type_scolarite">{{  trans('admin.type_scolarite')  }} : </label>
                        <select form="edit_inscription_form" id="edit_type_scolarite" name="type_scolarite" class="form-control" required>
                            <option value="0" @if($inscription->type_scolarite==0) selected @endif>{{ trans('admin.ancien') }}</option>
                            <option value="1" @if($inscription->type_scolarite==1) selected @endif>{{ trans('admin.nouveau') }}</option>
                        </select>
                    </div>
                        <div class="form-group">
                            <label>{{trans('admin.matricule')}} : (@lang('admin.no_matricule'))</label>
                            <p class="form-control-static">Le matricule sera genere automatiquement si l'etudiant n'en a pas encore.</p>

                        </div>

                    <input type="submit" class="btn btn-success form-control" value="{{trans('admin.modifier')}}" >
                </div>
            </form>

        </div>

    @elseif(isset($e))
        <p style="font-size: 18px"> <strong>Matricule : {{$e->matricule}} </strong></p>
        <p style="font-size: 18px"> <strong>{{trans('admin.nom')}} : {{$e->nom.' '.$e->prenom}} </strong></p>

        <div class="col-md-8">
            <form class="form-horizontal" id="add_inscription_form" method="post" action="{{URL::to('/inscriptions/store/')}}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                <input type="hidden" name="id_eleve" value="{{$e->id}}">

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="scolarite_style">Profil de scolarite : </label>
                        <select form="add_inscription_form" id="scolarite_style" name="scolarite_style" class="form-control" onchange="getScolarite()" required>
                            <option value="0" @if(strcmp(old('scolarite_style'),'0')==0) selected @endif>Ancien profil / ancien style de scolarite</option>
                            <option value="1" @if(strcmp(old('scolarite_style','1'),'1')==0) selected @endif>Nouveau profil / nouveau style de scolarite</option>
                        </select>
                        <input type="hidden" id="id_scolarite" name="id_scolarite" value="{{ old('id_scolarite') }}">
                    </div>
                    <div class="form-group">
                        <label>  @lang('admin.cycle'): </label>
                        <select form="add_inscription_form" id="cycle" name="cycle" class="form-control" onchange="filterInscriptionFields(); getScolarite()" required>
                            <option value=""> -- @lang('admin.choose_cycle') --</option>

                        @foreach($cycles as $c)
                                <option value="{{$c->id}}" @if(old('cycle')==$c->id) selected @endif>{{$c->nom}}</option>
                            @endforeach
                        </select>

                    </div>
                    <div class="form-group">
                        <label>@lang('admin.filiere')  : </label>
                        <select form="add_inscription_form" id="filiere" name="id_niveau" class="form-control" onchange="filterInscriptionFields(); getScolarite()" required>
                            <option value=""> -- Choisir --</option>
                            @foreach($niveaux as $n)
                                <option value="{{$n->id}}" @if(old('id_niveau')==$n->id) selected @endif>{{$n->getNom()}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>@lang('admin.specialites')  : </label>

                        <select form="add_inscription_form" id="classe" name="classe" class="form-control autocomplete" onchange="getScolarite()" required>
                            <option value=""> -- @lang('admin.choix_classe') --</option>
                            @foreach($classes as $c)
                                <option id="{{$c->niveau->id}}" data-filiere="{{$c->id_niveau}}" value="{{$c->id}}" @if(old('classe')==$c->id) selected @endif>{{$c->getNom()}}</option>
                            @endforeach
                        </select>

                    </div>
                    <div class="form-group">
                        <label for="type_scolarite">{{  trans('admin.type_scolarite')  }} : </label>
                        <select form="add_inscription_form" id="type_scolarite" name="type_scolarite" class="form-control" onchange="getScolarite()" >
                            <option id="0" value="0" @if(strcmp(old('type_scolarite'),'0')==0) selected @endif>{{ trans('admin.ancien') }}</option>
                            <option id="1" value="1" @if(strcmp(old('type_scolarite'),'1')==0) selected @endif>{{ trans('admin.nouveau') }}</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>  @lang('admin.nniveau') </label>
                        <select form="add_inscription_form" id="nniveau" name="nniveau" class="form-control" onchange="getScolarite()" required>
                            <option value=""> -- Choisir --</option>
                            @foreach($nniveaux as $nn)
                                <option value="{{$nn->id}}" data-cycle="{{$nn->id_cycle}}" @if(old('nniveau')==$nn->id) selected @endif>{{$nn->nom}}</option>
                            @endforeach
                        </select>

                    </div>




                </div>
                <div class="col-md-6" style="padding-left: 30px">
                    <div class="form-group">
                        <label>Campus : <span class="required">*</span></label>
                        @if(isset($campus_actif) && $campus_actif)
                            <input type="hidden" name="id_campus" value="{{ $campus_actif->id }}">
                            <p class="form-control-static">
                                <strong>{{ $campus_actif->nom }}</strong>
                                @if($campus_actif->sigle) - {{ $campus_actif->sigle }} @endif
                                @if($campus_actif->localisation) | {{ $campus_actif->localisation }} @endif
                            </p>
                        @else
                            <select name="id_campus" class="form-control" required>
                                <option value=""> -- Choisir --</option>
                                @if(isset($campus))
                                    @foreach($campus as $cp)
                                        <option value="{{ $cp->id }}" @if(old('id_campus')==$cp->id) selected @endif>{{ $cp->nom }}</option>
                                    @endforeach
                                @endif
                            </select>
                        @endif
                    </div>
                    @if($e->matricule==null)
                        <div class="form-group">
                            <label>{{trans('admin.matricule')}} : (@lang('admin.no_matricule'))</label>
                            <p class="form-control-static">Le matricule sera genere automatiquement apres l'inscription.</p>

                        </div>
                    @endif
                    <div class="form-group">
                        <div id="show_montant" class="alert alert-info hidden"  >
                            <span  style="font-size: 18px">

                                <span id="show_montant_value">25000</span>
                                @lang('inscription.devise')
                            </span>
                            <div id="scolarite_details" style="margin-top: 5px"></div>
                        </div>
                        <div id="scolarite_error" class="alert alert-danger hidden">
                            Aucune scolarite ne correspond a cette selection.
                        </div>

                        <div id="montant-group" class="hidden">
                            <label for="montant">{{trans('inscription.montant')}} : </label>
                            <div class="input-group">
                                <input type="number" name="montant" id="montant" value="{{ old('montant') }}"  class="form-control disabled" required readonly>
                                <span class="input-group-addon"> {{trans('inscription.devise')}}</span>
                            </div>
                        </div>


                    </div>
                        <div class="form-group">
                            <label>Montant en lettre</label>
                            <input type="text" name="lettre"  class="form-control"  value="{{old('lettre')}}">

                        </div>
                        <div class="form-group">
                            <label for="mode_paye">{{trans('inscription.detail')}} : </label>
                            <select form="add_inscription_form" id="mode_paye" name="mode_paye" class="form-control">
                                <option value="bancaire">{{trans('inscription.bancaire') }}</option>
                                <option value="orange money">{{trans('inscription.om')}}</option>
                                <option value="espece">{{trans('inscription.espece')}}</option>

                            </select>

                        </div>
                        <div class="form-group">
                            <label>Type d'operation : <span class="required">*</span></label>
                            <select name="operation_source" id="operation_source" class="form-control" onchange="toggleOperationSource()" required>
                                <option value="caisse" @if(old('operation_source')=='caisse') selected @endif>Espece / Caisse</option>
                                <option value="banque" @if(old('operation_source')=='banque') selected @endif>Banque</option>
                            </select>
                        </div>
                        <div class="form-group" id="caisse_group">
                            <label>Caisse d'entree</label>
                            <select name="id_caisse" class="form-control">
                                @if(isset($caisses))
                                    @foreach($caisses as $caisse)
                                        <option value="{{ $caisse->id }}" @if(old('id_caisse')==$caisse->id) selected @endif>{{ $caisse->nom }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div id="banque_group" class="hidden">
                            <div class="form-group">
                                <label>Banque</label>
                                <select name="id_banque" class="form-control">
                                    @if(isset($banques))
                                        @foreach($banques as $banque)
                                            <option value="{{ $banque->id }}" @if(old('id_banque')==$banque->id) selected @endif>{{ $banque->nom }} {{ $banque->numero_compte }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Numero de versement</label>
                                <input type="text" name="numero_versement" class="form-control" value="{{ old('numero_versement') }}">
                            </div>
                            <div class="form-group">
                                <label>Reference</label>
                                <input type="text" name="reference_operation" class="form-control" value="{{ old('reference_operation') }}">
                            </div>
                            <div class="form-group">
                                <label>Demande</label>
                                <input type="text" name="demande_operation" class="form-control" value="{{ old('demande_operation') }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>{{trans('inscription.ref')}} </label>
                            <input type="text" name="ref_versement"  class="form-control"  value="{{old('ref_versement')}}">

                        </div>
                </div>
                <input type="submit" class="btn btn-success form-control" value="{{trans('inscription.inscrire')}}" >

            </form>

        </div>

    @endif

    <script>
        var choosePlaceholder = {!! json_encode(' -- Choisir --') !!};
        var classPlaceholder = {!! json_encode(' -- '.trans('admin.choix_classe').' --') !!};

        var inscriptionClasses = [
            @if(isset($classes))
                @foreach($classes as $c)
                {
                    id: '{{$c->id}}',
                    filiere: '{{$c->id_niveau}}',
                    label: {!! json_encode($c->getNom()) !!},
                    selected: {{ old('classe')==$c->id ? 'true' : 'false' }}
                },
                @endforeach
            @endif
        ];

        var inscriptionNniveaux = [
            @if(isset($nniveaux))
                @foreach($nniveaux as $nn)
                {
                    id: '{{$nn->id}}',
                    cycle: '{{$nn->id_cycle}}',
                    label: {!! json_encode($nn->nom) !!},
                    selected: {{ old('nniveau')==$nn->id ? 'true' : 'false' }}
                },
                @endforeach
            @endif
        ];

        var scolariteProfiles = [
            @if(isset($scolarites))
                @foreach($scolarites as $s)
                {
                    id: '{{$s->id}}',
                    cycle: '{{$s->id_cycle}}',
                    filiere: '{{$s->id_niveau}}',
                    classe: '{{isset($s->id_classe) ? $s->id_classe : ''}}',
                    nniveau: '{{isset($s->id_nniveau) ? $s->id_nniveau : ''}}',
                    annee: {!! json_encode(isset($s->annee_scolaire) ? $s->annee_scolaire : '') !!},
                    type: '{{$s->type_scolarite}}',
                    inscription: {!! json_encode($s->inscription) !!}
                },
                @endforeach
            @endif
        ];

        var editInscriptionClasses = [
            @if(isset($old_record))
                @foreach($classes as $c)
                {
                    id: '{{$c->id}}',
                    filiere: '{{$c->id_niveau}}',
                    label: {!! json_encode($c->getNom()) !!},
                    selected: {{ $inscription->id_classe==$c->id ? 'true' : 'false' }}
                },
                @endforeach
            @endif
        ];

        var editInscriptionNniveaux = [
            @if(isset($old_record))
                @foreach($nniveaux as $nn)
                {
                    id: '{{$nn->id}}',
                    cycle: '{{$nn->id_cycle}}',
                    label: {!! json_encode($nn->nom) !!},
                    selected: {{ $inscription->id_nniveau==$nn->id ? 'true' : 'false' }}
                },
                @endforeach
            @endif
        ];

        function refreshChosen(selectId) {
            var el = $(selectId);
            if(el.hasClass('chosen-select') || el.hasClass('autocomplete')) {
                el.trigger('chosen:updated');
            }
        }

        function rebuildSelectOptions(selectId, items, filterKey, filterValue, placeholder) {
            var select = $(selectId);
            if(!select.length) {
                return;
            }

            var selectedValue = select.val() || '';
            select.empty();
            select.append($('<option>', {value: '', text: placeholder}));

            $.each(items, function(index, item) {
                if(filterValue && String(item[filterKey]) === String(filterValue)) {
                    select.append($('<option>', {
                        value: item.id,
                        text: item.label
                    }));
                }
            });

            if(filterValue && select.find('option[value="' + selectedValue + '"]').length) {
                select.val(selectedValue);
            }
            else if(filterValue) {
                var defaultSelected = null;
                $.each(items, function(index, item) {
                    if(defaultSelected === null && item.selected && String(item[filterKey]) === String(filterValue)) {
                        defaultSelected = item.id;
                    }
                });
                select.val(defaultSelected || '');
            }
            else {
                select.val('');
            }

            refreshChosen(selectId);
        }

        function filterInscriptionFields() {
            rebuildSelectOptions('#nniveau', inscriptionNniveaux, 'cycle', $('#cycle').val(), choosePlaceholder);
            rebuildSelectOptions('#classe', inscriptionClasses, 'filiere', $('#filiere').val(), classPlaceholder);
        }

        function filterEditInscriptionFields() {
            rebuildSelectOptions('#edit_nniveau', editInscriptionNniveaux, 'cycle', $('#edit_cycle').val(), choosePlaceholder);
            rebuildSelectOptions('#edit_classe', editInscriptionClasses, 'filiere', $('#edit_filiere').val(), classPlaceholder);
        }

        function getScolarite() {

            var cycle=$('#cycle').val();
            var niveau= $('#filiere').val();
            var classe= $('#classe').val();
            var nniveau= $('#nniveau').val();
            var type_scolarite= $('#type_scolarite').val();
            var scolarite_style= $('#scolarite_style').val();
            var token='{{csrf_token()}}';

            if(cycle.length>0 && (niveau.length>0 || classe.length>0) && (scolarite_style === '0' || (classe.length>0 && nniveau.length>0)))
            {
                var localScolarite = resolveLocalScolarite(cycle, niveau, classe, nniveau, type_scolarite, scolarite_style, '{{session('year')}}');
                if(localScolarite) {
                    applyResolvedScolarite(localScolarite);
                    return;
                }

                $.ajax({
                    url:'{{route('get_scolarite')}}',
                    type:'POST',
                    dataType:'json',
                    data: {
                        cycle: cycle,
                        niveau: niveau,
                        classe: classe,
                        nniveau: nniveau,
                        annee_scolaire: '{{session('year')}}',
                        _token: token,
                        type_scolarite: type_scolarite,
                        scolarite_style: scolarite_style
                    },
                    success: function (obj) {
                        applyResolvedScolarite(obj);
                    },
                    error: function () {
                        applyResolvedScolarite({id: null, inscription: 0});
                    }
                })
            }
            else {
                $('#id_scolarite').val('');
                $('#show_montant').addClass('hidden');
                $('#scolarite_error').addClass('hidden');
                $('#montant-group').addClass('hidden');
                $('#montant').val('');

            }


        }

        function normalizeYear(value) {
            return String(value || '').replace(/\s+/g, '');
        }

        function isEmptyProfileValue(value) {
            return value === null || value === undefined || value === '' || String(value) === '0';
        }

        function filiereFromClasse(classeId, fallbackFiliere) {
            var filiere = fallbackFiliere;
            $.each(inscriptionClasses, function(index, item) {
                if(String(item.id) === String(classeId)) {
                    filiere = item.filiere;
                    return false;
                }
            });
            return filiere;
        }

        function resolveLocalScolarite(cycle, niveau, classe, nniveau, typeScolarite, scolariteStyle, anneeScolaire) {
            var effectiveNiveau = classe ? filiereFromClasse(classe, niveau) : niveau;
            var matches = [];

            $.each(scolariteProfiles, function(index, item) {
                if(String(item.cycle) !== String(cycle)) {
                    return true;
                }
                if(String(item.filiere) !== String(effectiveNiveau)) {
                    return true;
                }
                if(String(item.type) !== String(typeScolarite)) {
                    return true;
                }

                if(String(scolariteStyle) === '0') {
                    if(!isEmptyProfileValue(item.classe) || !isEmptyProfileValue(item.nniveau) || normalizeYear(item.annee) !== '') {
                        return true;
                    }
                    matches.push({score: 0, item: item});
                    return true;
                }

                var score = 0;
                if(!isEmptyProfileValue(item.classe)) {
                    if(String(item.classe) !== String(classe)) {
                        return true;
                    }
                    score += 4;
                }
                if(!isEmptyProfileValue(item.nniveau)) {
                    if(String(item.nniveau) !== String(nniveau)) {
                        return true;
                    }
                    score += 3;
                }
                if(normalizeYear(item.annee) !== '') {
                    if(normalizeYear(item.annee) === normalizeYear(anneeScolaire)) {
                        score += 2;
                    }
                    else {
                        score += 1;
                    }
                }
                matches.push({score: score, item: item});
            });

            if(matches.length === 0) {
                return null;
            }

            matches.sort(function(a, b) {
                if(b.score !== a.score) {
                    return b.score - a.score;
                }
                return parseInt(b.item.id, 10) - parseInt(a.item.id, 10);
            });

            var selected = matches[0].item;
            return {
                id: selected.id,
                inscription: selected.inscription,
                type_scolarite: selected.type
            };
        }

        function applyResolvedScolarite(obj) {
            $('#id_scolarite').val(obj.id || '');
            if(obj.inscription && parseFloat(obj.inscription) > 0) {
                $('#scolarite_error').addClass('hidden');
                $('#show_montant').removeClass('hidden');
                $('#show_montant_value').html(obj.inscription);
                $('#scolarite_details').html('Frais d inscription recuperes : ' + obj.inscription + ' @lang('inscription.devise')');
                $('#montant-group').addClass('hidden');
                var montant=$('#montant').addClass('disabled');
                montant.attr('readonly', true);
                montant.val(obj.inscription);
            } else {
                $('#show_montant').addClass('hidden');
                $('#scolarite_details').html('');
                $('#scolarite_error').removeClass('hidden');
                $('#montant-group').addClass('hidden');
                var input=$('#montant').addClass('disabled');
                input.attr('readonly', true);
                input.val('');
            }
        }

        function toggleOperationSource() {
            var source = $('#operation_source').val();
            if(source == 'banque') {
                $('#banque_group').removeClass('hidden');
                $('#caisse_group').addClass('hidden');
            } else {
                $('#banque_group').addClass('hidden');
                $('#caisse_group').removeClass('hidden');
            }
        }

        $(document).ready(function() {
            filterInscriptionFields();
            filterEditInscriptionFields();
            toggleOperationSource();
            getScolarite();
        });
    </script>
@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('inscriptions')}}">@lang('menu.gestion_inscriptions')</a></strong></li>
    @if(!isset($old_record))
        <li><strong><a href="{{url('inscriptions/find')}}">@lang('admin.search')</a></strong></li>
    @endif
    <li class="active"><strong>{{$titre}}</strong></li>
@endsection
