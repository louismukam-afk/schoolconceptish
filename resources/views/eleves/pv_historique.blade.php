@extends('skeleton')

@section('sub-content')

<div class="col-md-12">
    <div class="btn-toolbar" style="margin-bottom:15px;">
        <a href="{{ url('/eleves') }}" class="btn btn-default">
            <span class="glyphicon glyphicon-home"></span> Accueil eleves
        </a>
        <a href="{{ url('/eleves/notes/matieres/show?id='.$id_classe.'&cycle='.$id_cycle.'&nniveau='.$id_nniveau) }}" class="btn btn-default">
            <span class="glyphicon glyphicon-list"></span> Page PV
        </a>
        <a href="javascript:history.back();" class="btn btn-info">
            <span class="glyphicon glyphicon-arrow-left"></span> Retour
        </a>
    </div>

    {{-- ==========================================================
         TITRE
         ========================================================== --}}

    <div class="panel panel-primary">

        <div class="panel-heading">

            <strong>
                Historique des procès-verbaux
            </strong>

        </div>

        <div class="panel-body">

            <div class="row">

                <div class="col-md-4">

                    <strong>Spécialité :</strong>

                    {{ isset($classe->nom)
                        ? $classe->nom
                        : '-'
                    }}

                </div>


                <div class="col-md-4">

                    <strong>Cycle :</strong>

                    {{ isset($cycle->nom)
                        ? $cycle->nom
                        : '-'
                    }}

                </div>


                <div class="col-md-4">

                    <strong>Niveau :</strong>

                    {{ isset($nniveau->nom)
                        ? $nniveau->nom
                        : '-'
                    }}

                </div>

            </div>

        </div>

    </div>



    {{-- ==========================================================
         FILTRES
         ========================================================== --}}

    <div class="panel panel-default">

        <div class="panel-heading">

            <strong>
                Rechercher les PV
            </strong>

        </div>

        <div class="panel-body">


            <form
                method="GET"
                action="{{ url(
                    '/eleves/notes/pv/historique/'
                    .$id_classe
                ) }}">


                <input
                    type="hidden"
                    name="cycle"
                    value="{{ $id_cycle }}">


                <input
                    type="hidden"
                    name="nniveau"
                    value="{{ $id_nniveau }}">


                <div class="row">


                    {{-- ANNEE --}}

                    <div class="col-md-3 form-group">

                        <label>
                            Année académique
                        </label>


                        <select
                            name="annee_academique"
                            class="form-control">

                            <option value="">
                                Toutes
                            </option>


                            @foreach($annees as $a)

                                <option
                                    value="{{ $a->annee_academique }}"

                                    {{ Request::get(
                                        'annee_academique'
                                    ) == $a->annee_academique
                                        ? 'selected'
                                        : ''
                                    }}>

                                    {{ $a->annee_academique }}

                                </option>

                            @endforeach

                        </select>

                    </div>



                    {{-- Formats --}}

                    <div class="col-md-3 form-group">

                        <label>
                            Format
                        </label>


                        <select
                            name="type_pv"
                            class="form-control">

                            <option value="">
                                Tous
                            </option>


                            <option
                                value="bts"
                                {{ Request::get(
                                    'type_pv'
                                ) == 'bts'
                                    ? 'selected'
                                    : ''
                                }}>

                                BTS

                            </option>


                            <option
                                value="hnd"
                                {{ Request::get(
                                    'type_pv'
                                ) == 'hnd'
                                    ? 'selected'
                                    : ''
                                }}>

                                HND

                            </option>


                            <option
                                value="licence_soa"
                                {{ Request::get(
                                    'type_pv'
                                ) == 'licence_soa'
                                    ? 'selected'
                                    : ''
                                }}>

                                Licence SOA

                            </option>


                            <option
                                value="licence_bamenda"
                                {{ Request::get(
                                    'type_pv'
                                ) == 'licence_bamenda'
                                    ? 'selected'
                                    : ''
                                }}>

                                Licence Bamenda

                            </option>


                            <option
                                value="licence_ebolowa"
                                {{ Request::get(
                                    'type_pv'
                                ) == 'licence_ebolowa'
                                    ? 'selected'
                                    : ''
                                }}>

                                Licence Ebolowa

                            </option>


                            <option
                                value="licence_ngaoundere"
                                {{ Request::get(
                                    'type_pv'
                                ) == 'licence_ngaoundere'
                                    ? 'selected'
                                    : ''
                                }}>

                                Licence Ngaoundere

                            </option>

                        </select>

                    </div>



                    {{-- CATEGORIE --}}

                    <div class="col-md-3 form-group">

                        <label>
                            Type de PV
                        </label>


                        <select
                            name="categorie"
                            id="categorie"
                            class="form-control">

                            <option value="">
                                Tous
                            </option>


                            <option
                                value="semestriel"

                                {{ Request::get(
                                    'categorie'
                                ) == 'semestriel'
                                    ? 'selected'
                                    : ''
                                }}>

                                Semestriel

                            </option>


                            <option
                                value="annuel"

                                {{ Request::get(
                                    'categorie'
                                ) == 'annuel'
                                    ? 'selected'
                                    : ''
                                }}>

                                Bilan annuel

                            </option>

                        </select>

                    </div>



                    {{-- SEMESTRE --}}

                    <div class="col-md-3 form-group">

                        <label>
                            Semestre
                        </label>


                        <select
                            name="semestre"
                            class="form-control">

                            <option value="">
                                Tous
                            </option>


                            <option
                                value="1"

                                {{ Request::get(
                                    'semestre'
                                ) == '1'
                                    ? 'selected'
                                    : ''
                                }}>

                                Semestre 1

                            </option>


                            <option
                                value="2"

                                {{ Request::get(
                                    'semestre'
                                ) == '2'
                                    ? 'selected'
                                    : ''
                                }}>

                                Semestre 2

                            </option>

                        </select>

                    </div>


                </div>



                {{-- ==================================================
                     PERIODE
                     ================================================== --}}

                <div class="row">


                    <div class="col-md-4 form-group">

                        <label>
                            Date début
                        </label>

                        <input
                            type="date"
                            name="date_debut"
                            value="{{ Request::get(
                                'date_debut'
                            ) }}"
                            class="form-control">

                    </div>



                    <div class="col-md-4 form-group">

                        <label>
                            Date fin
                        </label>

                        <input
                            type="date"
                            name="date_fin"
                            value="{{ Request::get(
                                'date_fin'
                            ) }}"
                            class="form-control">

                    </div>


                    <div
                        class="col-md-4"
                        style="padding-top:25px;">


                        <button
                            type="submit"
                            class="btn btn-primary">

                            <span
                                class="glyphicon glyphicon-search">
                            </span>

                            Rechercher

                        </button>


                        <a
                            href="{{ url(
                                '/eleves/notes/pv/historique/'
                                .$id_classe
                                .'?cycle='.$id_cycle
                                .'&nniveau='.$id_nniveau
                            ) }}"
                            class="btn btn-default">

                            Réinitialiser

                        </a>


                    </div>

                </div>


            </form>

        </div>

    </div>



    {{-- ==========================================================
         RESULTATS
         ========================================================== --}}

    <div class="panel panel-success">

        <div class="panel-heading">

            <strong>
                PV enregistrés
            </strong>

            <span class="badge">
                {{ count($pvs) }}
            </span>

        </div>


        <div class="panel-body">


            <div class="table-responsive">


                <table
                    class="
                        table
                        table-bordered
                        table-striped
                        table-hover
                    ">


                    <thead>

                    <tr>

                        <th>#</th>

                        <th>
                            Année académique
                        </th>

                        <th>
                            Type
                        </th>

                        <th>
                            Format
                        </th>

                        <th>
                            Période
                        </th>

                        <th>
                            Étudiants
                        </th>

                        <th>
                            Généré le
                        </th>

                        <th>
                            Statut
                        </th>

                        <th>
                            Action
                        </th>

                    </tr>

                    </thead>


                    <tbody>


                    <?php $i = 1; ?>


                    @forelse($pvs as $pv)


                        <tr>


                            <td>
                                {{ $i++ }}
                            </td>


                            <td>
                                {{ $pv->annee_academique }}
                            </td>


                            {{-- TYPE --}}

                            <td>

                                @if($pv->semestre == 1)

                                    <span
                                        class="label label-info">

                                        Semestre 1

                                    </span>


                                @elseif($pv->semestre == 2)

                                    <span
                                        class="label label-primary">

                                        Semestre 2

                                    </span>


                                @elseif($pv->semestre == 9 || $pv->semestre == 'annuel')

                                    <span
                                        class="label label-success">

                                        Bilan annuel

                                    </span>


                                @elseif($pv->semestre == 'rattrapage_1')

                                    <span
                                        class="label label-warning">

                                        Rattrapage 1

                                    </span>


                                @elseif($pv->semestre == 'rattrapage_2')

                                    <span
                                        class="label label-warning">

                                        Rattrapage 2

                                    </span>


                                @else

                                    <span
                                        class="label label-default">

                                        {{ $pv->semestre }}

                                    </span>

                                @endif

                            </td>



                            {{-- FORMAT --}}

                            <td>

                                <?php
                                    $pvTypeLabels = array(
                                        'bts' => 'BTS',
                                        'hnd' => 'HND',
                                        'licence_soa' => 'Licence SOA',
                                        'licence_bamenda' => 'Licence Bamenda',
                                        'licence_ebolowa' => 'Licence Ebolowa',
                                        'licence_ngaoundere' => 'Licence Ngaoundere'
                                    );
                                ?>

                                <strong>
                                    {{ isset($pvTypeLabels[$pv->type_pv])
                                        ? $pvTypeLabels[$pv->type_pv]
                                        : strtoupper($pv->type_pv)
                                    }}
                                </strong>

                            </td>



                            {{-- PERIODE --}}

                            <td>

                                @if(
                                    $pv->date_debut
                                    ||
                                    $pv->date_fin
                                )

                                    {{ $pv->date_debut
                                        ? $pv->date_debut
                                            ->format('d/m/Y')
                                        : '...'
                                    }}

                                    <br>

                                    <small>
                                        au
                                    </small>

                                    <br>

                                    {{ $pv->date_fin
                                        ? $pv->date_fin
                                            ->format('d/m/Y')
                                        : '...'
                                    }}


                                @else

                                    -

                                @endif

                            </td>



                            {{-- ETUDIANTS --}}

                            <td>

                                {{ $pv->nombre_etudiants }}

                            </td>



                            {{-- DATE GENERATION --}}

                            <td>

                                @if($pv->date_generation)

                                    {{ $pv->date_generation
                                        ->format(
                                            'd/m/Y H:i'
                                        )
                                    }}

                                @endif

                            </td>



                            {{-- STATUT --}}

                            <td>

                                @if($pv->statut == 'valide')

                                    <span
                                        class="label label-success">

                                        Validé

                                    </span>


                                @elseif($pv->statut == 'archive')

                                    <span
                                        class="label label-default">

                                        Archivé

                                    </span>


                                @else

                                    <span
                                        class="label label-warning">

                                        {{ ucfirst(
                                            $pv->statut
                                        ) }}

                                    </span>

                                @endif

                            </td>



                            {{-- ACTION --}}

                            <td>

                                <a
                                    href="{{ url(
                                        '/eleves/notes/pv/visualiser/'
                                        .$pv->id
                                    ) }}"
                                    class="
                                        btn
                                        btn-xs
                                        btn-primary
                                    ">

                                    <span
                                        class="
                                            glyphicon
                                            glyphicon-eye-open
                                        ">
                                    </span>

                                    Visualiser

                                </a>

                            </td>


                        </tr>


                    @empty


                        <tr>

                            <td
                                colspan="9"
                                class="text-center">

                                Aucun PV enregistré
                                pour cette période.

                            </td>

                        </tr>


                    @endforelse


                    </tbody>


                </table>


            </div>

        </div>

    </div>


    {{-- RETOUR --}}

    <a
        href="{{ url(
            '/eleves/notes/matieres/show'
            .'?id='.$id_classe
            .'&cycle='.$id_cycle
            .'&nniveau='.$id_nniveau
        ) }}"
        class="btn btn-default">

        <span
            class="glyphicon glyphicon-arrow-left">
        </span>

        Retour

    </a>


</div>

@endsection
