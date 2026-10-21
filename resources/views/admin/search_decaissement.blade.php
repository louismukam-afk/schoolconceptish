@extends('skeleton')
@section('sub-content')
@include('perso.functions')

    @if(isset($success))

        <div class="alert alert-success">
            {{  trans('admin.succes')  }}
        </div>


    @endif

    @if(isset($custom_error))

        <div class="alert alert-danger">
            {{  $custom_error  }}
        </div>

    @endif


    <div class="col-md-8">
        <form class="form-horizontal" id="search_form" method="get" action="{{URL::to('/admin/retrait/')}}">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="search">{{  trans('inscription.recherche')  }} : </label>
                    <div class="input-group">
                        <input type="search" name="search" placeholder="{{trans('inscription.nom')}}" class="form-control" autofocus autocomplete="true">
                        <div class="input-group-btn">
                            <button type="submit" class="btn btn-primary" ><span class="fa fa-search"> </span>{{trans('inscription.search')}}</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>

    <div class="col-md-8">
        <a data-toggle="modal" data-backdrop="false" href="#add_retrait" class="btn btn-warning"><span class="fa fa-money"></span> {{trans('admin.nouveau_retrait')}}</a>
    </div>

    <div class="col-md-8">
            <table class="table table-bordered table-striped table-condensed table-inverse" style="margin-top: 15px;">
                <thead>
                <th>#</th>
                <th>{{  trans('admin.nom')  }}</th>
                <th>{{  trans('inscription.motif')  }}</th>
                <th>{{  trans('inscription.montant')  }}</th>
                <th>Caisse</th>
                <th>{{  trans('admin.ligne_budget')  }}</th>
                <th>{{  trans('admin.categorie_budget')  }}</th>
                <th>Date</th>

                </thead>
                <tbody>
                <?php $i=1; ?>
                @foreach($decaissement as $d)
                    <?php $nomDepense = $d->nom_beneficiaire ? $d->nom_beneficiaire : $d->nom_personnel; ?>
                    <tr>
                        <td>{{$i++}}</td>
                        <td><a  data-toggle="modal" data-backdrop="false" href="#edit-retrait" onclick="editer_retrait({{$d->id}},{{$d->id_personnel}},'{{sanitize($d->nom_beneficiaire)}}','{{sanitize($d->motif)}}',{{$d->id_ligne_budgetaire}},{{$d->id_categorie_budgetaire}},{{$d->id_caisse ? $d->id_caisse : 0}})"><span class="glyphicon glyphicon-edit"  ></span> {{$nomDepense}}</a></li></td>

                        <td>{{$d->motif}}</td>
                        <td><a href="#edit_payement" data-toggle="modal" onclick="editer_payement({{$d->id}},{{$d->montant}})"><strong><span class="glyphicon glyphicon-edit"></span> {{$d->montant}}</strong></a> </td>
                        <td>{{$d->caisse ? $d->caisse->nom : '' }}</td>
                        <td>{{$d->ligne_budgetaire ? $d->ligne_budgetaire->libelle_ligne : '' }}</td>
                        <td>{{$d->categorie_budgetaire ? $d->categorie_budgetaire->libelle : '' }}</td>
                        <td>{{$d->date}}</td>

                    </tr>
                @endforeach
                </tbody>
            </table>

            <div class="paginate" style="text-align: center;"> <?php echo(str_replace('/?', '?', $decaissement->render()) ); ?></div>

    </div>

    <div class="modal fade" id="add_retrait">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">x</button>
                    <h4 class="modal-title">{{  trans('admin.remplir_champs')  }}</h4>
                </div>
                <div
                        class="modal-body">



                    <form accept-charset="UTF-8" role="form" method="POST" id="add_retrait_form" action="{{ url('/admin/retrait/store/') }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <fieldset>
                            <div class="form-group">
                                <label >{{  trans('admin.choix_personnel')  }} : </label>
                                <select name="personnel" form="add_retrait_form" class="form-control autocomplete" autocomplete="true" >
                                    <option value="">Personne externe / autre</option>
                                    @foreach($personnel as $p)
                                        <option value="{{  $p->id  }}" @if($p->id == old('personnel')) selected @endif>{{  $p->nom  }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label >Nom de la personne externe : </label>
                                <input class="form-control" name="nom_beneficiaire" type="text" value="{{ old('nom_beneficiaire') }}" placeholder="A remplir si ce n'est pas un personnel">
                            </div>

                            <div class="form-group">
                                <label >Caisse de sortie : <span class="required">*</span></label>
                                <select name="caisse" form="add_retrait_form" class="form-control autocomplete" required>
                                    <option value="">Selectionner une caisse</option>
                                    @foreach($caisses as $caisse)
                                        <option value="{{ $caisse->id }}" @if($caisse->id == old('caisse')) selected @endif>{{ $caisse->nom }} - Solde: {{ number_format($caisse->solde, 0, '.', ' ') }} @lang('inscription.devise')</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label >{{  trans('admin.ligne_budget')  }} : </label>
                                <select name="ligne_budgetaire" id="ligne_budgetaire" form="add_retrait_form" class="form-control autocomplete" autocomplete="true" onchange="onBudgetLineChange('ligne_budgetaire', 'categorie_budgetaire')" required>
                                <option value="">{{  trans('admin.select_item') }}</option>

                                    @foreach($ligne as $li)
                                        <option value="{{  $li->id  }}" @if($li->id == old('ligne_budgetaire')) selected @endif >{{  $li->libelle_ligne  }}</option>
                                    @endforeach

                                </select>
                            </div>

                            <div class="form-group">
                                <label >{{  trans('admin.categorie_budget')  }} : </label>
                                <select name="categorie_budgetaire" id="categorie_budgetaire" form="add_retrait_form" class="form-control autocomplete" autocomplete="true" required>
                                <option value="">{{  trans('admin.select_item') }}</option>

                                </select>
                            </div>

                                    <div class="form-group" >
                                        <label >{{  trans('inscription.montant')  }} : <span class="required">*</span></label>
                                        <input class="form-control" name="montant" type="number"  value="{{ old('montant') }}" min="1" required>

                                    </div>
                                    <div class="form-group">
                                        <label >{{  trans('inscription.motif')  }} : <span class="required">*</span></label>
                                        <textarea name="motif" form="add_retrait_form" class="form-control" required>{{old('motif')}}</textarea>
                                    </div>

                                    <input class="btn  btn-success " type="submit" value="{{  trans('admin.confirmer')  }}">


                                </fieldset>
                    </form>


                        </div>





                    </div>


                </div>
            </div>

            <div class="modal fade" id="edit-retrait">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">x</button>
                    <h4 class="modal-title">{{  trans('admin.remplir_champs')  }}</h4>
                </div>
                <div
                        class="modal-body">



                    <form accept-charset="UTF-8" role="form" method="POST" id="edit_retrait_form" action="{{ url('/admin/retrait/update/') }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="id" id="id_retrait" required>

                        <fieldset>
                            <div class="form-group">
                                <label >{{  trans('admin.choix_personnel')  }} : </label>
                                <select name="personnel" form="edit_retrait_form" id="edit_personnel" class="form-control autocomplete" autocomplete="true">
                                    <option value="">Personne externe / autre</option>
                                    @foreach($personnel as $p)
                                        <option value="{{  $p->id  }}" >{{  $p->nom  }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label >Nom de la personne externe : </label>
                                <input class="form-control" name="nom_beneficiaire" id="edit_nom_beneficiaire" type="text" placeholder="A remplir si ce n'est pas un personnel">
                            </div>

                            <div class="form-group">
                                <label >Caisse de sortie : <span class="required">*</span></label>
                                <select name="caisse" id="edit_caisse" form="edit_retrait_form" class="form-control autocomplete" required>
                                    <option value="">Selectionner une caisse</option>
                                    @foreach($caisses as $caisse)
                                        <option value="{{ $caisse->id }}">{{ $caisse->nom }} - Solde: {{ number_format($caisse->solde, 0, '.', ' ') }} @lang('inscription.devise')</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label >{{  trans('admin.ligne_budget')  }} : </label>
                                <select name="ligne_budgetaire" id="edit_ligne_budgetaire" form="edit_retrait_form" class="form-control autocomplete" autocomplete="true" onchange="onBudgetLineChange('edit_ligne_budgetaire', 'edit_categorie_budgetaire')" required>
                                <option value="">{{  trans('admin.select_item') }}</option>

                                    @foreach($ligne as $li)
                                        <option value="{{  $li->id  }}" >{{  $li->libelle_ligne  }}</option>
                                    @endforeach

                                </select>
                            </div>

                            <div class="form-group">
                                <label >{{  trans('admin.categorie_budget')  }} : </label>
                                <select name="categorie_budgetaire" id="edit_categorie_budgetaire" form="edit_retrait_form" class="form-control autocomplete" autocomplete="true" required>
                                <option value="">{{  trans('admin.select_item') }}</option>

                                </select>
                            </div>

                                    <div class="form-group">
                                        <label >{{  trans('inscription.motif')  }} : <span class="required">*</span></label>
                                        <textarea name="motif" form="edit_retrait_form" id="edit_motif" class="form-control" required></textarea>
                                    </div>

                                    <input class="btn  btn-success " type="submit" value="{{  trans('admin.confirmer')  }}">


                                </fieldset>
                    </form>


                        </div>





                    </div>


                </div>
            </div>

            <div class="modal fade" id="edit_payement">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">x</button>
                            <h4 class="modal-title">{{  trans('admin.remplir_champs')  }}</h4>
                        </div>
                        <div class="modal-body">
                            <form accept-charset="UTF-8" role="form" method="POST" id="edit_payement_form" action="{{ route('update_retrait_amount') }}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="id" id="edit_id" value=""/>
                                <fieldset>
                                    <div class="form-group">
                                        <label >{{  trans('inscription.montant')  }} : <span class="required">*</span></label>
                                        <input class="form-control" id="edit_montant" name="montant" type="number"  value="{{ old('montant') }}" required min="1">
                                    </div>
                                    <input class="btn  btn-success " type="submit" value="{{  trans('admin.confirmer')  }}">
                                </fieldset>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                var selectItemLabel = '{{ trans('admin.select_item') }}';

                function editer_payement(id,montant)
                {

                    document.getElementById("edit_id").value = id;
                    document.getElementById("edit_montant").value = montant;

                }

                function editer_retrait(id,id_personnel,nom_beneficiaire,motif,id_ligne_budgetaire,id_categorie_budgetaire,id_caisse)
                    {
                        document.getElementById("id_retrait").value = id;
                        document.getElementById("edit_personnel").value = id_personnel ? id_personnel : '';
                        document.getElementById("edit_nom_beneficiaire").value = nom_beneficiaire;
                        document.getElementById("edit_motif").value = motif;
                        document.getElementById("edit_caisse").value = id_caisse ? id_caisse : '';
                        document.getElementById("edit_ligne_budgetaire").value = id_ligne_budgetaire;
                        if (window.jQuery) {
                            $('#edit_caisse').trigger('chosen:updated');
                            $('#edit_personnel').trigger('chosen:updated');
                            $('#edit_ligne_budgetaire').trigger('chosen:updated');
                        }
                        onBudgetLineChange('edit_ligne_budgetaire', 'edit_categorie_budgetaire', id_categorie_budgetaire)
                    }

                function onBudgetLineChange(elementId, containerId, selectedCategoryId) {
                    var elId = elementId ? elementId : 'edit_ligne_budgetaire';
                    var lineId = document.getElementById(elId).value;
                    fillCategoriesOptions(lineId, containerId, selectedCategoryId, true);
                }

                function fillCategoriesOptions(lineId, containerId, selectedCategoryId, useAjaxFallback) {
                    var contId = containerId ? containerId : 'edit_categories_options';
                    var selectElement = document.getElementById(contId);
                    var templates = document.getElementById('all_budget_categories').getElementsByTagName('option');
                    var optionsHtml = '<option value="">' + selectItemLabel + '</option>';
                    var found = 0;

                    for (var i = 0; i < templates.length; i++) {
                        if (templates[i].getAttribute('data-line') == lineId) {
                            var selected = selectedCategoryId && parseInt(selectedCategoryId) === parseInt(templates[i].value) ? ' selected' : '';
                            optionsHtml += '<option value="' + templates[i].value + '"' + selected + '>' + templates[i].innerHTML + '</option>';
                            found++;
                        }
                    }

                    if (!selectElement) {
                        return;
                    }

                    if (lineId && found === 0 && useAjaxFallback && window.jQuery) {
                        $.ajax({
                            url: '{{url("/comptabilite/ligne/get-data")}}/' + lineId,
                            method: 'GET',
                            success: function (data) {
                                fillCategoriesFromAjax(data, contId, selectedCategoryId);
                            },
                            error: function() {
                                fillCategoriesFromAjax([], contId, selectedCategoryId);
                            }
                        });
                        return;
                    }

                    if (lineId && found === 0) {
                        optionsHtml += '<option value="" disabled>Aucune categorie budgetaire pour cette ligne</option>';
                    }
                    selectElement.innerHTML = optionsHtml;
                    if (selectedCategoryId) {
                        selectElement.value = selectedCategoryId;
                    }
                    if (window.jQuery) {
                        $(selectElement).trigger('chosen:updated').trigger('change');
                    }
                }

                function fillCategoriesFromAjax(data, contId, selectedCategoryId) {
                    var selectElement = document.getElementById(contId);
                    var optionsHtml = '<option value="">' + selectItemLabel + '</option>';
                    if (!selectElement) {
                        return;
                    }

                    for (var i = 0; i < data.length; i++) {
                        var selected = selectedCategoryId && parseInt(selectedCategoryId) === parseInt(data[i].categoryId) ? ' selected' : '';
                        optionsHtml += '<option value="' + data[i].categoryId + '"' + selected + '>' + data[i].categoryName + '</option>';
                    }
                    if (data.length === 0) {
                        optionsHtml += '<option value="" disabled>Aucune categorie budgetaire pour cette ligne</option>';
                    }

                    selectElement.innerHTML = optionsHtml;
                    if (selectedCategoryId) {
                        selectElement.value = selectedCategoryId;
                    }
                    $(selectElement).trigger('chosen:updated').trigger('change');
                }

                if (window.jQuery) {
                    $(function () {
                        $('#ligne_budgetaire').change(function () {
                            onBudgetLineChange('ligne_budgetaire', 'categorie_budgetaire');
                        });
                        $('#edit_ligne_budgetaire').change(function () {
                            onBudgetLineChange('edit_ligne_budgetaire', 'edit_categorie_budgetaire');
                        });
                        if ($('#ligne_budgetaire').val()) {
                            onBudgetLineChange('ligne_budgetaire', 'categorie_budgetaire', '{{ old('categorie_budgetaire') }}');
                        }
                    });
                }
                
                
            </script>
            <select id="all_budget_categories" style="display:none">
                @foreach($ligne as $li)
                    @foreach($li->donneesLigneBudgetaire as $entry)
                        @if($entry->categorieBudgetaire)
                            <option data-line="{{ $li->id }}" value="{{ $entry->categorieBudgetaire->id }}">{{ $entry->categorieBudgetaire->libelle }}</option>
                        @endif
                    @endforeach
                @endforeach
            </select>
        @endsection

        @section('breadcrumb')
            <li><strong><a href="{{url('comptabilite')}}">@lang('admin.comptabilite')</a></strong></li>

            <li class="active"><strong>{{$titre}}</strong></li>
        @endsection
