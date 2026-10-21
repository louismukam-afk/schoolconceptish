@extends('skeleton')
@section('sub-content')
    <div class="col-md-12" style="margin-bottom: 15px;">
        <a class="btn btn-warning" href="{{ url('/admin/sessions/global') }}">
            <span class="glyphicon glyphicon-th-list"></span> Sessions examens globales
        </a>
    </div>

    <ul class="col-md-12">



                @foreach($niveaux as $n)
                    <div class="col-md-6">
                        <h4 class="page-header"> {{$n->getNom()}}</h4>

                        @foreach($classes as $c)
                            <div class="col-md-12">
                                @if($c->id_niveau==$n->id)
                                    <li class="col-md-12"><a class="special" data-toggle="modal" data-backdrop="false" href="#choose_level" onclick="choose_level({{$c->id}}, '{{URL::to('/admin/programmes/selection-matieres/')}}', '')">{{$c->getNom()}}</a></li>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach

    </ul>



@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('admin')}}">@lang('admin.dashboard')</a></strong></li>
    <li class="active"><strong>{{$titre}}</strong></li>
@endsection
