@extends('skeleton')

@section('sub-content')
    @include('perso.functions')

    <style>
        .note-column { text-align:center; }
        .gris { background:#e0e0e0; color:#666; }
    </style>

    <style media="print">
        #note-excel, .dt-buttons,
        #note-excel_filter,
        #note-excel_info,
        #note-excel_paginate { display:none; }
    </style>
    <style>
        .table-ecran { display:block; }
        .table-print { display:block; }
        .note-column { text-align:center; }
        .gris { background:#e0e0e0; color:#777; }
    </style>

    <style media="print">
        body * { visibility:hidden; }

        .table-print,
        .table-print * {
            visibility:visible;
        }


        .table-print {
            position:absolute;
            top:0;
            left:0;
            width:100%;
        }

        .table-ecran { display:none; }
        .btn, .dt-buttons { display:none !important; }
    </style>

    <a href="#" class="btn btn-warning" onclick="window.print()">
        <i class="glyphicon glyphicon-print"></i> Imprimer
    </a>
<center>
    <div class="titre">
        <strong>PROCÈS VERBAL Annuel</strong><br>
        Année académique : {{ $annee }}
    </div>

    <div class="classe">
        Spécialité : {{ $classe->nom }} — Niveau {{ $nniveau->nom }}
    </div>
</center>
    <?php
    /* ================= PRÉPARATION ================= */

    // Total des crédits prévus
    $creditPrevuTotal = 0;
    foreach ($matieres as $m) {
        $creditPrevuTotal += $m->credit;
    }

    // Détection des semestres par matière
    $matiereSemestres = array();
    foreach ($matieres as $m) {
        $hasS1 = false;
        $hasS2 = false;

        foreach ($PV_s1_map as $pv) {
            foreach ($pv->matieres as $x) {
                if ($x->id_matiere == $m->id) {
                    $hasS1 = true;
                    break 2;
                }
            }
        }

        foreach ($PV_s2_map as $pv) {
            foreach ($pv->matieres as $x) {
                if ($x->id_matiere == $m->id) {
                    $hasS2 = true;
                    break 2;
                }
            }
        }

        $matiereSemestres[$m->id] = array('s1'=>$hasS1,'s2'=>$hasS2);
    }

    // Tableaux pour le classement
    $classementS1 = array();
    $classementS2 = array();
    $classementAnnuel = array();

    // Fonction de calcul des rangs
    function calculRang($tab) {
        $tab = array_filter($tab, function($v){ return $v !== null; });
        arsort($tab);
        $r = array(); $rang = 0; $pos = 0; $prev = null;
        foreach($tab as $id=>$v) {
            $pos++;
            if($prev === null || $v < $prev) { $rang = $pos; }
            $r[$id] = $rang;
            $prev = $v;
        }
        return $r;
    }

    // ================= CALCUL DES MOYENNES =================
    foreach($eleves as $e){
        $pvS1 = isset($PV_s1_map[$e->id]) ? $PV_s1_map[$e->id] : null;
        $pvS2 = isset($PV_s2_map[$e->id]) ? $PV_s2_map[$e->id] : null;

        $sumS1 = $countS1 = $creditS1 = 0;
        $sumS2 = $countS2 = $creditS2 = 0;

        foreach($matieres as $m){
            // S1
            $totalS1 = 0;
            if ($matiereSemestres[$m->id]['s1']) {
                $trouve = false;
                if ($pvS1) {
                    foreach($pvS1->matieres as $x){
                        if($x->id_matiere == $m->id){
                            $trouve = true;
                            $s = $c = 0;
                            if($x->cc !== null){$s += $x->cc; $c++;}
                            if($x->sn !== null){$s += $x->sn; $c++;}
                            if($x->tpe !== null){$s += $x->tpe; $c++;}
                            $totalS1 = $c>0 ? round($s/$c,2) : 0;
                        }
                    }
                }
                if(!$trouve) $totalS1 = 0;
                $sumS1 += $totalS1; $countS1++;
                if($totalS1 >= 10) $creditS1 += $m->credit;
            }

            // S2
            $totalS2 = 0;
            if ($matiereSemestres[$m->id]['s2']) {
                $trouve = false;
                if ($pvS2) {
                    foreach($pvS2->matieres as $x){
                        if($x->id_matiere == $m->id){
                            $trouve = true;
                            $s = $c = 0;
                            if($x->cc !== null){$s += $x->cc; $c++;}
                            if($x->sn !== null){$s += $x->sn; $c++;}
                            if($x->tpe !== null){$s += $x->tpe; $c++;}
                            $totalS2 = $c>0 ? round($s/$c,2) : 0;
                        }
                    }
                }
                if(!$trouve) $totalS2 = 0;
                $sumS2 += $totalS2; $countS2++;
                if($totalS2 >= 10) $creditS2 += $m->credit;
            }
        }

        $moyS1 = $countS1 ? round($sumS1/$countS1,2) : null;
        $moyS2 = $countS2 ? round($sumS2/$countS2,2) : null;

        $creditTotal = $creditS1 + $creditS2;
        $moyAnn = ($creditTotal == $creditPrevuTotal) ? round(($moyS1 + $moyS2)/2,2) : null;

        $classementS1[$e->id] = $moyS1;
        $classementS2[$e->id] = $moyS2;
        $classementAnnuel[$e->id] = $moyAnn;
    }

    // Calcul des rangs avant le rendu
    $rS1 = calculRang($classementS1);
    $rS2 = calculRang($classementS2);
    $rAnn = calculRang($classementAnnuel);
    ?>

    <div class="table table-excel">
        <table class="table table-bordered table-striped" id="note-excel">

            <thead>
            <tr>
                <th>#</th>
                <th>Matricule</th>
                <th>Nom & Prénom</th>

                @foreach($matieres as $m)
                    @if($matiereSemestres[$m->id]['s1'])<th>{{ $m->nom }} S1</th>@endif
                    @if($matiereSemestres[$m->id]['s2'])<th>{{ $m->nom }} S2</th>@endif
                @endforeach

                <th>Moy S1</th>
                <th>Rang S1</th>
                <th>Cr S1</th>

                <th>Moy S2</th>
                <th>Rang S2</th>
                <th>Cr S2</th>

                <th>Moy Annuelle</th>
                <th>Cr validés</th>
                <th>Cr prévus</th>
                <th>Décision</th>
                <th>Matières non validées</th>
                <th>Rang Annuel</th>
            </tr>
            </thead>

            <tbody>
            <?php $i=1; ?>
            @foreach($eleves as $e)

                <?php
                $pvS1 = isset($PV_s1_map[$e->id]) ? $PV_s1_map[$e->id] : null;
                $pvS2 = isset($PV_s2_map[$e->id]) ? $PV_s2_map[$e->id] : null;

                $sumS1=$countS1=$creditS1=0;
                $sumS2=$countS2=$creditS2=0;
                $nonValide=array();
                ?>

                <tr>
                    <td>{{ $i++ }}</td>
                    <td>{{ $e->matricule }}</td>
                    <td>{{ $e->nom }} {{ $e->prenom }}</td>

                    @foreach($matieres as $m)
                        <?php
                        $totalS1 = 0; $totalS2 = 0;

                        // S1
                        if ($matiereSemestres[$m->id]['s1']) {
                            $trouve = false;
                            if ($pvS1) {
                                foreach ($pvS1->matieres as $x){
                                    if($x->id_matiere==$m->id){
                                        $trouve=true;
                                        $s=$c=0;
                                        if($x->cc!==null){$s+=$x->cc;$c++;}
                                        if($x->sn!==null){$s+=$x->sn;$c++;}
                                        if($x->tpe!==null){$s+=$x->tpe;$c++;}
                                        $totalS1 = $c>0 ? round($s/$c,2) : 0;
                                    }
                                }
                            }
                            if(!$trouve) $totalS1 = 0;
                            $sumS1+=$totalS1; $countS1++;
                            if($totalS1<10) $nonValide[]=$m->nom.' (S1)'; else $creditS1+=$m->credit;
                        }

                        // S2
                        if ($matiereSemestres[$m->id]['s2']) {
                            $trouve = false;
                            if ($pvS2) {
                                foreach ($pvS2->matieres as $x){
                                    if($x->id_matiere==$m->id){
                                        $trouve=true;
                                        $s=$c=0;
                                        if($x->cc!==null){$s+=$x->cc;$c++;}
                                        if($x->sn!==null){$s+=$x->sn;$c++;}
                                        if($x->tpe!==null){$s+=$x->tpe;$c++;}
                                        $totalS2 = $c>0 ? round($s/$c,2) : 0;
                                    }
                                }
                            }
                            if(!$trouve) $totalS2=0;
                            $sumS2+=$totalS2; $countS2++;
                            if($totalS2<10) $nonValide[]=$m->nom.' (S2)'; else $creditS2+=$m->credit;
                        }
                        ?>
                        @if($matiereSemestres[$m->id]['s1'])<td>{{ number_format($totalS1,2) }}</td>@endif
                        @if($matiereSemestres[$m->id]['s2'])<td>{{ number_format($totalS2,2) }}</td>@endif
                    @endforeach

                    <?php
                    $moyS1 = $countS1?round($sumS1/$countS1,2):null;
                    $moyS2 = $countS2?round($sumS2/$countS2,2):null;
                    $creditTotal = $creditS1+$creditS2;
                    $moyAnn = ($creditTotal==$creditPrevuTotal)?round(($moyS1+$moyS2)/2,2):null;
                    $decision = ($creditTotal==$creditPrevuTotal)?'ADMIS':'AJOURNÉ';
                    ?>

                    <td>{{ $moyS1!==null?number_format($moyS1,2):'-' }}</td>
                    <td>{{ isset($rS1[$e->id])?$rS1[$e->id]:'-' }}</td>
                    <td>{{ $creditS1 }}</td>

                    <td>{{ $moyS2!==null?number_format($moyS2,2):'-' }}</td>
                    <td>{{ isset($rS2[$e->id])?$rS2[$e->id]:'-' }}</td>
                    <td>{{ $creditS2 }}</td>

                    <td>{{ $moyAnn!==null?number_format($moyAnn,2):'-' }}</td>
                    <td>{{ $creditTotal }}</td>
                    <td>{{ $creditPrevuTotal }}</td>
                    <td><strong>{{ $decision }}</strong></td>
                    <td>{{ implode(', ',array_unique($nonValide)) }}</td>
                    <td>{{ isset($rAnn[$e->id])?$rAnn[$e->id]:'-' }}</td>

                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <?php
    /* ================= PRÉPARATION ================= */
    $creditPrevuTotal = 0;
    foreach ($matieres as $m) {
        $creditPrevuTotal += $m->credit;
    }

    // Détection des semestres par matière
    $matiereSemestres = array();
    foreach ($matieres as $m) {
        $hasS1 = false;
        $hasS2 = false;

        foreach ($PV_s1_map as $pv) {
            foreach ($pv->matieres as $x) {
                if ($x->id_matiere == $m->id) {
                    $hasS1 = true;
                    break 2;
                }
            }
        }

        foreach ($PV_s2_map as $pv) {
            foreach ($pv->matieres as $x) {
                if ($x->id_matiere == $m->id) {
                    $hasS2 = true;
                    break 2;
                }
            }
        }

        $matiereSemestres[$m->id] = array('s1'=>$hasS1,'s2'=>$hasS2);
    }

    // Tableaux pour le classement
    $classementS1 = array();
    $classementS2 = array();
    $classementAnnuel = array();

    // Fonction pour calculer les rangs
    function calculRang1($tab){
        $tab = array_filter($tab,function($v){ return $v!==null; });
        arsort($tab);
        $r = array(); $rang = 0; $pos = 0; $prev = null;
        foreach($tab as $id=>$v){
            $pos++;
            if($prev===null || $v<$prev) $rang=$pos;
            $r[$id]=$rang;
            $prev=$v;
        }
        return $r;
    }

    // ================= CALCUL DES MOYENNES =================
    foreach($eleves as $e){
        $pvS1 = isset($PV_s1_map[$e->id]) ? $PV_s1_map[$e->id] : null;
        $pvS2 = isset($PV_s2_map[$e->id]) ? $PV_s2_map[$e->id] : null;

        $sumS1 = $countS1 = $creditS1 = 0;
        $sumS2 = $countS2 = $creditS2 = 0;

        foreach($matieres as $m){
            $totalS1 = 0; $totalS2 = 0;

            // S1
            $pm = null;
            if($pvS1) foreach($pvS1->matieres as $x){ if($x->id_matiere==$m->id) $pm=$x; }
            if(!$pm && $pvS2) foreach($pvS2->matieres as $x){ if($x->id_matiere==$m->id) $pm=$x; }

            if ($pm) {
                $cc = $pm->cc!==null ? $pm->cc : 0;
                $sn = $pm->sn!==null ? $pm->sn : 0;
                $tpe = $pm->tpe!==null ? $pm->tpe : 0;

                $denom = 0;
                if ($pm->cc!==null) $denom++;
                if ($pm->sn!==null) $denom++;
                if ($pm->tpe!==null) $denom++;

                $total = $denom > 0 ? round(($cc+$sn+$tpe)/$denom, 2) : 0;
            } else {
                $cc = $sn = $tpe = $total = 0;
            }

            // Attribution par semestre
            if($matiereSemestres[$m->id]['s1']){
                $totalS1 = $total;
                $sumS1 += $totalS1; $countS1++;
                if($totalS1>=10) $creditS1 += $m->credit;
            }
            if($matiereSemestres[$m->id]['s2']){
                $totalS2 = $total;
                $sumS2 += $totalS2; $countS2++;
                if($totalS2>=10) $creditS2 += $m->credit;
            }
        }

        $moyS1 = $countS1?round($sumS1/$countS1,2):null;
        $moyS2 = $countS2?round($sumS2/$countS2,2):null;
        $creditTotal = $creditS1+$creditS2;
        $moyAnn = ($creditTotal==$creditPrevuTotal)?round(($moyS1+$moyS2)/2,2):null;

        $classementS1[$e->id] = $moyS1;
        $classementS2[$e->id] = $moyS2;
        $classementAnnuel[$e->id] = $moyAnn;
    }

    // Calcul des rangs
    $rS1 = calculRang1($classementS1);
    $rS2 = calculRang1($classementS2);
    $rAnn = calculRang1($classementAnnuel);
    ?>

    <div class="table table-print">
        <center>
            <div class="titre">
                <h2>CENTRE DE FORMATION HINTEL</h2>
                <strong>PROCÈS VERBAL ANNUEL</strong><br>
                Année académique : {{ $annee }}
            </div>

            <div class="classe">
                Spécialité : {{ $classe->nom }} — Niveau {{ $nniveau->nom }}
            </div>
        </center>
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>#</th>
                <th>Matricule</th>
                <th>Nom</th>

                @foreach($matieres as $m)
                    <th colspan="4">{{ $m->nom }}<br><small>Cr {{ $m->credit }}</small></th>
                @endforeach

                <th>Moy S1</th>
                <th>Rang S1</th>
                <th>Cr S1</th>
                <th>Moy S2</th>
                <th>Rang S2</th>
                <th>Cr S2</th>
                <th>Moy Annuelle</th>
                <th>Cr validés</th>
                <th>Cr prévus</th>
                <th>Décision</th>
                <th>Matières non validées</th>
                <th>Rang Annuel</th>
            </tr>

            <tr>
                <th></th><th></th><th></th>
                @foreach($matieres as $m)
                    <th>CC</th><th>SN</th><th>TPE</th><th>Total</th>
                @endforeach
                <th></th><th></th><th></th><th></th><th></th><th></th>
                <th></th><th></th><th></th><th></th><th></th>
            </tr>
            </thead>

            <tbody>
            <?php $i=1; ?>
            @foreach($eleves as $e)
                <?php
                $pvS1 = isset($PV_s1_map[$e->id]) ? $PV_s1_map[$e->id] : null;
                $pvS2 = isset($PV_s2_map[$e->id]) ? $PV_s2_map[$e->id] : null;

                $sumS1=$countS1=$creditS1=0;
                $sumS2=$countS2=$creditS2=0;
                $nonValide=array();
                ?>

                <tr>
                    <td>{{ $i++ }}</td>
                    <td>{{ $e->matricule }}</td>
                    <td>{{ $e->nom }} {{ $e->prenom }}</td>

                    @foreach($matieres as $m)
                        <?php
                        $pm = null;
                        if($pvS1) foreach($pvS1->matieres as $x){ if($x->id_matiere==$m->id) $pm=$x; }
                        if(!$pm && $pvS2) foreach($pvS2->matieres as $x){ if($x->id_matiere==$m->id) $pm=$x; }

                        if($pm){
                            $cc = $pm->cc !== null ? $pm->cc : 0;
                            $sn = $pm->sn !== null ? $pm->sn : 0;
                            $tpe = $pm->tpe !== null ? $pm->tpe : 0;

                            $denom = ($pm->cc !== null ? 1 : 0) + ($pm->sn !== null ? 1 : 0) + ($pm->tpe !== null ? 1 : 0);
                            $total = $denom > 0 ? round(($cc + $sn + $tpe) / $denom, 2) : 0;
                        } else {
                            $cc = $sn = $tpe = 0;
                            $total = 0;
                        }


                        // Attribution par semestre
                        if($matiereSemestres[$m->id]['s1']){
                            $sumS1+=$total; $countS1++;
                            if($total>=10) $creditS1+=$m->credit; else $nonValide[]=$m->nom.'(S1)';
                        }
                        if($matiereSemestres[$m->id]['s2']){
                            $sumS2+=$total; $countS2++;
                            if($total>=10) $creditS2+=$m->credit; else $nonValide[]=$m->nom.'(S2)';
                        }
                        ?>

                        <td>{{ number_format($cc,2) }}</td>
                        <td>{{ number_format($sn,2) }}</td>
                        <td>{{ number_format($tpe,2) }}</td>
                        <td>{{ number_format($total,2) }}</td>
                    @endforeach

                    <?php
                    $moyS1 = $countS1?round($sumS1/$countS1,2):null;
                    $moyS2 = $countS2?round($sumS2/$countS2,2):null;
                    $creditTotal = $creditS1+$creditS2;
                    $moyAnn = ($creditTotal==$creditPrevuTotal)?round(($moyS1+$moyS2)/2,2):null;
                    $decision = ($creditTotal==$creditPrevuTotal)?'ADMIS':'AJOURNÉ';
                    ?>

                    <td>{{ $moyS1!==null?number_format($moyS1,2):'-' }}</td>
                    <td>{{ isset($rS1[$e->id])?$rS1[$e->id]:'-' }}</td>
                    <td>{{ $creditS1 }}</td>

                    <td>{{ $moyS2!==null?number_format($moyS2,2):'-' }}</td>
                    <td>{{ isset($rS2[$e->id])?$rS2[$e->id]:'-' }}</td>
                    <td>{{ $creditS2 }}</td>

                    <td>{{ $moyAnn!==null?number_format($moyAnn,2):'-' }}</td>
                    <td>{{ $creditTotal }}</td>
                    <td>{{ $creditPrevuTotal }}</td>
                    <td><strong>{{ $decision }}</strong></td>
                    <td>{{ implode(', ', array_unique($nonValide)) }}</td>
                    <td>{{ isset($rAnn[$e->id])?$rAnn[$e->id]:'-' }}</td>

                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <script>
        $('#note-excel').dataTable({
            dom: 'Bfrtip',
            pageLength: 2000,
            buttons: [{
                extend: 'excelHtml5',
                title: 'Bilan annuel - {{ $classe->nom }} {{ $nniveau->nom }}'
            }]
        });
    </script>
@endsection

@section('breadcrumb')
    <li><strong><a href="{{ url('eleves') }}">@lang('menu.gestion_notes')</a></strong></li>
    <li><strong><a href="{{ url('eleves/notes') }}">Notes</a></strong></li>

    <li><a href="{{ url('eleves/notes/show') }}"><strong>@lang('admin.choix_classe')</strong></a></li>
    <li>
        <a href="{{ url('eleves/notes/matieres/show').'?id='.$classe->id.'&nniveau='.$nniveau->id }}">
            <strong>@lang('admin.titre_matiere')</strong>
        </a>
    </li>
    <li class="active"><strong>{{ isset($titre) ? $titre : '' }}</strong></li>

@endsection