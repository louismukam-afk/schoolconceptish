@extends('index')

@section('content')
    <div class="page-header">
        <h3>{{trans('admin.personnel')}}</h3>
    </div>
    <div class="row text-center pad-top">
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{  URL::to('personnel/list/')  }}" >
                    <i class="fa fa-user fa-5x"></i>
                    <h4>{{trans('admin.ajouter_personnel')}}</h4>
                </a>
            </div>


        </div>

        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{  URL::to('/personnel/fonction/pers/')  }}" >
                    <i class="fa fa-5x fa-wrench"></i>
                    <h4>{{trans('admin.ajouter_fonction_personnel')}}</h4>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{  URL::to('/personnel/dispenses/')  }}" >
                    <i class="fa fa-clock-o fa-5x "></i>
                    <h4>{{trans('admin.gerer_emploi_temps')}}</h4>
                </a>
            </div>


        </div>


        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('/personnel/titulaire/')}}" >
                    <i class="fa fa-5x fa-bookmark-o"></i>
                    <h4>{{trans('admin.titulaire')}}</h4>
                </a>
            </div>


        </div>



    </div>
    <div class="row text-center pad-top">
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('/personnel/search/')}}" >
                    <i class="fa fa-5x fa-search"></i>
                    <h4>{{trans('admin.search_edit_personnel')}}</h4>
                </a>
            </div>


        </div>
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('/personnel/discipline/')}}" >
                    <i class="fa fa-5x fa-bar-chart-o"></i>
                    <h4>{{trans('admin.etat')}}</h4>
                </a>
            </div>


        </div>




    </div>



@endsection