@extends('skeleton')
@section('sub-content')
<div class="col-md-10">
    @include('perso.functions')

    <div class="row">
        <div class="col-md-12">
            <a href="#" class="btn btn-warning btn-lg" onclick="window.print()"><span class="glyphicon glyphicon-print"></span> {{trans('inscription.imprimer')}}</a>
        </div>
    </div>
    <link rel="stylesheet" media="print" href="{{URL::asset('css/print_table.css')}}">

    <h3>@lang('admin.ligne_budget'): <strong>{{ $categorie->libelle }}</strong></h3>
    <table class="table table-striped table-bordered table-condensed table-inverse" style="margin-top: 15px;">
        <thead>
        <caption style="font-size: 22px"></caption>
        <th>#</th>
        <th>{{  trans('admin.personnel')  }}</th>
        <th>{{  trans('admin.motif')  }}</th>
        <th>{{  trans('admin.montant')  }}</th>
        <th>{{  trans('admin.date')  }}</th>
        <th>{{  trans('admin.ligne')  }}</th>
        </thead>
        <tbody>
        <?php $i=1; $totalSum = 0; $rSum =0 ?>
        @foreach($lineData as $c)
        <?php $totalSum += $c->total_amount; $rSum += $c->realized_amount ?>

        <tr>
            <td>{{$i++}}</td>
            <td>{{$c->nom_personnel}}</td>
            <td><strong> {{ $c->motif}} </a></strong></td>
            <td>{{$c->montant}} @lang('inscription.devise')</td>
            <td>{{$c->date)}}</td>
            <td>{{$c->ligne)}}</td>
<!--            <td>{{number_format($c->total_amount - $c->realized_amount, 0, '.', ' ')}} @lang('inscription.devise')</td>
-->
        </tr>
        @endforeach
      <!--  <tr>
            <td colspan="3">{{ trans('admin.total') }}</td>
            <td>{{number_format($totalSum, 0, '.', ' ')}} @lang('inscription.devise')</td>
            <td>{{number_format($rSum, 0, '.', ' ')}} @lang('inscription.devise')</td>
            <td>{{number_format($totalSum - $rSum, 0, '.', ' ')}} @lang('inscription.devise')</td>
        </tr>-->
        </tbody>
    </table>

    <h5>Date: {{ date('d-m-Y') }}</h5>

    <!--    -->
    @endsection

    @section('breadcrumb')
    <li><strong><a href="{{url('comptabilite')}}">@lang('admin.comptabilite')</a></strong></li>
    <li><strong><a href="{{url('comptabilite/ligne')}}">@lang('admin.ligne_budget')</a></strong></li>
    <li class="active"><strong>{{$titre}}</strong></li>
    @endsection