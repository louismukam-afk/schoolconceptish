@extends('index')

@section('content')
<div class="alert alert-warning">
    <h4>@lang('admin.total_entrees') : <strong>{{number_format($entrees).' '.trans('inscription.devise')}}</strong></h4>
    <h4>@lang('admin.total_sorties') : <strong>{{number_format($sorties).' '.trans('inscription.devise')}}</strong></h4>
    <h4>@lang('admin.solde_caisse') : <strong>{{number_format($entrees-$sorties).' '.trans('inscription.devise')}}</strong></h4>
</div>
    <div class="row text-center pad-top">
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('admin/retrait/')}}" >
                    <i class="fa fa-money fa-5x"></i>
                    <h4>{{trans('admin.effectuer_retrait')}} </h4>
                </a>
            </div>

        </div>
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('comptabilite/payement-salaire/')}}" >
                    <i class="fa fa-money fa-5x"></i>
                    <h4>{{trans('admin.payer_personnel')}} </h4>
                </a>
            </div>

        </div>

        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{url('/comptabilite/bilan/entrees')}}" >
                    <i class="fa fa-download fa-5x"></i>
                    <h4>{{trans('admin.bilan_entrees')}} </h4>
                </a>
            </div>

        </div>

        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{url('/comptabilite/bilan/sorties')}}" >
                    <i class="fa fa-upload fa-5x"></i>
                    <h4>{{trans('admin.bilan_sorties')}} </h4>
                </a>
            </div>

        </div>
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{url('/comptabilite/categorie/')}}" >
                    <i class="fa fa-bank fa-5x"></i>
                    <h4>{{trans('admin.categorie_budget')}} </h4>
                </a>
            </div>

        </div>

        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{url('/comptabilite/ligne/')}}" >
                    <i class="fa fa-life-bouy fa-5x"></i>
                    <h4>{{trans('admin.ligne_budget')}} </h4>
                </a>
            </div>

        </div>

    </div>
    @endsection

@section('breadcrumb')

    <li class="active"><strong>@lang('admin.comptabilite')</strong></li>
@endsection