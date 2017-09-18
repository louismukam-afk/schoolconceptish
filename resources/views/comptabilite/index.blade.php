@extends('index')

@section('content')

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



    </div>
    @endsection