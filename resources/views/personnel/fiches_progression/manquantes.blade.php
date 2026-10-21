@extends('skeleton')

@section('sub-content')
    <div class="col-md-12">
        <div class="panel panel-warning">
            <div class="panel-heading"><strong>Cours sans fiches de progression</strong></div>
            <div class="panel-body">
                <style>
                    @page { margin: 0; }
                    @media print {
                        body { margin: 8mm !important; padding: 0 !important; background: #fff !important; }
                        .no-print,
                        .breadcrumb,
                        .big-title,
                        .navbar,
                        .navbar-inverse,
                        .navbar-side,
                        .sidebar-collapse,
                        .back-button,
                        .nom-etab,
                        .logout-spn,
                        .annee,
                        .langage,
                        .action,
                        hr,
                        .panel-heading { display: none !important; }
                        #wrapper, #page-wrapper, #page-inner {
                            margin: 0 !important;
                            padding: 0 !important;
                            width: 100% !important;
                            min-height: 0 !important;
                        }
                        .col-md-12, .col-lg-12 {
                            float: none !important;
                            width: 100% !important;
                            padding: 0 !important;
                        }
                        .panel, .panel-body { border: 0 !important; box-shadow: none !important; }
                        .print-campus-header { display: block !important; }
                        table { font-size: 11px; }
                        a[href]:after { content: ""; }
                    }
                </style>
                <div class="btn-group no-print" style="margin-bottom:15px">
                    <a href="{{ route('fiches_progression_enseignants', array('annee_scolaire'=>$selectedYear)) }}" class="btn btn-default">Flux par enseignant</a>
                    <a href="{{ route('fiches_progression_par_specialite', array('annee_scolaire'=>$selectedYear, 'id_cycle'=>$selectedCycle, 'id_nniveau'=>$selectedNniveau, 'id_niveau'=>$selectedNiveau, 'id_classe'=>$selectedClasse)) }}" class="btn btn-info">Cours avec fiches</a>
                    <button type="button" onclick="window.print()" class="btn btn-primary">
                        <span class="glyphicon glyphicon-print"></span> Imprimer
                    </button>
                </div>
                <div class="no-print">
                    @include('personnel.fiches_progression.partials_filtres_specialite')
                </div>
                <div class="print-campus-header text-center" style="display:none;margin-bottom:12px">
                    @if($campus_actif)
                        <h3 style="margin:0">{{ $campus_actif->nom }} @if($campus_actif->sigle) - {{ $campus_actif->sigle }} @endif</h3>
                        <div>
                            @if($campus_actif->localisation) {{ $campus_actif->localisation }} @endif
                            @if($campus_actif->ville) {{ $campus_actif->ville }} @endif
                        </div>
                        <div>
                            @if($campus_actif->telephone) Tel: {{ $campus_actif->telephone }} @endif
                            @if($campus_actif->email) | Email: {{ $campus_actif->email }} @endif
                        </div>
                    @endif
                </div>
                <h4 class="visible-print-block text-center">Cours sans fiches de progression - {{ $selectedYear }}</h4>
                @include('personnel.fiches_progression.partials_table_cours', array('avecFiche'=>false))
            </div>
        </div>
    </div>
@endsection

@section('breadcrumb')
    <li><strong><a href="{{ url('/personnel') }}">@lang('menu.gestion_personnel')</a></strong></li>
    <li class="active"><strong>Cours sans fiches</strong></li>
@endsection
