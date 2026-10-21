@extends('skeleton')
@section('sub-content')
    <div class="col-md-12">
        @if(isset($success))

            <div class="alert alert-success">
                {{  trans('admin.succes')  }}
            </div>

        @endif

        <div class="row" style="margin-bottom: 15px;">
            <div class="col-md-6">
                <a href="{{ url('/admin/scolarites?style=0') }}" class="btn btn-lg btn-block @if($style !== null && intval($style) === 0) btn-primary @else btn-default @endif">
                    {{ trans('admin.ancien') }} style de scolarite
                </a>
            </div>
            <div class="col-md-6">
                <a href="{{ url('/admin/scolarites?style=1') }}" class="btn btn-lg btn-block @if($style !== null && intval($style) === 1) btn-primary @else btn-default @endif">
                    {{ trans('admin.nouveau') }} style de scolarite
                </a>
            </div>
        </div>

        @if($style !== null)
            <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_cycle"
            ><span class="glyphicon glyphicon-plus"></span>{{  trans('admin.nouvelle_scolarite')  }} - @if(intval($style) === 0) {{ trans('admin.ancien') }} @else {{ trans('admin.nouveau') }} @endif</button>

        <table class="table table-bordered table-striped table-condensed table-inverse" style="margin-top: 15px;">
            <caption style="font-size: 22px">{{  trans('admin.titre_table_scolarite')  }} - @if(intval($style) === 0) {{ trans('admin.ancien') }} @else {{ trans('admin.nouveau') }} @endif</caption>

            <thead>
            <th>#</th>
            <th>Annee</th>
            <th>Profil</th>
            <th>Cycle / Filiere</th>
            <th>@lang('admin.specialites')</th>
            <th>@lang('admin.nniveau')</th>
            <th>@lang('admin.inscriptions')</th>
            <th>{{  trans('admin.tranche1')  }}</th>
            <th>{{  trans('admin.tranche2')  }}</th>
            <th>{{  trans('admin.tranche3')  }}</th>
            <th>{{  trans('admin.tranche4')  }}</th>
            <th>{{  trans('admin.tranche5')  }}</th>
             <th>{{  trans('admin.type_scolarite')  }}</th>
            <th>Total</th>
            </thead>
            <tbody>
            <?php $i=1; ?>
            @foreach($scolarite as $s)
                <tr>
                    <td>{{$i++}}</td>
                            <?php $cy=null; ?>
                            @foreach($cycles as $c)

                                @if($c->id==$s->id_cycle)
                                    <?php $cy=$c; break; ?>
                                @endif
                            @endforeach
                    <td>{{isset($s->annee_scolaire) && $s->annee_scolaire ? $s->annee_scolaire : 'Ancien parametrage'}}</td>
                    <td>{{isset($s->libelle) && $s->libelle ? $s->libelle : ($s->type_scolarite==0 ? trans('admin.ancien') : trans('admin.nouveau'))}}</td>
                    <td><strong >
                            @foreach($niveaux as $c)

                                    @if($c->id==$s->id_niveau)
                                    <a  data-toggle="modal" data-backdrop="false" href="#edit-classe" onclick='editer_scolarite({{$s->id}},{{$s->id_niveau}},{{$s->id_cycle}},{{isset($s->id_classe) && $s->id_classe ? $s->id_classe : 0}},{{isset($s->id_nniveau) && $s->id_nniveau ? $s->id_nniveau : 0}},{!! json_encode(isset($s->annee_scolaire) ? $s->annee_scolaire : '') !!},{!! json_encode(isset($s->libelle) ? $s->libelle : '') !!},{{$s->inscription}},{{$s->tranche1}},{{$s->tranche2}},{{$s->tranche3}},{{$s->tranche4}},{{$s->tranche5}},{{$s->type_scolarite}})'><span class="glyphicon glyphicon-edit"  ></span> {{$cy?$cy->nom.' - ':''}} {{$c->getNom()}}</a></li>
                                        <?php break; ?>
                                    @endif
                            @endforeach
                        </strong></td>
                    <td>
                        @if(isset($s->id_classe) && $s->id_classe)
                            @foreach($classes as $cl)
                                @if($cl->id==$s->id_classe) {{$cl->getNom()}} <?php break; ?> @endif
                            @endforeach
                        @else
                            Toutes
                        @endif
                    </td>
                    <td>
                        @if(isset($s->id_nniveau) && $s->id_nniveau)
                            @foreach($nniveaux as $nn)
                                @if($nn->id==$s->id_nniveau) {{$nn->nom}} <?php break; ?> @endif
                            @endforeach
                        @else
                            Tous
                        @endif
                    </td>
                    <td>{{$s->inscription}}</td>
                    <td>{{$s->tranche1}}</td>
                    <td>{{$s->tranche2}}</td>
                    <td>{{$s->tranche3}}</td>
                    <td>{{$s->tranche4}}</td>
                    <td>{{$s->tranche5}}</td>
                      @if($s->type_scolarite==0)
                        <td>{{ trans('admin.ancien') }}</td>
                    @else
                        <td>{{ trans('admin.nouveau') }}</td>
                    @endif
                    <td>{{$s->total}}</td>
                    <td>

                                <a  href="{{ URL::to('/admin/scolarite/destroy/').'/'.$s->id }}"   class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-trash"></span> @lang('admin.del')</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

            <div class="col-md-12">
                <div class="alert alert-info">
                    <strong>
                        Total does not include registration amount
                    </strong>
                </div>
            </div>
        @else
            <div class="alert alert-info">
                Choisir un style de scolarite pour afficher ses profils et ajouter une nouvelle scolarite.
            </div>
        @endif

        @if($style !== null)
        <div class="modal fade" id="add_cycle">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">x</button>
                        <h4 class="modal-title">{{  trans('admin.remplir_champs')  }}</h4>
                    </div>
                    <div
                            class="modal-body">



                        <form accept-charset="UTF-8" role="form" method="POST" id="add_level_form" action="{{ url('/admin/scolarite/store/') }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="scolarite_style" value="{{ intval($style) }}">
                            <fieldset>
                                <div class="form-group">
                                    <label for="cycle">Cycle : </label>
                                    <select id="cycle" name="cycle" form="add_level_form" class="form-control" onchange="filterScolariteFields()">
                                        @foreach($cycles as $c)
                                            <option value="{{  $c->id  }}" @if($c->id == old('cycle')) selected @endif>{{  $c->nom  }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @if(intval($style) === 1)
                                    <div class="form-group">
                                        <label for="annee_scolaire">{{  trans('admin.year')  }} : <span class="required">*</span></label>
                                        <input class="form-control" id="annee_scolaire" name="annee_scolaire" type="text" value="{{ old('annee_scolaire', session('year')) }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="libelle">Profil / Cas de scolarite : </label>
                                        <input class="form-control" id="libelle" name="libelle" type="text" value="{{ old('libelle') }}" placeholder="Ex: mutation externe, reduction speciale">
                                    </div>
                                @endif
                                <div class="form-group">
                                    <label for="classe">{{trans('admin.level')}} : <span class="required">*</span></label>
                                    <select id="classe" name="classe" form="add_level_form" class="form-control" onchange="filterScolariteFields()">
                                        @foreach($niveaux as $c)
                                            <option value="{{  $c->id  }}" @if($c->id == old('classe')) selected @endif>{{  $c->getNom()  }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @if(intval($style) === 1)
                                    <div class="form-group">
                                        <label for="specialite">@lang('admin.specialites') : </label>
                                        <select id="specialite" name="specialite" form="add_level_form" class="form-control">
                                            <option value="">Toutes les specialites de cette filiere</option>
                                            @foreach($classes as $c)
                                                <option value="{{  $c->id  }}" data-filiere="{{$c->id_niveau}}" @if($c->id == old('specialite')) selected @endif>{{  $c->getNom()  }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="nniveau">@lang('admin.nniveau') : </label>
                                        <select id="nniveau" name="nniveau" form="add_level_form" class="form-control">
                                            <option value="">Tous les niveaux du cycle</option>
                                            @foreach($nniveaux as $nn)
                                                <option value="{{  $nn->id  }}" data-cycle="{{$nn->id_cycle}}" @if($nn->id == old('nniveau')) selected @endif>{{  $nn->nom  }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label for="inscription">{{  trans('admin.inscriptions')  }} : </label>
                                        <input class="form-control" id="inscription" name="inscription" type="text"  value="{{ old('inscription') }}">

                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="tranche1">{{  trans('admin.tranche1')  }} : <span class="required">*</span></label>
                                        <input class="form-control" id="tranche1" name="tranche1" type="text"  value="{{ old('tranche1') }}"  autofocus>

                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="tranche2">{{  trans('admin.tranche2')  }} : <span class="required">*</span></label>
                                        <input class="form-control" id="tranche2" name="tranche2" type="text"  value="{{ old('tranche2') }}">

                                    </div>
                                </div>
                               <div class="row">
                                   <div class="form-group col-md-6">
                                       <label for="tranche3">{{  trans('admin.tranche3')  }} : <span class="required">*</span></label>
                                       <input class="form-control" id="tranche3" name="tranche3" type="text"  value="{{ old('tranche3') }}">

                                   </div>
                                   <div class="form-group col-md-6">
                                       <label for="tranche4">{{  trans('admin.tranche4')  }} :</label>
                                       <input class="form-control" id="tranche4" name="tranche4" type="text"  value="{{ old('tranche4') }}">


                                   </div>
                               </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="tranche5">{{  trans('admin.tranche5')  }} : </label>
                                        <input class="form-control" id="tranche5" name="tranche5" type="text"  value="{{ old('tranche5') }}">

                                    </div>

                                </div>
                                  <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="type_scolarite">{{  trans('admin.type_scolarite')  }} : </label>
                                            <select form="add_level_form" id="type_scolarite" name="type_scolarite" class="form-control">
                                                <option value="0" @if(strcmp(old('type_scolarite'),'0')==0) selected @endif>{{ trans('admin.ancien') }}</option>
                                                <option value="1" @if(strcmp(old('type_scolarite'),'1')==0) selected @endif>{{ trans('admin.nouveau') }}</option>
                                            </select>

                                        </div>
                                    </div>
                                </div>


                                <input class="btn  btn-success " type="submit" value="{{  trans('admin.confirmer')  }}">


                            </fieldset>
                        </form>


                    </div>





                </div>


            </div>
        </div>
        @endif

        <div class="modal fade" id="edit-classe">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">x</button>
                        <h4 class="modal-title">{{  trans('admin.remplir_champs')  }}</h4>
                    </div>
                    <div
                            class="modal-body">



                        <form accept-charset="UTF-8" role="form" method="POST" id="edit_scolarite_form" action="{{ url('/admin/scolarite/update/') }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                            <input type="hidden" name="scolarite_style" value="{{ $style !== null ? intval($style) : 1 }}"/>
                            <input type="hidden" name="id" id="id_scolarite" value=""/>
                            <fieldset>
                                <div class="form-group">
                                    <label for="cycle">Cycle : </label>
                                    <select id="edit_cycle" name="cycle" form="edit_scolarite_form" class="form-control" onchange="filterEditScolariteFields()">
                                        @foreach($cycles as $c)
                                            <option value="{{  $c->id  }}" @if($c->id == old('cycle')) selected @endif>{{  $c->nom  }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @if($style !== null && intval($style) === 1)
                                    <div class="form-group">
                                        <label for="edit_annee_scolaire">{{  trans('admin.year')  }} : <span class="required">*</span></label>
                                        <input class="form-control" id="edit_annee_scolaire" name="annee_scolaire" type="text" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_libelle">Profil / Cas de scolarite : </label>
                                        <input class="form-control" id="edit_libelle" name="libelle" type="text">
                                    </div>
                                @endif
                                <div class="form-group">
                                    <label for="edit_classes">{{trans('admin.level')}} : <span class="required">*</span></label>
                                    <select id="edit_classes" name="classes" form="edit_scolarite_form" class="form-control" onchange="filterEditScolariteFields()">
                                        @foreach($niveaux as $c)
                                            <option value="{{  $c->id  }}" @if($c->id == old('classes')) selected @endif>{{  ($c->getNom())  }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @if($style !== null && intval($style) === 1)
                                    <div class="form-group">
                                        <label for="edit_specialite">@lang('admin.specialites') : </label>
                                        <select id="edit_specialite" name="specialite" form="edit_scolarite_form" class="form-control">
                                            <option value="">Toutes les specialites de cette filiere</option>
                                            @foreach($classes as $c)
                                                <option value="{{  $c->id  }}" data-filiere="{{$c->id_niveau}}">{{  ($c->getNom())  }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit_nniveau">@lang('admin.nniveau') : </label>
                                        <select id="edit_nniveau" name="nniveau" form="edit_scolarite_form" class="form-control">
                                            <option value="">Tous les niveaux du cycle</option>
                                            @foreach($nniveaux as $nn)
                                                <option value="{{  $nn->id  }}" data-cycle="{{$nn->id_cycle}}">{{  $nn->nom  }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label for="edit-inscription">{{  trans('admin.inscriptions')  }} : </label>
                                        <input class="form-control" id="edit-inscription" name="inscription" type="text">

                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="edit_tranche1">{{  trans('admin.tranche1')  }} : <span class="required">*</span></label>
                                        <input class="form-control" id="edit_tranche1" name="tranche1" type="text"  value="{{ old('tranche1') }}"  autofocus>

                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="edit_tranche2">{{  trans('admin.tranche2')  }} : <span class="required">*</span></label>
                                        <input class="form-control" id="edit_tranche2" name="tranche2" type="text"  value="{{ old('tranche2') }}">

                                    </div>
                                </div>
                               <div class="row">
                                   <div class="form-group col-md-6">
                                       <label for="edit_tranche3">{{  trans('admin.tranche3')  }} : <span class="required">*</span></label>
                                       <input class="form-control" id="edit_tranche3" name="tranche3" type="text"  value="{{ old('tranche3') }}">

                                   </div>
                                   <div class="form-group col-md-6">
                                       <label for="edit_tranche4">{{  trans('admin.tranche4')  }} :</label>
                                       <input class="form-control" id="edit_tranche4" name="tranche4" type="text"  value="{{ old('tranche4') }}">

                                   </div>
                               </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="edit_tranche5">{{  trans('admin.tranche5')  }} : </label>
                                        <input class="form-control" id="edit_tranche5" name="tranche5" type="text"  value="{{ old('tranche5') }}">

                                    </div>

                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="edit_type_scolarite">{{  trans('admin.type_scolarite')  }} : </label>
                                            <select form="edit_scolarite_form" id="edit_type_scolarite" name="type_scolarite" class="form-control">
                                                <option value="0">{{ trans('admin.ancien') }}</option>
                                                <option value="1">{{ trans('admin.nouveau') }}</option>
                                            </select>

                                        </div>
                                    </div>
                                </div>


                                <input class="btn  btn-success " type="submit" value="{{  trans('admin.confirmer')  }}">


                            </fieldset>
                        </form>


                    </div>





                </div>


            </div>
        </div>

        <div class="paginate" style="text-align: center;"> <?php echo(str_replace('/?', '?', $scolarite->render()) ); ?></div>
    </div>

    <script>
        var specialitePlaceholder = {!! json_encode('Toutes les specialites de cette filiere') !!};
        var nniveauPlaceholder = {!! json_encode('Tous les niveaux du cycle') !!};

        var scolariteSpecialites = [
            @foreach($classes as $c)
            {
                id: '{{$c->id}}',
                filiere: '{{$c->id_niveau}}',
                label: {!! json_encode($c->getNom()) !!},
                selected: {{ old('specialite')==$c->id ? 'true' : 'false' }}
            },
            @endforeach
        ];

        var scolariteNniveaux = [
            @foreach($nniveaux as $nn)
            {
                id: '{{$nn->id}}',
                cycle: '{{$nn->id_cycle}}',
                label: {!! json_encode($nn->nom) !!},
                selected: {{ old('nniveau')==$nn->id ? 'true' : 'false' }}
            },
            @endforeach
        ];

        function rebuildScolariteSelect(selectId, items, filterKey, filterValue, placeholder, selectedValue) {
            var select = $(selectId);
            if(!select.length) {
                return;
            }

            var currentValue = selectedValue !== undefined ? String(selectedValue || '') : String(select.val() || '');
            select.empty();
            select.append($('<option>', {value: '', text: placeholder}));

            $.each(items, function(index, item) {
                if(!filterValue || String(item[filterKey]) === String(filterValue)) {
                    select.append($('<option>', {
                        value: item.id,
                        text: item.label
                    }));
                }
            });

            if(currentValue && select.find('option[value="' + currentValue + '"]').length) {
                select.val(currentValue);
            }
            else if(selectedValue === undefined) {
                var defaultSelected = null;
                $.each(items, function(index, item) {
                    if(defaultSelected === null && item.selected && (!filterValue || String(item[filterKey]) === String(filterValue))) {
                        defaultSelected = item.id;
                    }
                });
                select.val(defaultSelected || '');
            }
            else {
                select.val('');
            }
        }

        function filterScolariteFields() {
            rebuildScolariteSelect('#nniveau', scolariteNniveaux, 'cycle', $('#cycle').val(), nniveauPlaceholder);
            rebuildScolariteSelect('#specialite', scolariteSpecialites, 'filiere', $('#classe').val(), specialitePlaceholder);
        }

        function filterEditScolariteFields(selectedSpecialite, selectedNniveau) {
            rebuildScolariteSelect('#edit_nniveau', scolariteNniveaux, 'cycle', $('#edit_cycle').val(), nniveauPlaceholder, selectedNniveau);
            rebuildScolariteSelect('#edit_specialite', scolariteSpecialites, 'filiere', $('#edit_classes').val(), specialitePlaceholder, selectedSpecialite);
        }

        function editer_scolarite(id,id_classe,id_cycle,id_specialite,id_nniveau,annee_scolaire,libelle,inscription,tranche1,tranche2,tranche3,tranche4,tranche5,type_scolarite)
        {
            document.getElementById("id_scolarite").value = id;
            document.getElementById("edit_classes").value = id_classe;
            document.getElementById("edit_cycle").value = id_cycle;
            filterEditScolariteFields(id_specialite, id_nniveau);
            setScolariteValue("edit_annee_scolaire", annee_scolaire);
            setScolariteValue("edit_libelle", libelle);
            document.getElementById("edit-inscription").value = inscription;
            document.getElementById("edit_tranche1").value = tranche1;
            document.getElementById("edit_tranche2").value = tranche2;
            document.getElementById("edit_tranche3").value = tranche3;
            document.getElementById("edit_tranche4").value = tranche4;
            document.getElementById("edit_tranche5").value = tranche5;
            document.getElementById("edit_type_scolarite").value = type_scolarite;


        }

        function setScolariteValue(id, value) {
            var el = document.getElementById(id);
            if(el) {
                el.value = value;
            }
        }

        $(document).ready(function() {
            filterScolariteFields();
            filterEditScolariteFields();
        });
    </script>
    <!--    -->
@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('admin')}}">@lang('admin.dashboard')</a></strong></li>
    <li class="active"><strong>{{$titre}}</strong></li>
@endsection
