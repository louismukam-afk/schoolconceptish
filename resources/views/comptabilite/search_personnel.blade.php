@extends('skeleton')
@section('sub-content')
    @if(isset($success))

        <div class="alert alert-success">
            {{  trans('admin.succes')  }}
        </div>
        <a href="#" class="btn btn-lg btn-warning" onclick="window.print()">@lang('inscription.print_receipe')</a>

        @include('comptabilite.recu_payement')

    @else

    @if(isset($custom_error))

        <div class="alert alert-danger">
            {{  $custom_error  }}
        </div>

    @endif


    <div class="col-md-8">
        <form class="form-horizontal" id="add_inscription_form" method="get" action="{{URL::to('/search/personnel/')}}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
            <input type="hidden" name="view" value="comptabilite\search_personnel">
            <input type="hidden" name="titre" value="{{trans('admin.search_personnel')}}">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="search">{{  trans('inscription.recherche')  }} : </label>
                    <div class="input-group">
                        <input type="search" name="search" placeholder="{{trans('inscription.nom')}}" class="form-control" autofocus>
                        <div class="input-group-btn">
                            <button type="submit" class="btn btn-primary" ><span class="fa fa-search"> </span>{{trans('inscription.search')}}</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>

    <div class="col-md-8">
        @if(session('search_result'))
            <table class="table table-bordered table-striped table-condensed table-inverse" style="margin-top: 15px;">
                <thead>
                <caption style="font-size: 22px">{{  trans('inscription.search_result')  }}</caption>
                <th>#</th>
                <th>{{  trans('admin.nom')  }}</th>

                <th>{{  trans('inscription.sexe')  }}</th>

                </thead>
                <tbody>
                <?php $i=1; ?>
                @foreach($personnel as $p)
                    <tr>
                        <td>{{$i++}}</td>
                        <td><strong >{{$p->nom}}</strong></td>
                        <td>{{$p->sexe}}</td>
                        <td>
                            <a  href="{{ URL::to('/comptabilite/payement-salaire/create/').'/'.$p->id }}"   class="btn btn-success"> <span class="fa fa-money"></span> {{  trans('admin.payer_personnel')  }}</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="paginate" style="text-align: center;"> <?php echo(str_replace('/?', '?', $personnel->render()) ); ?></div>

            {{Session::forget('search_result')}}
        @endif
    </div>
    @endif
@endsection