@extends('skeleton')
@section('sub-content')
    <div class="col-md-10">
        @include('perso.functions')
        @if(isset($success))

            <div class="alert alert-success">
                {{  trans('admin.succes')  }}
            </div>

        @endif


        <table class="table table-striped table-condensed table-inverse" style="margin-top: 15px;">
            <thead>
            <caption style="font-size: 22px"></caption>
            <th>#</th>
            <th>{{  trans('admin.libelle')  }}</th>
            <th>{{  trans('admin.date_debut')  }}</th>
            <th>{{  trans('admin.date_fin')  }}</th>
            <th>{{  trans('admin.total')  }}</th>
            <th></th>
            </thead>
            <tbody>
            <?php $i=1; ?>
            @foreach($ligne_budget as $c)
                <tr>
                    <td>{{$i++}}</td>
                    <td><strong><a href="{{URL::to('/comptabilite/donnee/ligne/'.$c->id)}}"> {{ $c->libelle_ligne }} </a></strong></td>
                    <td>{{$c->date_debut}}</td>
                    <td>{{$c->date_fin}}</td>
                    <td>{{number_format($c->total, 0, '.', ' ')}} @lang('inscription.devise')</td>
                    <td>
                        <div class="btn-group">
                            <a href="#" class=" btn btn-primary dropdown-toggle" data-toggle="dropdown">Actions <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li><a style="color: white" title="Modifier les informations" class="btn-primary"  data-toggle="modal" data-backdrop="false" href="#edit_classe" onclick="editer_classe({{$c->id}},'{{sanitize($c->libelle_ligne)}}','{{$c->date_debut}}','{{$c->date_fin}}', {{$c->total}})"><span class="glyphicon glyphicon-pencil"  ></span> {{  trans('admin.edit')  }}</a></li>
                                <li><a style="color: white" href="{{ URL::to('/comptabilite/ligne/report/').'/'.$c->id }}"   class="btn-success"><span ></span> {{  trans('admin.report')  }}</a></li>
                                <li><a style="color: white" href="{{ URL::to('/comptabilite/print-ligne/report/').'/'.$c->id }}"   class="btn-success"><span ></span> Listes des éléments de la ligne budgétaire</a></li>
                                <li><a style="color: white" href="{{ URL::to('/comptabilite/ligne/destroy/').'/'.$c->id }}"   class="btn-danger"><span class="glyphicon glyphicon-trash"></span> {{  trans('admin.del')  }}</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_cycle"
        ><span class="glyphicon glyphicon-plus"></span>{{  trans('admin.ligne_new')  }}</button>
        <div class="modal fade" id="add_cycle">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">x</button>
                        <h4 class="modal-title">{{  trans('admin.remplir_champs')  }}</h4>
                    </div>
                    <div
                            class="modal-body">



                        <form accept-charset="UTF-8" role="form" method="POST" id="add_level_form" action="{{ url('/comptabilite/ligne/store/') }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <fieldset>

                                <div class="form-group">
                                    <label>{{trans('admin.libelle')}} : <span class="required">*</span></label>
                                    <input class="form-control"  name="libelle_ligne" type="text"  value="{{ old('libelle_ligne') }}"  autofocus>

                                </div>
                                <div class="form-group">
                                    <label for="date_debut">{{  trans('admin.date_debut')  }} : </label>
                                    <input readonly placeholder="yyyy/mm/dd" class="form-control datepicker" id="date_debut" name="date_debut" type="text"  value="{{ old('date_debut') }}" required>

                                </div>
                                <div class="form-group ">
                                    <label for="date_fin">{{  trans('admin.date_fin')  }} : </label>
                                    <input readonly placeholder="yyyy/mm/dd" class="form-control datepicker" id="date_fin" name="date_fin" type="text"  value="{{ old('date_fin') }}" required >

                                </div>
                                <div class="form-group">
                                    <label for="total">{{  trans('admin.montant')  }} :</label>
                                    <input class="form-control" id="total" name="total" type="numeric"  value="{{ old('total') }}"  >

                                </div>

                                <input class="btn  btn-success " type="submit" value="{{  trans('admin.confirmer')  }}">


                            </fieldset>
                        </form>


                    </div>





                </div>


            </div>
        </div>

        <div class="modal fade" id="edit_classe">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">x</button>
                        <h4 class="modal-title">{{  trans('admin.remplir_champs')  }}</h4>
                    </div>
                    <div
                            class="modal-body">



                        <form accept-charset="UTF-8" role="form" method="POST" id="edit_classes_form" action="{{ url('/comptabilite/ligne/update/') }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                            <input type="hidden" name="id" id="edit_id_classe" value=""/>
                            <fieldset>

                                <div class="form-group">
                                    <label>{{trans('admin.libelle')}} : <span class="required">*</span></label>
                                    <input class="form-control"  id="edit-libelle_ligne" name="libelle_ligne" type="text"  value="{{ old('libelle_ligne') }}"  autofocus>

                                </div>
                                <div class="form-group">
                                    <label for="date_debut">{{  trans('admin.date_debut')  }} : </label>
                                    <input readonly placeholder="yyyy/mm/dd" class="form-control datepicker" id="edit-date_debut" name="date_debut" type="text"  value="{{ old('date_debut') }}" required>

                                </div>
                                <div class="form-group ">
                                    <label for="date_fin">{{  trans('admin.date_fin')  }} : </label>
                                    <input readonly placeholder="yyyy/mm/dd" class="form-control datepicker" id="edit-date_fin" name="date_fin" type="text"  value="{{ old('date_fin') }}" required >

                                </div>
                                <div class="form-group">
                                    <label for="total">{{  trans('admin.montant')  }} :</label>
                                    <input class="form-control" id="edit-total" name="total" type="numeric"  value="{{ old('total') }}"  >

                                </div>

                                <input class="btn  btn-success " type="submit" value="{{  trans('admin.confirmer')  }}">
                            </fieldset>
                        </form>
                    </div>
                </div>


            </div>
        </div>

        <div class="paginate" style="text-align: center;"> <?php echo(str_replace('/?', '?', $ligne_budget->render()) ); ?></div>


    </div>

    <script>
        function editer_classe(id,libelle_ligne,date_debut,date_fin,total)
        {
            document.getElementById("edit_id_classe").value = id;
            document.getElementById("edit-libelle_ligne").value =libelle_ligne;
            document.getElementById("edit-date_debut").value = date_debut;
            document.getElementById("edit-date_fin").value = date_fin;
            document.getElementById("edit-total").value =total;
        }
    </script>
    <!--    -->
@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('comptabilite')}}">@lang('admin.comptabilite')</a></strong></li>
    <li class="active"><strong>{{$titre}}</strong></li>
@endsection