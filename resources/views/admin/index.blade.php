@extends('index')

@section('content')
    <div class="page-header">
        <h3>Inscriptions</h3>
    </div>
    <div class="row text-center pad-top">
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{  URL::to('admin/cycles')  }}" >
                    <i class="fa fa-circle-o-notch fa-5x"></i>
                    <h4>Cycles</h4>
                </a>
            </div>


        </div>

        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{  URL::to('/admin/niveaux/')  }}" >
                    <i class="fa fa-level-up fa-5x "></i>
                    <h4>{{  trans('admin.level')  }}</h4>
                </a>
            </div>


        </div>
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{  URL::to('/admin/classes/')  }}" >
                    <i class="fa fa-5x fa-cloud"></i>
                    <h4>Classes</h4>
                </a>
            </div>


        </div>
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('/admin/scolarites/')}}" >
                    <i class="fa fa-5x fa-money"></i>
                    <h4>{{trans('admin.schoolfees')}}</h4>
                </a>
            </div>


        </div>


    </div>

    <div class="page-header">
        <h3>{{trans('admin.eleves_notes')}}</h3>
    </div>
    <div class="row text-center pad-top">

        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('admin/matieres/')}}" >
                    <i class="fa fa-book fa-5x"></i>
                    <h4>{{trans('admin.titre_matiere')}} </h4>
                </a>
            </div>


        </div>

        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('admin/programmes/')}}" >
                    <i class="fa fa-calendar-o fa-5x"></i>
                    <h4>{{trans('admin.programme')}} </h4>
                </a>
            </div>


        </div>
    </div>

    <div class="page-header">
        <h3>{{trans('admin.personnel')}}</h3>
    </div>
    <div class="row text-center pad-top">
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('admin/fonctions/')}}" >
                    <i class="fa fa-tencent-weibo fa-5x"></i>
                    <h4>{{trans('admin.fonctions')}} </h4>
                </a>
            </div>

        </div>

        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('admin/salaire/')}}" >
                    <i class="fa fa-money fa-5x"></i>
                    <h4>{{trans('admin.salaire')}} </h4>
                </a>
            </div>

        </div>

        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('admin/horaires/')}}" >
                    <i class="fa fa-clock-o fa-5x"></i>
                    <h4>{{trans('admin.horaire')}} </h4>
                </a>
            </div>

        </div>






    </div>

    <div class="row text-center pad-top">
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6"  style="display: none;">
            <div class="div-square">
                <a href="{{URL::to('/admin/taux/')}}" >
                    <i class="fa fa-5x fa-euro"></i>
                    <h4>{{trans('admin.taux_enseignant')}}</h4>
                </a>
            </div>


        </div>
    </div>

    <div class="page-header">
        <h3>{{trans('admin.autre')}} </h3>
    </div>
    <div class="row text-center pad-top">
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{url('/utilisateurs/')}}" >
                    <i class="fa fa-users fa-5x"></i>
                    <h4>{{trans('admin.utilisateurs')}} </h4>
                </a>
            </div>

        </div>
    </div>

@endsection