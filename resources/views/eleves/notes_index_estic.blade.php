@extends('index')

@section('content')
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
                <a href="{{URL::to('/eleves/notes/show_esstic')}}" >
                    <i class="fa fa-list-alt fa-5x"></i>
                    <h4>@lang('admin.afficher')</h4>
                </a>
            </div>


        </div>


    </div>


@endsection
