@extends('skeleton')
@section('sub-content')
    <div class="col-md-10">
        @include('perso.functions')
        @include('perso.header3', ['campus' => isset($campus) ? $campus : null])
        <div class="row">
            <div class="col-md-12">
                <a href="#" class="btn btn-warning btn-lg" onclick="window.print()"><span class="glyphicon glyphicon-print"></span> {{trans('inscription.imprimer')}}</a>
            </div>
        </div>
        <link rel="stylesheet" media="print" href="{{URL::asset('css/print_table.css')}}">

        <?php
            $dateDebut = $line->date_debut ? date('d-m-Y', strtotime($line->date_debut)) : '';
            $dateFin = $line->date_fin ? date('d-m-Y', strtotime($line->date_fin)) : '';
        ?>
        <h3>@lang('admin.ligne_budget'): <strong>{{ $line->libelle_ligne }}</strong> || {{ $dateDebut }} - {{ $dateFin }}</h3>
        <table class="table table-striped table-bordered table-condensed table-inverse" style="margin-top: 15px;">
            <thead>
            <caption style="font-size: 22px"></caption>
            <th>#</th>
            <th>{{  trans('admin.numero_compte')  }}</th>
            <th>{{  trans('admin.libelle')  }}</th>
            <th>{{  trans('admin.total')  }}</th>

            </thead>
            <tbody>
            <?php $i=1; $totalSum = 0; $rSum =0 ?>
            @foreach($line->donneesLigneBudgetaire as $c)
            <?php $totalSum += $c->montant; ?>

                <tr>
                    <td>{{$i++}}</td>
                    <td>{{$c->categorieBudgetaire->numero_compte}}</td>
                    <td><strong>{{ $c->categorieBudgetaire->libelle }}</strong></td>
                    <td>{{number_format($c->montant, 0, '.', ' ')}} @lang('inscription.devise')</td>
                    <td>
                        <div class="btn-group">
                            <a href="#" class=" btn btn-primary dropdown-toggle" data-toggle="dropdown">Actions <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li><a style="color: white" href="{{ URL::to('/comptabilite/categorie/report/').'/'.$c->id }}"   class="btn-warning"><span ></span> Afficher les dépenses de la catégorie</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @endforeach
            <tr>
                <td colspan="3">{{ trans('admin.total') }}</td>
                <td>{{number_format($totalSum, 0, '.', ' ')}} @lang('inscription.devise')</td>
            </tr>
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
