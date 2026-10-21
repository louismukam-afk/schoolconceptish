@extends('skeleton')
@section('sub-content')
    <div class="col-md-6">
        @if(isset($success))

            <div class="alert alert-success">
                {{  trans('admin.succes')  }}
            </div>

        @endif

        <table class="table  table-striped table-condensed table-inverse" style="margin-top: 15px;">
            <thead>
            <caption style="font-size: 22px">{{  trans('admin.titre_table_niveau')  }}</caption>
            <th>#</th>
            <th>@lang('admin.nom')</th>
            <th>@lang('admin.nom_anglais')</th>
            <th>Domaine</th>
            <th></th>
            </thead>
            <tbody>
            <?php $i=1; ?>
            @foreach($niveaux as $n)
                <tr>
                    <td>{{$i++}}</td>
                    <td><strong >{{$n->nom}}</strong></td>
                    <td>
                        {{$n->nom_anglais}}
                    </td>
                    <td>{{$n->domaines}}</td>
                    <td>
                        <div class="btn-group">
                            <a href="#" class=" btn btn-primary dropdown-toggle" data-toggle="dropdown">Actions <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li><a style="color: white" class="btn-primary"  data-toggle="modal" data-backdrop="false" href="#edit_niveau" onclick="editer_niveau({{$n->id}},'{{addslashes($n->nom)}}','{{addslashes($n->nom_anglais)}}','{{addslashes($n->domaines)}}')"><span class="glyphicon glyphicon-pencil"  ></span> {{  trans('admin.edit')  }}</a></li>
                                <li>
                                    <a style="color: white" href="{{ URL::to('/admin/niveaux/destroy/').'/'.$n->id }}"   class="btn-danger"><span class="glyphicon glyphicon-trash"></span> @lang('admin.del')</a>
                                </li>
                            </ul>
                        </div>


                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_cycle"
        ><span class="glyphicon glyphicon-plus"></span>{{  trans('admin.nouveau_niveau')  }}</button>
        <div class="modal fade" id="add_cycle">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">x</button>
                        <h4 class="modal-title">{{  trans('admin.remplir_champs')  }}</h4>
                    </div>
                    <div
                            class="modal-body">



                        <form accept-charset="UTF-8" role="form" method="POST" id="add_level_form" action="{{ url('/admin/niveaux/store/') }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <fieldset>


                                <div class="form-group">
                                    <label for="nom">{{  trans('admin.nom')  }} : <span class="required">*</span></label>
                                    <input class="form-control" id="nom" name="nom" type="text"  value="{{ old('nom') }}" required autofocus>

                                </div>

                                <div class="form-group">
                                    <label>{{  trans('admin.nom_anglais')  }} : </label>
                                    <input class="form-control"  name="nom_anglais" type="text"  value="{{ old('nom_anglais') }}">

                                </div>
                                <div class="form-group">
                                    <label>Domaine : </label>
                                    <input class="form-control" name="domaines" type="text" value="{{ old('domaines') }}">
                                </div>
                                <input class="btn  btn-success " type="submit" value="{{  trans('admin.confirmer')  }}">


                            </fieldset>
                        </form>


                    </div>





                </div>


            </div>
        </div>



            <div class="modal fade" id="edit_niveau">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">x</button>
                            <h4 class="modal-title">{{  trans('admin.remplir_champs')  }}</h4>
                        </div>
                        <div
                                class="modal-body">



                            <form accept-charset="UTF-8" role="form" method="POST" id="edit_level_form" action="{{ url('/admin/niveaux/update/') }}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="id" id="edit_id_niveau" value=""/>
                                <fieldset>
                                    <div class="form-group">
                                        <label for="nom">{{  trans('admin.nom')  }} : <span class="required">*</span></label>
                                        <input class="form-control" id="edit_nom" name="nom" type="text"  value="{{ old('nom') }}" required autofocus>

                                    </div>

                                    <div class="form-group">
                                        <label>{{  trans('admin.nom_anglais')  }} : </label>
                                        <input class="form-control" id="edit_nom_anglais" name="nom_anglais" type="text"  value="{{ old('nom_anglais') }}" >

                                    </div>
                                    <div class="form-group">
                                        <label>Domaine : </label>
                                        <input class="form-control" id="edit_domaines" name="domaines" type="text" value="{{ old('domaines') }}">
                                    </div>
                                    <input class="btn  btn-success " type="submit" value="{{  trans('admin.confirmer')  }}">


                                </fieldset>
                            </form>



                        </div>





                    </div>


                </div>
            </div>

        <div class="paginate" style="text-align: center;"> <?php echo(str_replace('/?', '?', $niveaux->render()) ); ?></div>
    </div>

    <script>
        function editer_niveau(id,nom,nom_anglais,domaines)
        {

            document.getElementById("edit_id_niveau").value = id;
            document.getElementById("edit_nom").value = nom;
            document.getElementById("edit_nom_anglais").value = nom_anglais;
            document.getElementById("edit_domaines").value = domaines || '';

        }
    </script>

@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('admin')}}">@lang('admin.dashboard')</a></strong></li>
    <li class="active"><strong>{{$titre}}</strong></li>
@endsection