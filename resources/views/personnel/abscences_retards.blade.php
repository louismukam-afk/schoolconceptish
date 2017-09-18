@extends('skeleton')
@section('sub-content')
    <div class="col-md-12">
        @if(isset($success))

            <div class="alert alert-success">
                {{  trans('admin.succes')  }}
            </div>

        @endif
    </div>
        <div class="main">
            <div class="col-md-5">
                <table class="table table-bordered table-striped table-condensed table-inverse" style="margin-top: 15px;">
                    <caption><h4>{{trans('eleve.abscence')}}</h4></caption>
                    <thead>
                    <th>#</th>
                    <th>{{trans('admin.heure')}}</th>
                    <th>Date</th>
                    </thead>
                    <tbody>
                    <?php  $i=0;$s=0; ?>
                    @foreach($abscences as $a)

                        <tr>
                            <td>{{++$i}}</td>


                            <td>{{$a->nombre}}</td>
                            <?php $s=$s+$a->nombre; ?>
                            <td>{{$a->date_d}}</td>



                            <td><a href="{{ URL::to('/personnel/discipline/destroy/').'/'.$a->id_personnel.'/'.$a->id }}"  class="btn btn-danger btn-xs pull-right"><span class="glyphicon glyphicon-trash"></span> {{trans('admin.del')}}</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <p><strong>Total: {{$s.' '.trans('admin.heure')}} </strong></p>
                <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_abscence"
                ><span class="glyphicon glyphicon-plus"></span> {{  trans('admin.ajouter_abscence')  }}</button>

                <div class="paginate" style="text-align: center;"> <?php echo(str_replace('/?', '?', $abscences->render()) ); ?></div>

            </div>


            <div class="col-md-5 col-md-offset-2">
                <table class="table table-bordered table-striped table-condensed table-inverse" style="margin-top: 15px;">
                    <caption><h4>{{trans('eleve.retard')}}</h4></caption>
                    <thead>
                    <th>#</th>
                    <th>{{trans('admin.heure_arrive')}}</th>
                    <th>Date</th>
                    </thead>
                    <tbody>
                    <?php  $i=0; ?>
                    @foreach($retards as $r)

                        <tr>
                            <td>{{++$i}}</td>
                            <td>{{$r->heure_arrive}}</td>
                            <td>{{$r->date_d}}</td>



                            <td><a href="{{ URL::to('/personnel/discipline/destroy/').'/'.$r->id_personnel.'/'.$r->id }}"  class="btn btn-danger btn-xs pull-right"><span class="glyphicon glyphicon-trash"></span> {{trans('admin.del')}}</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <p><strong>Total: {{$i.' '.trans('eleve.retard')}}</strong></p>
                <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_retard"
                ><span class="glyphicon glyphicon-plus"></span> {{  trans('admin.ajouter_retard')  }}</button>

                <div class="paginate" style="text-align: center;"> <?php echo(str_replace('/?', '?', $retards->render()) ); ?></div>


            </div>

        </div>
.



        <div class="modal fade" id="add_abscence">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">x</button>
                        <h4 class="modal-title">{{  trans('admin.remplir_champs')  }}</h4>
                    </div>
                    <div class="modal-body">



                        <form accept-charset="UTF-8"  role="form" method="POST" action="{{ url('/personnel/discipline/store/') }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="id_personnel" value="{{$personnel->id}}">
                            <input type="hidden" name="type" value="0">

                            <fieldset>
                                <div class="form-group">
                                    <label>{{  trans('admin.nombre_heure_abscence')  }} : <span class="required">*</span></label>
                                    <input type="number" name="nombre" class="form-control" required>
                                </div>



                                <input class="btn  btn-success " type="submit" value="{{  trans('admin.confirmer')  }}">

                            </fieldset>
                        </form>


                    </div>

                </div>


            </div>
        </div>
            <div class="modal fade" id="add_retard">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">x</button>
                            <h4 class="modal-title">{{  trans('admin.remplir_champs')  }}</h4>
                        </div>
                        <div class="modal-body">



                            <form accept-charset="UTF-8"  role="form" method="POST" action="{{ url('/personnel/discipline/store/') }}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="id_personnel" value="{{$personnel->id}}">
                                <input type="hidden" name="type" value="1">

                                <fieldset>
                                    <div class="form-group">
                                        <label>{{  trans('admin.heure_arrive')  }} : <span class="required">*</span></label>
                                        <input type="text" placeholder="8h00" name="heure_arrive" class="form-control" required>
                                    </div>



                                    <input class="btn  btn-success " type="submit" value="{{  trans('admin.confirmer')  }}">

                                </fieldset>
                            </form>


                        </div>

                    </div>


                </div>
            </div>


@endsection