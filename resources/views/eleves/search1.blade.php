@extends('skeleton')
@section('sub-content')
    @if(isset($success))

        <div class="alert alert-success">
            {{  trans('admin.succes')  }}
        </div>

    @endif


    <div id="monaccordeon" class="panel-group col-md-12">
        <h3>{{trans('inscription.choix_liste')}}</h3>
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <a class="accordion-toggle" href="#item1" dataparent="#monaccordeon"
                       data-toggle="collapse">
                        {{trans('admin.etat_disc')}}
                    </a>
                </h3>
            </div>

            <div id="item1"
                 class="panel-collapse collapsing">
                <div
                        class="panel-body">
                    <li class="col-md-12"><a href={{route('etat_disciplinaire').'?id=0'}}>{{trans('inscription.all_eleves')}}</a></li>




                        @foreach($niveaux as $n)
                            <div class="col-md-6">
                                <h4 class="page-header"> {{$n->getNom()}}</h4>

                                @foreach($classes as $c)
                                    <div class="col-md-12">
                                        @if($c->id_niveau==$n->id)
                                            <li class="col-md-12"><a class="special" data-toggle="modal" data-backdrop="false" href="#choose_level" onclick="choose_level({{$c->id}}, '{{route('etat_disciplinaire')}}', '')">{{$c->getNom()}}</a></li>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endforeach



                </div>

            </div>
        </div>
    </div>

    <div class="col-md-8">
        <form class="form-horizontal" id="add_inscription_form" method="get" action="{{URL::to('/eleves/discipline/search')}}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
            <div class="col-md-8">
                <div class="form-group">
                    <label for="search">{{  trans('inscription.recherche')  }} : </label>
                    <div class="input-group">
                        <input type="search" name="search" placeholder="{{trans('inscription.nom')}} ou Matricule" class="form-control" autofocus>
                        <div class="input-group-btn">
                            <button type="submit" class="btn btn-primary" ><span class="fa fa-search"> </span>{{trans('inscription.search')}}</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>

    <div class="col-md-10">
        @if(session('search_result'))
            <table class="table table-bordered table-striped table-condensed table-inverse" style="margin-top: 15px;">
                <thead>
                <caption style="font-size: 22px">{{  trans('inscription.search_result')  }}</caption>
                <th>#</th>
                <th>Matricule</th>
                <th>{{  trans('admin.nom')  }}</th>
                <th>{{  trans('inscription.date_naiss')  }}</th>
                <th>{{  trans('inscription.lieu_naiss')  }}</th>
                <th>{{  trans('inscription.sexe')  }}</th>
                <th>@lang('admin.specialites')</th>

                </thead>
                <tbody>
                <?php $i=1; ?>
                @foreach($eleves as $e)
                    <tr>
                        <td>{{$i++}}</td>
                        <td>{{$e->matricule}}</td>
                        <td><strong >{{$e->nom.' '.$e->prenom}}</strong></td>
                        <td>{{$e->date_naiss}}</td>
                        <td>{{$e->lieu_naiss}}</td>
                        <td>{{$e->sexe}}</td>
                        <td>{{(session('locale')=='en')?$e->nom_classe_anglais:$e->nom_classe}}</td>
                        <td>

                                    <a href="{{URL::to('/eleves/discipline/show/'.$e->id)}}"  class=" btn btn-success" ><span class="fa fa-bell-o"></span> {{trans('eleve.titre_discipline')}}</a


                    </tr>
                @endforeach
                </tbody>
            </table>

            {{Session::forget('search_result')}}
        @elseif(session('info_discipline'))


                {{Session::forget('info_discipline')}}
                @endif
    </div>



@endsection
@section('breadcrumb')
    <li><strong><a href="{{url('eleves')}}">@lang('menu.gestion_notes')</a></strong></li>
    <li class="active"><strong>{{$titre}}</strong></li>
@endsection
