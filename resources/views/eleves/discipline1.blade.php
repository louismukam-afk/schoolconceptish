@extends('skeleton')
@section('sub-content')
    <div class="col-md-12">
        @if(isset($success))

            <div class="alert alert-success">
                {{  trans('admin.succes')  }}
            </div>

        @endif





            <div class="etat-disc">
                <h4>{{trans('eleve.etat_discipline').' '.$pers->nom.' '.$pers->prenom}}</h4>
                @if(isset($exclus_def))
                    <div class="alert alert-danger">
                        <a href="#show_others" class="text-danger" data-toggle="modal" data-backdrop="false" onclick="loadOthers(5)"><span class="glyphicon glyphicon-eye-open"></span>  <strong style="font-size: 16px"> @lang('admin.exclus_def')</strong> </a>


                    </div>
                @endif
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <div class="panel-title">
                            <h4>Informations</h4>

                        </div>


                    </div>

                    <div class="panel-body">
                        <div class="col-md-4">

                            <a href="#show_others"  data-toggle="modal" data-backdrop="false" onclick="loadOthers(1)"><span class="glyphicon glyphicon-eye-open"></span> <strong style="font-size: 16px">{{trans('eleve.retard')}}</strong> : {{ $retard}}</a>

                        </div>
                        <div class="col-md-4">

                            <a href="#show_abscence_inj"  data-toggle="modal" data-backdrop="false" onclick="loadAbscenceInj()"><span class="glyphicon glyphicon-eye-open"></span>  <strong style="font-size: 16px">{{trans('eleve.abscence_i')}}</strong>( {{trans('eleve.heure')}}) : {{$abscence_inj}}</a>
                        </div>
                        <div class="col-md-4">

                            <a href="#show_abscence_just"  data-toggle="modal" data-backdrop="false" onclick="loadAbscenceJust()"><span class="glyphicon glyphicon-eye-open"></span>  <strong style="font-size: 16px">{{trans('eleve.abscence_j')}}</strong>( {{trans('eleve.heure')}}) : {{$abscence_just}}</a>
                        </div>
                        <div class="col-md-4">

                            <a href="#show_others"  data-toggle="modal" data-backdrop="false" onclick="loadOthers(3)"><span class="glyphicon glyphicon-eye-open"></span>  <strong style="font-size: 16px">{{trans('eleve.avertissement')}}</strong> :{{$avertissement}}</a>

                        </div>
                        <div class="col-md-4">

                            <a href="#show_others"  data-toggle="modal" data-backdrop="false" onclick="loadOthers(4)"><span class="glyphicon glyphicon-eye-open"></span>  <strong style="font-size: 16px">{{trans('eleve.blame')}}</strong> : {{$blame}}</a>

                        </div>
                        <div class="col-md-4">

                            <a href="#show_exclusion"  data-toggle="modal" data-backdrop="false" onclick="loadJoursEx()"><span class="glyphicon glyphicon-eye-open"></span> <strong style="font-size: 16px">{{trans('eleve.exclusion')}}</strong>( {{trans('eleve.jour')}}) : {{$exclusion}}</a>

                        </div>
                    </div>

                </div>
                <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#new_discipline"
                ><span class="glyphicon glyphicon-plus"></span>{{  trans('admin.nouveau')  }}</button>
            </div>



            <div class="modal fade" id="new_discipline">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">x</button>
                            <h4 class="modal-title">{{  trans('admin.remplir_champs')  }}</h4>
                        </div>
                        <div class="modal-body">
                            <form accept-charset="UTF-8" id="new_discipline_form" role="form" method="POST" action="{{route('update_discipline')}}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="id" value="{{$pers->id}}">

                                <fieldset>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="type">Type : </label>
                                            <select id="type" name="type" form="new_discipline_form" class="form-control" onchange="handleType(this)">
                                                <option value="0">@lang('admin.abscences')</option>
                                                <option value="1">@lang('admin.retards')</option>
                                                <option value="2">@lang('eleve.exclusion')</option>
                                                <option value="3">@lang('eleve.avertissement')</option>
                                                <option value="4">@lang('eleve.blame')</option>
                                                <option value="5">@lang('admin.exclus_def')</option>
                                            </select>
                                        </div>


                                  </div>
                                    <div class="col-md-6 gpe_heures">
                                        <div class="form-group">
                                            <label for="nb_heures">@lang('admin.nombre_heure_abscence') :</label>
                                            <input id="nb_heures" name="nb_heures" type="number" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-6 gpe_jours">
                                        <div class="form-group">
                                            <label for="heure_arrivee">@lang('admin.nb_jours_ex') :</label>
                                            <input id="nb_jours" name="nb_jours" type="number" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6" style="display: none;">
                                        <div class="form-group">
                                            <label for="sequence">Sequence : </label>
                                            <select id="sequence" name="sequence" form="new_discipline_form" class="form-control" disabled>
                                                <option value="1">1</option>

                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="description">@lang('admin.mod') :</label>
                                            <textarea id="description" name="description" class="form-control"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="date">Date :</label>
                                            <input readonly id="date" name="date" type="text" class="form-control" placeholder="yyyy/mm/dd">
                                        </div>
                                    </div>



                                </fieldset>
                                <input class="btn  btn-success " type="submit" value="{{  trans('admin.confirmer')  }}">

                            </form>

                        </div>

                    </div>
                </div>
            </div>



            <div class="modal fade" id="show_abscence_inj">
                <div class="modal-dialog" style="min-width: 60%;" >
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" onclick="location.reload()">x</button>
                            <h4 class="modal-title">{{  trans('admin.remplir_champs')  }}</h4>
                        </div>
                        <div class="modal-body">
                            <form id="justify_form" >
                                <input type="hidden" name="_token" value="{{ csrf_token() }}"/>

                                <a style="margin-bottom: 15px" href="#" onclick="justify()" class="btn btn-xs btn-warning pull-right">@lang('admin.just_abscence')</a>

                                <table id="table_abs_inj" class=" table  table-striped table-condensed table-inverse" style="margin-top: 15px;">
                                    <thead>
                                    <th style="width:150px">@lang('admin.nombre_heure_abscence')</th>
                                    <th >Details</th>
                                    <th style="width: 100px">Date</th>
                                    <th style="width: 25px"></th>

                                    </thead>
                                    <tbody id="abscence_inj_pa">
                                    </tbody>
                                </table>
                            </form>


                        </div>

                    </div>
                </div>
            </div>

            <div class="modal fade" id="show_abscence_just">
                <div class="modal-dialog" style="min-width: 60%;" >
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">x</button>
                            <h4 class="modal-title">{{  trans('admin.remplir_champs')  }}</h4>
                        </div>
                        <div class="modal-body">



                                <table id="table_abs_just" class=" table  table-striped table-condensed table-inverse" style="margin-top: 15px;">
                                    <thead>
                                    <th style="width:150px">@lang('admin.nombre_heure_abscence')</th>
                                    <th >Details</th>
                                    <th style="width: 100px">Date</th>

                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>


                        </div>

                    </div>
                </div>
            </div>

            <div class="modal fade" id="show_exclusion">
                <div class="modal-dialog" style="min-width: 60%;" >
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">x</button>
                            <h4 class="modal-title">{{  trans('admin.remplir_champs')  }}</h4>
                        </div>
                        <div class="modal-body">



                            <table id="table_exclusion" class=" table  table-striped table-condensed table-inverse" style="margin-top: 15px;">
                                <thead>
                                <th style="width:150px">@lang('admin.nb_jours_ex')</th>
                                <th >Details</th>
                                <th style="width: 100px">Date</th>

                                </thead>
                                <tbody>
                                </tbody>
                            </table>


                        </div>

                    </div>
                </div>
            </div>

            <div class="modal fade" id="show_others">
                <div class="modal-dialog" style="min-width: 60%;" >
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">x</button>
                            <h4 class="modal-title">{{  trans('admin.remplir_champs')  }}</h4>
                        </div>
                        <div class="modal-body">



                            <table id="table_others" class=" table  table-striped table-condensed table-inverse" style="margin-top: 15px;">
                                <thead>
                                <th >Details</th>
                                <th style="width: 100px">Date</th>

                                </thead>
                                <tbody>
                                </tbody>
                            </table>


                        </div>

                    </div>
                </div>
            </div>
    </div>

    <script>
        function hideALL() {
            $('.gpe_heures').css('display','none');
            $('.gpe_jours').css('display','none');
        }
        function handleType(element){
            hideALL();
            var val=element.value;
            if(val==0){
                $('.gpe_heures').css('display','block');
            }
            if(val==2){
                $('.gpe_jours').css('display','block');
            }
        }
        $(function () {
            $('.gpe_jours').css('display','none');
        });

        function loadAbscenceInj() {
            startLoading();
            $('#table_abs_inj').find('tbody').html('');
            $.ajax({
                url:'{{url('/eleves/discipline/show/precision/').'/'.$pers->id}}',
                type:'GET',
                dataType:'html',
                data:'type=0&just=0',
                complete: function (res,stat) {
                    var abs_arr=JSON.parse(res.responseText);
                    var i=1;
                    $.each(abs_arr,function (key,val) {
                        $('#table_abs_inj').find('tbody').append('<tr>  <td>'+ val.nb_heures +'</td> <td>'+ val.description +'</td> <td>'+ val.ddate +'</td> <td><input type="checkbox" value="'+ val.id +'" name="justify[]" ></td> </tr>');
                        i++;
                    });

                    stopLoading();

                }
            })
                
        }

        function loadAbscenceJust() {
            startLoading();
            $('#table_abs_just').find('tbody').html('');
            $.ajax({
                url:'{{url('/eleves/discipline/show/precision/').'/'.$pers->id}}',
                type:'GET',
                dataType:'html',
                data:'type=0&just=1',
                complete: function (res,stat) {
                    var abs_arr=JSON.parse(res.responseText);
                    var i=1;
                    $.each(abs_arr,function (key,val) {
                        $('#table_abs_just').find('tbody').append('<tr>  <td>'+ val.nb_heures +'</td> <td>'+ val.description +'</td> <td>'+ val.ddate +'</td>  </tr>');
                        i++;
                    });

                    stopLoading();

                }
            })

        }

        function loadJoursEx() {
            startLoading();
            $('#table_exclusion').find('tbody').html('');
            $.ajax({
                url:'{{url('/eleves/discipline/show/precision/').'/'.$pers->id}}',
                type:'GET',
                dataType:'html',
                data:'type=2',
                complete: function (res,stat) {
                    var abs_arr=JSON.parse(res.responseText);
                    var i=1;
                    $.each(abs_arr,function (key,val) {
                        $('#table_exclusion').find('tbody').append('<tr>  <td>'+ val.nb_jours +'</td> <td>'+ val.description +'</td> <td>'+ val.ddate +'</td>  </tr>');
                        i++;
                    });
                    stopLoading();

                }
            })

        }

        function loadOthers(type) {
            startLoading();
            $('#table_others').find('tbody').html('');
            $.ajax({
                url:'{{url('/eleves/discipline/show/precision/').'/'.$pers->id}}',
                type:'GET',
                dataType:'html',
                data:'type='+type,
                complete: function (res,stat) {
                    var abs_arr=JSON.parse(res.responseText);
                    var i=1;
                    $.each(abs_arr,function (key,val) {
                        $('#table_others').find('tbody').append('<tr>   <td>'+ val.description +'</td> <td>'+ val.ddate +'</td>  </tr>');
                        i++;
                    });
                    stopLoading();

                }
            })

        }

        function justify() {
            startLoading();
            $.ajax({
                url:'{{url('/eleves/discipline/justify/')}}',
                type:'POST',
                data: new FormData($('#justify_form')[0]),
                cache: false,
                contentType: false,
                processData: false,
                complete: function (res,stat) {
                    loadAbscenceInj();
                }
            })

        }
    </script>

@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('eleves')}}">@lang('menu.gestion_notes')</a></strong></li>
    <li><strong><a href="{{url('eleves/discipline')}}">@lang('inscription.title_search')</a></strong></li>

    <li class="active"><strong>{{$titre}}</strong></li>
@endsection