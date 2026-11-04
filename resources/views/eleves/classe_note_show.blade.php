@extends('skeleton')
@section('sub-content')
    @if(Auth::user()->canDoAction(\gesecole\Action::ACTION_STUDENT_NOTE_CU) && !Auth::user()->isTeacher())
    <p><a class="btn btn-success" href="{{ url('/eleves/notes/deliberations') }}">Délibérations par tutelle</a> <a class="btn btn-info" href="{{ url('/eleves/notes/deliberations/rapport') }}">Rapport des délibérations</a></p>
    @endif
    <h4>{{trans('eleve.choix_classe')}}</h4>
    <hr>
    @include('eleves.partials.cycle_niveau_filiere_filter', array('selectionBaseUrl' => Request::is('eleves/notes/show_esstic') ? URL::to('/eleves/notes/matieres/show_esstic/') : URL::to('/eleves/notes/matieres/show/')))
    <div class="liste-classe col-md-12">
        <ul>
           



                    @foreach($niveaux as $n)
                        <div class="col-md-6 niveau-block" data-filiere="{{$n->id}}" style="display:none;">
                            <h4 class="page-header"> {{$n->getNom()}}</h4>

                            @foreach($classes as $c)
                                <div class="col-md-12">
                                    @if($c->id_niveau==$n->id)
                                        <li class="col-md-12"><a class="special cycle-niveau-specialite-link" href="#" data-classe="{{$c->id}}">{{$c->getNom()}}</a></li>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endforeach



        </ul>
    </div>






@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('eleves')}}">@lang('menu.gestion_notes')</a></strong></li>
    <li><strong><a href="{{url('eleves/notes')}}">Notes</a></strong></li>

    <li class="active"><strong>@lang('admin.choix_classe')</strong></li>
@endsection
