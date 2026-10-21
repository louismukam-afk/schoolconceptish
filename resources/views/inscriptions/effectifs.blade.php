@extends('skeleton')

@section('sub-content')
    <div class="col-md-12">
        <style>
            .effectifs-header {
                margin-bottom: 20px;
            }
            .effectifs-title {
                margin-top: 0;
                font-weight: bold;
            }
            .effectifs-cycle-title {
                margin-top: 25px;
                padding: 8px 10px;
                background: #f5f5f5;
                border: 1px solid #ddd;
                font-weight: bold;
                text-transform: uppercase;
            }
            .effectifs-summary {
                margin-top: 15px;
                font-size: 16px;
                font-weight: bold;
            }
            @media print {
                .no-print,
                .btn,
                form,
                .panel,
                .modal,
                .breadcrumb,
                .navbar,
                .navbar-fixed-top,
                .navbar-side,
                .sidebar,
                .sidebar-collapse,
                .footer,
                .action,
                .page-header,
                #main-menu {
                    display: none !important;
                }
                #wrapper,
                #page-wrapper,
                #page-inner {
                    margin: 0 !important;
                    padding: 0 !important;
                    width: 100% !important;
                    min-height: 0 !important;
                    background: #fff !important;
                }
                body {
                    background: #fff !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }
                .col-md-12 {
                    float: none !important;
                    width: 100% !important;
                    padding: 0 !important;
                }
                .table {
                    font-size: 12px;
                }
                .effectifs-cycle-title {
                    page-break-after: avoid;
                }
            }
        </style>

        <div class="effectifs-header">
            <div class="pull-right no-print">
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="fa fa-print"></i> Imprimer
                </button>
            </div>

            <h3 class="effectifs-title">Effectifs des inscriptions</h3>
            <p>
                <strong>Annee academique :</strong> {{ $annee_scolaire }}
                <br>
                <strong>Campus :</strong>
                @if($campus)
                    {{ $campus->nom }} @if($campus->sigle) - {{ $campus->sigle }} @endif
                @else
                    Aucun campus actif
                @endif
            </p>
        </div>

        <div class="panel panel-default no-print">
            <div class="panel-body">
                <form class="form-inline" method="get" action="{{ route('inscriptions_effectifs') }}">
                    <div class="form-group">
                        <label for="annee_scolaire">Annee academique</label>
                        <input type="text" id="annee_scolaire" name="annee_scolaire" class="form-control" value="{{ $annee_scolaire }}" placeholder="2026-2027">
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-search"></i> Afficher
                    </button>
                </form>
            </div>
        </div>

        @if(count($effectifsParCycle) == 0)
            <div class="alert alert-warning">
                Aucun effectif trouve pour cette annee academique et ce campus.
            </div>
        @else
            @foreach($effectifsParCycle as $cycle => $group)
                <div class="effectifs-cycle-title">
                    Cycle : {{ $cycle }} - Total : {{ $group['total'] }}
                </div>

                <table class="table table-bordered table-condensed table-striped">
                    <thead>
                        <tr>
                            <th style="width: 40px">#</th>
                            <th>Filiere</th>
                            <th>Specialite</th>
                            <th style="width: 130px">Niveau</th>
                            <th style="width: 100px" class="text-right">Effectif</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                        @foreach($group['rows'] as $row)
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td>{{ $row->filiere ? $row->filiere : 'Sans filiere' }}</td>
                                <td>
                                    @if($row->code_specialite)
                                        {{ $row->code_specialite }} -
                                    @endif
                                    {{ $row->specialite ? $row->specialite : 'Sans specialite' }}
                                </td>
                                <td>{{ $row->niveau ? $row->niveau : 'Sans niveau' }}</td>
                                <td class="text-right"><strong>{{ $row->effectif }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-right">Total {{ $cycle }}</th>
                            <th class="text-right">{{ $group['total'] }}</th>
                        </tr>
                    </tfoot>
                </table>
            @endforeach

            <div class="alert alert-info effectifs-summary">
                Total general : {{ $total }}
            </div>
        @endif
    </div>
@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('inscriptions')}}">@lang('menu.gestion_inscriptions')</a></strong></li>
    <li class="active"><strong>{{$titre}}</strong></li>
@endsection
