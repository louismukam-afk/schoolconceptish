@extends('skeleton')

@section('sub-content')
    @include('perso.functions')

    <a href="#" class="btn btn-lg btn-warning" onclick="window.print()">
        <span class="glyphicon glyphicon-print"></span> @lang('inscription.imprimer')
    </a>

    @include('perso.header')
    <link rel="stylesheet" href="{{ URL::asset('css/note_matiere.css') }}">

    <?php
    // Pourcentage du CC (envoyé par le controller avec la session_exam)
    // Exemple : cc1 = 15, cc2 = 10, etc.
    // Si rien n’est passé, on met 0 pour éviter les divisions par 0
    $pourcentageCc = isset($pourcentage) ? (float) $pourcentage : 0.0;
    $pourcentageTpe = isset($pourcentage_tpe) ? (float) $pourcentage_tpe : 0.0;
    $afficherTpe = isset($hasTPE) ? (bool) $hasTPE : ($pourcentageTpe > 0);
    ?>

    <div class="titre">
        <p style="font-size: 19px">
            <strong>
                Contrôle continu de {{ $matiere->nom }}
                @if($pourcentageCc > 0)
                    ({{ $pourcentageCc }} %)
                @endif
            </strong>
        </p>
        <strong class="traduction">
            Continuous assessment of {{ $matiere->nom_anglais }}
            @if($pourcentageCc > 0)
                ({{ $pourcentageCc }} %)
            @endif
        </strong>
    </div>

    <div class="classe">
        <div class="specialite">
            <p>
                <strong>Spécialité : {{ $classe->nom }} - Niveau {{ $nniveau->nom }}</strong>
            </p>
            <strong class="traduction">
                Speciality : {{ $classe->nom_anglais }} - Level {{ $nniveau->nom }}
            </strong>
        </div>

        <div class="filiere">
            <p>
                <strong>Filière: {{ \gesecole\niveau::find($classe->id_niveau)->nom }}</strong>
            </p>
            <strong class="traduction">
                Path : {{ \gesecole\niveau::find($classe->id_niveau)->nom_anglais }}
            </strong>
        </div>
    </div>

    <div class="table">
        <table class="table table-bordered table-striped table-condensed table-inverse" style="margin-top: 5px;">
            <thead>
            <th class="number-column">#</th>
            <th class="mat-column">Matricule/Matricle</th>
            <th>Nom/Name</th>
            <th class="note-column">Note /20</th>
            @if($afficherTpe)
                <th class="note-column">TPE /20</th>
            @endif
            </thead>
            <tbody>
            <?php $i = 1; ?>
            @foreach($eleves as $e)
                <tr>
                    <td class="number-column">{{ $i++ }}</td>
                    <td class="mat-column">{{ $e->matricule }}</td>
                    <td>{{ $e->nom.' '.$e->prenom }}</td>

                    <td class="note-column">
                        <?php
                        $noteStockee = null;

                        if (isset($notes)) {
                            foreach ($notes as $n) {
                                if ($n->id_eleve == $e->id) {
                                    $noteStockee = $n->note;
                                    break;
                                }
                            }
                        }

                        if (!is_null($noteStockee) && $pourcentageCc > 0) {
                            // note_stockée = saisie * (pourcentage / 20)
                            // => saisie (sur 20) = note_stockée * 20 / pourcentage
                            $noteAffichee = round(($noteStockee * 20) / $pourcentageCc, 2);
                            echo $noteAffichee;
                        } elseif (!is_null($noteStockee)) {
                            // Cas dégradé si pas de pourcentage (on affiche la note brute)
                            echo round($noteStockee, 2);
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                    @if($afficherTpe)
                        <td class="note-column">
                            <?php
                            $noteTpeStockee = null;

                            if (isset($notes_tpe)) {
                                foreach ($notes_tpe as $nTpe) {
                                    if ($nTpe->id_eleve == $e->id) {
                                        $noteTpeStockee = $nTpe->note;
                                        break;
                                    }
                                }
                            }

                            if (!is_null($noteTpeStockee) && $pourcentageTpe > 0) {
                                echo round(($noteTpeStockee * 20) / $pourcentageTpe, 2);
                            } elseif (!is_null($noteTpeStockee)) {
                                echo round($noteTpeStockee, 2);
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                    @endif
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('breadcrumb')
    <li>
        <strong><a href="{{ url('eleves') }}">@lang('menu.gestion_notes')</a></strong>
    </li>
    <li>
        <strong><a href="{{ url('eleves/notes') }}">Notes</a></strong>
    </li>
    <li>
        <a href="{{ url('eleves/notes/show') }}">
            <strong>@lang('admin.choix_classe')</strong>
        </a>
    </li>
    <li>
        <a href="{{ url('eleves/notes/matieres/show').'?id='.$classe->id.'&cycle='.$id_cycle.'&nniveau='.$nniveau->id }}">
            <strong>@lang('admin.titre_matiere')</strong>
        </a>
    </li>
    <li class="active">
        <strong>{{ isset($titre) ? $titre : 'Controle continu' }}</strong>
    </li>
@endsection
