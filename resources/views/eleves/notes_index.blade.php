@extends('index')

@section('content')
    @if(Auth::user()->canDoAction(\gesecole\Action::ACTION_STUDENT_NOTE_CU) && !Auth::user()->isTeacher())
    <p><a class="btn btn-success" href="{{ url('/eleves/notes/deliberations') }}">Délibérations par tutelle</a> <a class="btn btn-info" href="{{ url('/eleves/notes/deliberations/rapport') }}">Rapport des délibérations</a></p>
    @endif
    <div class="alert alert-info">
        <a class="btn btn-primary" href="{{ url('/eleves/documents-tutelles') }}">Notes et documents par tutelle</a>
        <a class="btn btn-warning" href="{{ url('/eleves/notes/anonymats') }}">Anonymats par tutelle</a>
        SOA · Ngaoundéré · ESSTIC · ISABEE / Ebolowa · Polytechnique
    </div>
    <div class="row">
        @foreach(config('academic_documents.profiles') as $model => $profile)
            <div class="col-md-2"><a class="btn btn-primary btn-block" href="{{ url('/eleves/documents-tutelles?modele='.$model) }}">{{ $profile['label'] }}</a></div>
        @endforeach
    </div>
    <div class="row text-center pad-top">
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('/eleves/notes/edit')}}" >
                    <i class="fa fa-pencil-square-o fa-5x"></i>
                    <h4>{{trans('admin.modifier')}}</h4>
                </a>
            </div>


        </div>
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('/eleves/notes/show')}}" >
                    <i class="fa fa-list-alt fa-5x"></i>
                    <h4>@lang('admin.afficher')</h4>
                </a>
            </div>


        </div>

     <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('/eleves/notes/fiche')}}" >
                    <i class="fa fa-list-alt fa-5x"></i>
                    <h4>Fiches individualisées</h4>
                </a>
            </div>


        </div>


    </div>


@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('eleves')}}">@lang('menu.gestion_notes')</a></strong></li>

    <li class="active"><strong>{{$titre}}</strong></li>
@endsection
