@extends('index')

@section('content')
    <div class="row text-center pad-top">
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('/eleves/discipline/')}}" >
                    <i class="fa fa-bell-o fa-5x"></i>
                    <h4>{{trans('eleve.titre_discipline')}}</h4>
                </a>
            </div>


        </div>
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('/eleves/disciplineetd')}}">
                    <i class="fa fa-pencil-square-o fa-5x"></i>
                    <h4>Effectuer l'appel en salle par specialite</h4>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('/eleves/releves_bts/')}}">
                    <i class="fa fa-file-text-o fa-5x"></i>
                    <h4>Releves de notes BTS par session</h4>
                </a>
            </div>
        </div>
         <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('/eleves/notes/')}}">
                    <i class="fa fa-list-alt fa-5x"></i>
                    <h4>Notes et PV par session</h4>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{ route('notes_import') }}">
                    <i class="fa fa-file-excel-o fa-5x"></i>
                    <h4>Importer les notes</h4>
                </a>
            </div>
        </div>

         <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('/eleves/soa/')}}" >
                    <i class="fa fa-pencil-square-o fa-5x"></i>
                    <h4>Notes de Soa</h4>
                </a>
            </div>


        </div>
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('/eleves/ndere/')}}" >
                    <i class="fa fa-pencil-square-o fa-5x"></i>
                    <h4>Notes de Ngaoundéré</h4>
                </a>
            </div>


        </div>

        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6" >
            <div class="div-square">
                <a   href="{{url('/eleves/releves_soa/')}}" >
                    <i class="fa fa-barcode fa-5x"></i>
                    <h4>Relevé de notes Soa</h4>
                </a>
            </div>


        </div>
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6" >
            <div class="div-square">
                <a href="{{url('/eleves/releves_ndere/')}}" >
                    <i class="fa fa-barcode fa-5x"></i>
                    <h4>Releve de notes Ngaoundere</h4>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6" >
            <div class="div-square">
                <a href="{{url('/eleves/attestations_reussite_ndere/')}}" >
                    <i class="fa fa-graduation-cap fa-5x"></i>
                    <h4>Attestations de reussite Ngaoundere</h4>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6" >
            <div class="div-square">
                <a href="{{url('/eleves/attestations_reussite_soa/')}}" >
                    <i class="fa fa-graduation-cap fa-5x"></i>
                    <h4>Attestations de reussite SOA</h4>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6" >
            <div class="div-square">
                <a href="{{url('/eleves/releve_estic/')}}" >
                    <i class="fa fa-barcode fa-5x"></i>
                    <h4>Releve de notes Ebolowa</h4>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6" >
            <div class="div-square">
                <a href="{{url('/eleves/attestations_reussite_ebolowa/')}}" >
                    <i class="fa fa-graduation-cap fa-5x"></i>
                    <h4>Attestations de reussite Ebolowa</h4>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6" >
            <div class="div-square">
                <a href="{{url('/eleves/releves_bamenda/')}}" >
                    <i class="fa fa-barcode fa-5x"></i>
                    <h4>Bamenda transcripts</h4>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6" >
            <div class="div-square">
                <a href="{{url('/eleves/estic/')}}" >
                    <i class="fa fa-pencil-square-o fa-5x"></i>
                    <h4>Notes et PV Ebolowa</h4>
                </a>
            </div>
        </div>
    </div>
@endsection

@section('breadcrumb')
    <li class="active"><strong>@lang('menu.gestion_notes')</strong></li>
@endsection
