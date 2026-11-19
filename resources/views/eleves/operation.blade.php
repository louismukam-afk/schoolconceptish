@extends('index')

@section('content')
    <div class="row text-center pad-top">
        @if(Auth::user()->canDoAction(array(\gesecole\Action::ACTION_STUDENT_DISCIPLINE_ETD_CREATE, \gesecole\Action::ACTION_STUDENT_DISCIPLINE_ETD_UPDATE)))
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('/eleves/disciplineetd1')}}" >
                    <i class="fa fa-pencil-square-o fa-5x"></i>
                    <h4>Effectuer l'appel en salle par specialite</h4>
                </a>
            </div>


        </div>
        @endif
        @if(Auth::user()->canDoAction(\gesecole\Action::ACTION_STUDENT_DISCIPLINE_ETD_REPORT))
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('/eleves/discipline_etat/')}}" >
                    <i class="fa fa-dot-circle-o fa-5x"></i>
                    <h4>Etats de discipline par  specialite</h4>
                </a>
            </div>


        </div>
        @endif

    </div>


@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('eleves')}}">@lang('menu.gestion_notes')</a></strong></li>

    <li class="active"><strong>{{$titre}}</strong></li>
@endsection
