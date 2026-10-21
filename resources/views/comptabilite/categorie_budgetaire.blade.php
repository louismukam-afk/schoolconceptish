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
            <caption style="font-size: 22px">{{  trans('admin.liste_categorie_budgetaire')  }}</caption>
            <th>#</th>
            <th>{{  trans('admin.numero_compte')  }}</th>
            <th>{{  trans('admin.libelle')  }}</th>
            <th>{{  trans('admin.description')  }}</th>
            <th></th>
            </thead>
            <tbody>
            <?php $i=1; ?>
            @foreach($categorie_budget as $c)
                <tr>
                    <td>{{$i++}}</td>
                    <td>{{$c->numero_compte}}</td>
                    <td><strong >{{$c->libelle}}</strong></td>
                    <td>{{$c->description}}</td>
                    <td>
                        <div class="btn-group">
                            <a href="#" class=" btn btn-primary dropdown-toggle" data-toggle="dropdown">Actions <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li><a style="color: white" title="Modifier les informations" class="btn-primary"  data-toggle="modal" data-backdrop="false" href="#edit_classe" onclick="editer_classe({{$c->id}},'{{sanitize($c->numero_compte)}}','{{sanitize($c->libelle)}}','{{sanitize($c->description)}}')"><span class="glyphicon glyphicon-pencil"  ></span> {{  trans('admin.edit')  }}</a></li>
                                <li><a style="color: white" href="{{ URL::to('/comptabilite/categorie/destroy/').'/'.$c->id }}"   class="btn-danger"><span class="glyphicon glyphicon-trash"></span> {{  trans('admin.del')  }}</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_cycle"
        ><span class="glyphicon glyphicon-plus"></span>{{  trans('admin.categorie_new')  }}</button>
        <div class="modal fade" id="add_cycle">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">x</button>
                        <h4 class="modal-title">{{  trans('admin.remplir_champs')  }}</h4>
                    </div>
                    <div
                            class="modal-body">



                        <form accept-charset="UTF-8" role="form" method="POST" id="add_level_form" action="{{ url('/comptabilite/categorie/store/') }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <fieldset>

                                <div class="form-group">
                                    <label>{{trans('admin.numero_compte')}} : <span class="required">*</span></label>
                                    <input class="form-control"  name="numero_compte" type="text"  value="{{ old('numero_compte') }}"  autofocus>

                                </div>
                                <div class="form-group">
                                    <label for="libelle">{{  trans('admin.libelle')  }} : <span class="required">*</span></label>
                                    <input class="form-control" id="libelle" name="libelle" type="text"  value="{{ old('libelle') }}"  >

                                </div>
                                <div class="form-group">
                                    <label for="description">{{  trans('admin.description')  }} informations:</label>
                                    <textarea  class="form-control" id="description" name="description">{{ old('description') }}</textarea>

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



                        <form accept-charset="UTF-8" role="form" method="POST" id="edit_classes_form" action="{{ url('/comptabilite/categorie/update/') }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                            <input type="hidden" name="id" id="edit_id_classe" value=""/>
                            <fieldset>

                                <div class="form-group">
                                    <label>{{trans('admin.numero_compte')}} <span class="required">*</span></label>
                                    <input class="form-control" id="edit_numero_compte" name="numero_compte" type="text"  value="{{ old('numero_compte') }}"  autofocus>

                                </div>
                                <div class="form-group">
                                    <label for="libelle">{{  trans('admin.libelle')  }} : <span class="required">*</span></label>
                                    <input class="form-control" id="edit_libelle" name="libelle" type="text"  value="{{ old('libelle') }}"  required>

                                </div>

                                <div class="form-group">
                                    <label>{{  trans('admin.description')  }} : </label>
                                    <textarea class="form-control" id="edit_description" name="description" type="text"  value="{{ old('description') }}"  ></textarea>

                                </div>
                                <input class="btn  btn-success " type="submit" value="{{  trans('admin.confirmer')  }}">
                            </fieldset>
                        </form>
                    </div>
                </div>


            </div>
        </div>
        <div class="paginate" style="text-align: center;"> <?php echo(str_replace('/?', '?', $categorie_budget->render()) ); ?></div>

    </div>

    <script>
        function editer_classe(id,numero_compte,libelle,description)
        {
            document.getElementById("edit_id_classe").value = id;
            document.getElementById("edit_numero_compte").value = numero_compte;
            document.getElementById("edit_libelle").value = libelle;
            document.getElementById("edit_description").value = description;
        }
    </script>
    <!--    -->
@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('comptabilite')}}">@lang('admin.comptabilite')</a></strong></li>
    <li class="active"><strong>{{$titre}}</strong></li>
@endsection