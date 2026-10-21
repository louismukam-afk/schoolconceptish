<?php $__env->startSection('sub-content'); ?>
    <?php echo $__env->make('perso.functions', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
    <?php
        $soaGrade = function ($note100) {
            $note20 = ((float) $note100) / 5;
            if ($note20 >= 18) return array('A+', 4);
            if ($note20 >= 16) return array('A', 3.7);
            if ($note20 >= 14) return array('B+', 3.3);
            if ($note20 >= 13) return array('B', 3);
            if ($note20 >= 12) return array('B-', 2.7);
            if ($note20 >= 11) return array('C+', 2.3);
            if ($note20 >= 10) return array('C', 2);
            if ($note20 >= 9) return array('C-', 1.7);
            if ($note20 >= 7) return array('D', 1.3);
            return array('F', 0);
        };
        $soaMention = function ($note100) {
            $grade = getGrade($note100);
            if (in_array($grade[0], array('F', 'E', 'D', 'D+', 'C-'))) return 'Insuffisant / Insufficient';
            if (in_array($grade[0], array('C', 'C+'))) return 'Passable / Fair';
            if (in_array($grade[0], array('B-', 'B'))) return 'Assez-Bien / Fairly Good';
            if ($grade[0] == 'B+') return 'Bien / Good';
            if ($grade[0] == 'A') return 'Tres Bien / Very Good';
            return 'Excellent / Excellent';
        };
        $soaDecision = function ($gpa) {
            return ((float) $gpa >= 2) ? 'ADMIS(E)/PASSED' : 'AJOURNE(E)/FAILED';
        };
        $soaClean = function ($value) {
            return trim((string) $value);
        };
        $soaYear = function () {
            $year = (string) session('year');
            if (preg_match('/(\d{4})\D*$/', $year, $matches)) return $matches[1];
            return date('Y');
        };
        $soaFirst = function ($values, $fallback = '') use ($soaClean) {
            foreach ($values as $value) {
                $clean = $soaClean($value);
                if ($clean !== '') return $clean;
            }
            return $fallback;
        };
        $soaLogoPath = function ($path, $fallback = '') {
            $path = trim((string) $path);
            if ($path === '') return $fallback;
            $path = str_replace('\\', '/', $path);
            if (strpos($path, '/public/') !== false) {
                $path = substr($path, strpos($path, '/public/') + 8);
            }
            return ltrim($path, '/');
        };
    ?>

    <style>
        @media  print {
            .no-print,
            .navbar,
            .navbar-default,
            .navbar-side,
            .breadcrumb,
            .big-title,
            .action,
            .alert,
            .back-button,
            .copy,
            .loader,
            .loading-back,
            .loading_icon,
            hr {
                display: none !important;
            }
            @page  {
                size: <?php echo e(isset($page_size) ? $page_size : 'A4'); ?> <?php echo e(isset($page_orientation) ? $page_orientation : 'portrait'); ?>;
                margin: 0.8% 2% 3.2% 2%;
            }
            body { background: #fff !important; }
            #wrapper,
            #page-wrapper,
            #page-inner,
            .row,
            .row-fluid,
            .col-lg-12,
            .col-md-12 {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                float: none !important;
                border: 0 !important;
            }
            .soa-page {
                width: auto;
                min-height: 96vh;
                page-break-after: always;
                break-after: page;
                box-shadow: none;
                margin: 0 auto;
                padding: 0.5% 0 6%;
                position: relative;
                box-sizing: border-box;
            }
            .soa-page:last-child {
                page-break-after: auto;
                break-after: auto;
            }
        }
        .soa-page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 18px;
            padding: 0.5% 2% 6%;
            box-sizing: border-box;
            background: #fff;
            color: #000;
            font-family: "Times New Roman", Times, serif;
            font-size: 11.5px;
            line-height: 1.12;
        }
        .soa-official-header {
            display: table;
            width: 100%;
            table-layout: fixed;
            margin-bottom: 4px;
            font-weight: bold;
            text-align: center;
        }
        .soa-header-col {
            display: table-cell;
            width: 33.33%;
            vertical-align: top;
            padding: 0 3px;
        }
        .soa-header-logo {
            display: block;
            max-height: 58px;
            max-width: 82px;
            margin: 0 auto 1px;
            object-fit: contain;
        }
        .soa-header-left { text-align: left; }
        .soa-header-right { text-align: right; }
        .soa-header-center { font-size: 11.5px; padding-top: 14px; }
        .soa-header-school { font-size: 10.5px; line-height: 1.12; }
        .soa-header-site { font-size: 10.5px; margin-top: 2px; }
        .soa-transcript-table,
        .soa-transcript-table th,
        .soa-transcript-table td {
            border: 1px solid #000 !important;
            border-collapse: collapse;
        }
        .soa-transcript-table {
            width: 100%;
            table-layout: fixed;
            font-size: 11.5px;
            margin: 2px 0 0;
        }
        .soa-transcript-table th,
        .soa-transcript-table td {
            padding: 2px 4px !important;
            vertical-align: middle !important;
        }
        .soa-title-row td {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            height: 30px;
        }
        .soa-identity td {
            height: 27px;
            font-weight: bold;
        }
        .soa-label {
            display: inline-block;
            font-weight: bold;
        }
        .soa-value {
            display: inline-block;
            margin-left: 8px;
            font-weight: bold;
        }
        .soa-notes thead th {
            text-align: center;
            font-weight: bold;
            font-size: 8.9px;
            line-height: 1.02;
        }
        .soa-notes {
            font-size: 9.3px;
        }
        .soa-notes th,
        .soa-notes td {
            padding: 1px 2px !important;
        }
        .soa-notes td { height: 15px; }
        .soa-code { width: 8%; font-weight: bold; }
        .soa-course { width: 36%; }
        .soa-mark { width: 8%; text-align: right; }
        .soa-grade { width: 8%; text-align: center; }
        .soa-appreciation { width: 13%; text-align: center; }
        .soa-credit { width: 7%; text-align: center; font-weight: bold; }
        .soa-session { width: 13%; text-align: center; font-weight: bold; }
        .soa-year { width: 7%; text-align: center; font-weight: bold; }
        .soa-module-row td {
            font-weight: bold;
            background: #fff;
        }
        .soa-child-course { padding-left: 12px !important; }
        .soa-summary td {
            text-align: center;
            font-weight: bold;
            height: 19px;
            line-height: 1.02;
            font-size: 9px;
        }
        .soa-summary-title td {
            text-align: center;
            font-weight: bold;
            height: 19px;
            font-size: 9.6px;
            background: #f7f7f7;
        }
        .soa-scale td {
            font-size: 8.2px;
            text-align: center;
            font-weight: bold;
            line-height: 1.08;
        }
        .soa-signature {
            width: 100%;
            text-align: right;
            margin-top: 10px;
            font-weight: bold;
            font-size: 13px;
        }
        .soa-dean-block {
            display: inline-block;
            min-width: 190px;
            text-align: center;
            margin-top: 8px;
        }
        .soa-nb {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0.6%;
            margin-top: 0;
            font-size: 10px;
            line-height: 1.08;
            text-align: center;
            padding: 0 2%;
        }
        .soa-page-a3 {
            width: 297mm;
            min-height: 420mm;
        }
        .soa-page-a3 .soa-notes {
            font-size: 10.2px;
        }
        .soa-page-a3 .soa-notes thead th {
            font-size: 9.8px;
        }
        .soa-page-a3 .soa-summary td {
            font-size: 9.8px;
            height: 21px;
        }
        @media  print {
            .soa-page-a3 {
                width: auto;
                min-height: 96vh;
            }
        }
    </style>

    <?php
        $soaReleveType = strtolower(isset($pv_type) ? $pv_type : 'licence_soa');
        $soaIsEbolowa = ($soaReleveType == 'licence_ebolowa');
        $soaIsBamenda = ($soaReleveType == 'licence_bamenda');
        $soaBasePath = $soaIsBamenda ? '/eleves/releves_bamenda' : ($soaIsEbolowa ? '/eleves/releve_estic' : '/eleves/releves_soa');
        $soaPageLabel = $soaIsBamenda ? 'Bamenda page' : ($soaIsEbolowa ? 'Page Ebolowa' : 'Page SOA');
        $soaSignatureCity = $soaIsBamenda ? 'Bamenda' : ($soaIsEbolowa ? 'Ebolowa' : 'Soa');
        $soaLabel = function ($french, $english) use ($soaIsBamenda) {
            return $soaIsBamenda ? $english.' / '.$french : $french.' / '.$english;
        };
        $soaCourseLabel = function ($item) use ($soaIsBamenda, $soaClean) {
            if (is_array($item)) {
                $primary = !empty($item['altName']) ? $item['altName'] : (isset($item['nom']) ? $item['nom'] : '');
                $english = isset($item['nom_anglais']) ? $item['nom_anglais'] : '';
            } else {
                $primary = !empty($item->altName) ? $item->altName : (isset($item->nom) ? $item->nom : '');
                $english = isset($item->nom_anglais) ? $item->nom_anglais : '';
            }
            if (!$soaIsBamenda || $soaClean($english) === '' || strpos($primary, ' / ') !== false) {
                return $primary;
            }
            return $primary.' / '.$english;
        };
    ?>
    <div class="btn-toolbar no-print" style="margin-bottom:15px;">
        <a href="<?php echo e(url('/eleves')); ?>" class="btn btn-default"><span class="glyphicon glyphicon-home"></span> Accueil eleves</a>
        <a href="<?php echo e(url($soaBasePath)); ?>" class="btn btn-default"><span class="glyphicon glyphicon-list"></span> <?php echo e($soaPageLabel); ?></a>
        <a href="<?php echo e(url($soaBasePath.'/historique')); ?>" class="btn btn-warning"><span class="glyphicon glyphicon-folder-open"></span> Historique</a>
        <a href="javascript:history.back();" class="btn btn-info"><span class="glyphicon glyphicon-arrow-left"></span> Retour</a>
        <a href="#" class="btn btn-lg btn-warning" onclick="window.print()"><span class="glyphicon glyphicon-print"></span> <?php echo \Illuminate\Support\Facades\Lang::get('inscription.imprimer'); ?></a>
    </div>
    <?php
        $soaPrintUrl = \Request::url();
        $soaPrintQuery = \Input::all();
        $soaPrintQuery['page_size'] = 'a4';
        $soaPrintA4 = $soaPrintUrl.'?'.http_build_query($soaPrintQuery);
        $soaPrintQuery['page_size'] = 'a3';
        $soaPrintA3 = $soaPrintUrl.'?'.http_build_query($soaPrintQuery);
    ?>
    <span class="no-print">
        <a href="<?php echo e($soaPrintA4); ?>" class="btn btn-default">A4 portrait</a>
        <a href="<?php echo e($soaPrintA3); ?>" class="btn btn-default">A3 portrait</a>
    </span>

    <?php $ne = 0; ?>
    <?php foreach($PV as $releve): ?>
        <?php
            $plannedCredits = 0;
            $validatedCredits = 0;
            $weightedNote = 0;
            $weightedGpa = 0;
            $tmp = 0;
            $tmv = 0;
            $studentName = $soaClean($releve->eleve->nom.' '.$releve->eleve->prenom);
            $birthDate = '';
            if (!empty($releve->eleve->date_naiss)) {
                try { $birthDate = (new DateTime($releve->eleve->date_naiss))->format('d/m/Y'); } catch (Exception $e) { $birthDate = $releve->eleve->date_naiss; }
            }
            $levelName = isset($nniveau->nom) ? preg_replace('/[^0-9]/', '', $nniveau->nom) : '';
            if ($levelName === '') $levelName = isset($nniveau->nom) ? $nniveau->nom : '';
            $renderedMatiereIds = array();
            $findReleveMatiere = function ($idMatiere) use ($releve) {
                foreach ($releve->matieres as $releveMatiere) {
                    $candidate = isset($releveMatiere->id_matiere) ? $releveMatiere->id_matiere : (isset($releveMatiere->id) ? $releveMatiere->id : null);
                    if ($candidate !== null && (int) $candidate === (int) $idMatiere) {
                        return $releveMatiere;
                    }
                }
                return null;
            };
            $findNote = function ($idMatiere) use ($findReleveMatiere) {
                $releveMatiere = $findReleveMatiere($idMatiere);
                return ($releveMatiere && isset($releveMatiere->note) && is_numeric($releveMatiere->note)) ? (float) $releveMatiere->note : null;
            };
            $markTotals = function ($note, $credit) use (&$plannedCredits, &$validatedCredits, &$weightedNote, &$weightedGpa, &$tmp, &$tmv, $soaGrade) {
                if (!is_numeric($credit) || (float) $credit <= 0) return;
                $credit = (float) $credit;
                $plannedCredits += $credit;
                $tmp++;
                if ($note === null) return;
                $weightedNote += ((float) $note / 5) * $credit;
                $gradeValues = $soaGrade($note);
                $weightedGpa += $gradeValues[1] * $credit;
                if ((float) $note >= 50) {
                    $validatedCredits += $credit;
                    $tmv++;
                }
            };
            $soaCampus = isset($campus) ? $campus : null;
            $soaTutelle = isset($tutelle) ? $tutelle : null;
            $campusName = $soaFirst(array(
                $soaCampus && isset($soaCampus->nom) ? $soaCampus->nom : '',
                'CENTRE DE FORMATION HINTEL'
            ));
            $campusLogo = $soaLogoPath($soaCampus && isset($soaCampus->logo) ? $soaCampus->logo : '', 'img/school-concept.png');
            $campusLines = array();
            $campusLines[] = $campusName;
            foreach (array('arrete_creation', 'autorisation_ouverture') as $field) {
                if ($soaCampus && isset($soaCampus->$field) && $soaClean($soaCampus->$field) !== '') {
                    $campusLines[] = $soaCampus->$field;
                }
            }
            $campusContacts = array();
            foreach (array('boite_postale', 'ville') as $field) {
                if ($soaCampus && isset($soaCampus->$field) && $soaClean($soaCampus->$field) !== '') {
                    $campusContacts[] = $soaCampus->$field;
                }
            }
            if ($soaCampus && isset($soaCampus->telephone) && $soaClean($soaCampus->telephone) !== '') {
                $campusContacts[] = 'Tel '.$soaCampus->telephone;
            }
            if (count($campusContacts) > 0) {
                $campusLines[] = implode(', ', $campusContacts);
            } else {
                $campusLines[] = 'BP 4441 Yaounde, Tel (237)696459283/672280944';
            }
            $campusSite = $soaFirst(array($soaCampus && isset($soaCampus->site_web) ? $soaCampus->site_web : ''), 'hintelhotel.com');

            $ministryLine = $soaFirst(array($soaTutelle && isset($soaTutelle->ministere_tutelle) ? $soaTutelle->ministere_tutelle : ''), 'MINISTERE DE L\'ENSEIGNEMENT SUPERIEUR');
            $ministryEnglishLine = $soaFirst(array($soaTutelle && isset($soaTutelle->ministere_tutelle_anglophone) ? $soaTutelle->ministere_tutelle_anglophone : ''), 'MINISTRY OF HIGHER EDUCATION');
            $tutelleName = $soaFirst(array($soaTutelle && isset($soaTutelle->nom) ? $soaTutelle->nom : '', 'UNIVERSITE DE YAOUNDE II-SOA'));
            $facultyName = $soaFirst(array($soaTutelle && isset($soaTutelle->faculte) ? $soaTutelle->faculte : '', 'FACULTE DES SCIENCES ECONOMIQUES ET DE GESTION'));
            $tutelleLogo = $soaLogoPath(
                $soaTutelle && isset($soaTutelle->logo_faculte) && $soaClean($soaTutelle->logo_faculte) !== '' ? $soaTutelle->logo_faculte : ($soaTutelle && isset($soaTutelle->logo) ? $soaTutelle->logo : ''),
                'img/releve_soa_univ_yde2.jpeg'
            );
            $tutelleContacts = array();
            if ($soaTutelle && isset($soaTutelle->localisation) && $soaClean($soaTutelle->localisation) !== '') $tutelleContacts[] = $soaTutelle->localisation;
            if ($soaTutelle && isset($soaTutelle->ville) && $soaClean($soaTutelle->ville) !== '') $tutelleContacts[] = $soaTutelle->ville;
            if ($soaTutelle && isset($soaTutelle->telephone) && $soaClean($soaTutelle->telephone) !== '') $tutelleContacts[] = 'Tel. : '.$soaTutelle->telephone;
            $tutelleSite = $soaFirst(array($soaTutelle && isset($soaTutelle->email) ? $soaTutelle->email : ''), 'www.univ-yde2.org');
        ?>
        <div class="soa-page soa-page-<?php echo e(strtolower(isset($page_size) ? $page_size : 'a4')); ?>">
            <div class="soa-official-header">
                <div class="soa-header-col soa-header-left">
                    <img src="<?php echo e(asset($campusLogo)); ?>" alt="Hintel" class="soa-header-logo">
                    <div class="soa-header-school">
                        <?php foreach($campusLines as $campusLine): ?>
                            <?php echo e($campusLine); ?><br>
                        <?php endforeach; ?>
                    </div>
                    <div class="soa-header-site"><?php echo e($campusSite); ?></div>
                </div>
                <div class="soa-header-col soa-header-center">
                    <?php if($soaIsBamenda): ?>
                        <?php echo e($ministryEnglishLine); ?><br>
                        <?php echo e($ministryLine); ?>
                    <?php else: ?>
                        <?php echo e($ministryLine); ?><br>
                        <?php echo e($ministryEnglishLine); ?>
                    <?php endif; ?>

                </div>
                <div class="soa-header-col soa-header-right">
                    <img src="<?php echo e(asset($tutelleLogo)); ?>" alt="Tutelle" class="soa-header-logo">
                    <div class="soa-header-school">
                        <?php echo e($tutelleName); ?><br>
                        <?php echo e($facultyName); ?><br>
                        <?php if(count($tutelleContacts) > 0): ?>
                            <?php echo e(implode(', ', $tutelleContacts)); ?>

                        <?php else: ?>
                            B.P. : 1365 YAOUNDE, Tel. : (237)22062698
                        <?php endif; ?>
                    </div>
                    <div class="soa-header-site"><?php echo e($tutelleSite); ?></div>
                </div>
            </div>

            <table class="soa-transcript-table">
                <tbody>
                <tr class="soa-title-row">
                    <td colspan="8"><?php echo e($soaIsBamenda ? 'TRANSCRIPT / RELEVE DE NOTES' : 'RELEVE DE NOTES/TRANSCRIPT'); ?></td>
                </tr>
                <tr class="soa-identity">
                    <td colspan="4">
                        <span class="soa-label"><?php echo e($soaLabel('Nom et Prenom', 'Name and Surname')); ?></span>
                        <span class="soa-value"><?php echo e($studentName); ?></span>
                    </td>
                    <td colspan="4">
                        <span class="soa-label">Matricule/Registration N&deg;</span>
                        <span class="soa-value"><?php echo e($releve->eleve->matricule); ?></span>
                    </td>
                </tr>
                <tr class="soa-identity">
                    <td colspan="4">
                        <span class="soa-label"><?php echo e($soaLabel('Ne(e) le', 'Born on the')); ?></span>
                        <span class="soa-value"><?php echo e($birthDate); ?></span>
                    </td>
                    <td colspan="4">
                        <span class="soa-label"><?php echo e($soaLabel('a', 'at')); ?></span>
                        <span class="soa-value"><?php echo e($releve->eleve->lieu_naiss); ?></span>
                    </td>
                </tr>
                <tr class="soa-identity">
                    <td colspan="4">
                        <span class="soa-label"><?php echo e($soaLabel('Cycle', 'Degree')); ?></span>
                        <span class="soa-value"><?php echo e(isset($cycle->nom) ? $cycle->nom : ''); ?></span>
                    </td>
                    <td colspan="4">
                        <span class="soa-label"><?php echo e($soaLabel('Annee Academique', 'Academic Year')); ?></span>
                        <span class="soa-value"><?php echo e(session('year')); ?></span>
                    </td>
                </tr>
                <tr class="soa-identity">
                    <td colspan="5">
                        <span class="soa-label"><?php echo e($soaLabel('Specialite', 'Speciality')); ?></span>
                        <span class="soa-value"><?php echo e(isset($classe->nom) ? $classe->nom : ''); ?></span>
                    </td>
                    <td colspan="3">
                        <span class="soa-label"><?php echo e($soaLabel('Niveau', 'Level')); ?></span>
                        <span class="soa-value"><?php echo e($levelName); ?></span>
                    </td>
                </tr>
                </tbody>
            </table>

            <table class="soa-transcript-table soa-notes">
                <thead>
                <tr>
                    <th class="soa-code">Code</th>
                    <th class="soa-course"><?php echo e($soaIsBamenda ? 'Course Title' : 'Intitule de l\'Unite d\'Enseignement'); ?><br><?php echo e($soaIsBamenda ? 'Intitule de l\'Unite d\'Enseignement' : 'Course Title'); ?></th>
                    <th class="soa-mark"><?php echo e($soaIsBamenda ? 'Marks' : 'Notes'); ?><br><?php echo e($soaIsBamenda ? 'Notes' : 'Marks'); ?></th>
                    <th class="soa-grade"><?php echo e($soaIsBamenda ? 'Grade' : 'Notation'); ?><br><?php echo e($soaIsBamenda ? 'Notation' : 'Grade'); ?></th>
                    <th class="soa-appreciation"><?php echo e($soaIsBamenda ? 'Appreciation' : 'Appreciation'); ?><br><?php echo e($soaIsBamenda ? 'Appreciation' : 'Appreciation'); ?></th>
                    <th class="soa-credit">Credits<br>Credits</th>
                    <th class="soa-session"><?php echo e($soaIsBamenda ? 'Semester/Session' : 'Semestre/Session'); ?><br><?php echo e($soaIsBamenda ? 'Semestre/Session' : 'Semester/Session'); ?></th>
                    <th class="soa-year"><?php echo e($soaIsBamenda ? 'Year' : 'Annee'); ?><br><?php echo e($soaIsBamenda ? 'Annee' : 'Year'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php if(!empty($module_groups)): ?>
                    <?php foreach($module_groups as $moduleGroup): ?>
                        <?php
                            $module = is_array($moduleGroup) ? $moduleGroup['module'] : $moduleGroup->module;
                            $moduleRows = is_array($moduleGroup) ? $moduleGroup['matieres'] : $moduleGroup->matieres;
                            $moduleId = isset($module->id) ? $module->id : null;
                            $moduleNote = $moduleId !== null ? $findNote($moduleId) : null;
                            $moduleCredit = isset($module->credit) ? $module->credit : (isset($module->coef) ? $module->coef : 0);
                            $moduleNoteSum = 0;
                            $moduleNoteCount = 0;
                            $moduleWeightedNote = 0;
                            $moduleWeightedCredit = 0;
                            foreach ($moduleRows as $averageRow) {
                                $averageRowId = is_array($averageRow) ? (isset($averageRow['id']) ? $averageRow['id'] : null) : (isset($averageRow->id) ? $averageRow->id : null);
                                $averageSaved = $averageRowId !== null ? $findReleveMatiere($averageRowId) : null;
                                $averageNote = $averageSaved && isset($averageSaved->note) && is_numeric($averageSaved->note) ? (float) $averageSaved->note : null;
                                $averageCredit = is_array($averageRow)
                                    ? (isset($averageRow['credit']) ? $averageRow['credit'] : (isset($averageRow['coef']) ? $averageRow['coef'] : 0))
                                    : (isset($averageRow->credit) ? $averageRow->credit : (isset($averageRow->coef) ? $averageRow->coef : 0));
                                if ($averageNote !== null) {
                                    $moduleNoteSum += $averageNote;
                                    $moduleNoteCount++;
                                    if (is_numeric($averageCredit) && (float) $averageCredit > 0) {
                                        $moduleWeightedNote += $averageNote * (float) $averageCredit;
                                        $moduleWeightedCredit += (float) $averageCredit;
                                    }
                                }
                            }
                            if ($moduleNote === null) {
                                if ($moduleWeightedCredit > 0) {
                                    $moduleNote = $moduleWeightedNote / $moduleWeightedCredit;
                                } elseif ($moduleNoteCount > 0) {
                                    $moduleNote = $moduleNoteSum / $moduleNoteCount;
                                }
                            }
                            if ((!is_numeric($moduleCredit) || (float) $moduleCredit <= 0) && $moduleWeightedCredit > 0) {
                                $moduleCredit = $moduleWeightedCredit;
                            }
                            $moduleGrade = $moduleNote !== null ? $soaGrade($moduleNote) : array('', '');
                            $markTotals($moduleNote, $moduleCredit);
                            if ($moduleId !== null) $renderedMatiereIds[(int) $moduleId] = true;
                        ?>
                        <tr class="soa-module-row">
                            <td class="soa-code"><?php echo e(!empty($module->id_code) ? $module->id_code : (isset($module->code) ? $module->code : '')); ?></td>
                            <td class="soa-course"><?php echo e($soaCourseLabel($module)); ?></td>
                            <td class="soa-mark"><?php echo e($moduleNote !== null ? number_format($moduleNote / 5, 2) : ''); ?></td>
                            <td class="soa-grade"><?php echo e($moduleGrade[0]); ?></td>
                            <td class="soa-appreciation"><?php echo e($moduleNote !== null ? $soaMention($moduleNote) : ''); ?></td>
                            <td class="soa-credit"><?php echo e(number_format((float) $moduleCredit, 0)); ?></td>
                            <td class="soa-session"></td>
                            <td class="soa-year"></td>
                        </tr>
                        <?php foreach($moduleRows as $moduleRow): ?>
                            <?php
                                $rowId = is_array($moduleRow) ? (isset($moduleRow['id']) ? $moduleRow['id'] : null) : (isset($moduleRow->id) ? $moduleRow->id : null);
                                $rowSaved = $rowId !== null ? $findReleveMatiere($rowId) : null;
                                $rowNote = $rowSaved && isset($rowSaved->note) && is_numeric($rowSaved->note) ? (float) $rowSaved->note : null;
                                $rowGrade = $rowNote !== null ? $soaGrade($rowNote) : array('', '');
                                $rowCredit = is_array($moduleRow)
                                    ? (isset($moduleRow['credit']) ? $moduleRow['credit'] : (isset($moduleRow['coef']) ? $moduleRow['coef'] : 0))
                                    : (isset($moduleRow->credit) ? $moduleRow->credit : (isset($moduleRow->coef) ? $moduleRow->coef : 0));
                                $rowSemester = is_array($moduleRow) ? (isset($moduleRow['semestre']) ? $moduleRow['semestre'] : '') : (isset($moduleRow->semestre) ? $moduleRow->semestre : '');
                                $rowSession = $rowSaved && isset($rowSaved->session) ? $rowSaved->session : (is_array($moduleRow) ? (isset($moduleRow['session']) ? $moduleRow['session'] : 'N') : (isset($moduleRow->session) ? $moduleRow->session : 'N'));
                                if ($rowId !== null) $renderedMatiereIds[(int) $rowId] = true;
                            ?>
                            <tr>
                                <td class="soa-code"><?php echo e(is_array($moduleRow) ? (!empty($moduleRow['id_code']) ? $moduleRow['id_code'] : '') : (!empty($moduleRow->id_code) ? $moduleRow->id_code : '')); ?></td>
                                <td class="soa-course soa-child-course"><?php echo e($soaCourseLabel($moduleRow)); ?></td>
                                <td class="soa-mark"><?php echo e($rowNote !== null ? number_format($rowNote / 5, 2) : ''); ?></td>
                                <td class="soa-grade"><?php echo e($rowGrade[0]); ?></td>
                                <td class="soa-appreciation"><?php echo e($rowNote !== null ? $soaMention($rowNote) : ''); ?></td>
                                <td class="soa-credit"><?php echo e(is_numeric($rowCredit) && (float) $rowCredit > 0 ? number_format((float) $rowCredit, 0) : ''); ?></td>
                                <td class="soa-session"><?php echo e($rowSemester !== '' ? getSemestre($rowSemester).$rowSession : ''); ?></td>
                                <td class="soa-year"><?php echo e($soaYear()); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                <?php foreach($releve->matieres as $matiere): ?>
                    <?php
                        $matiereId = isset($matiere->id_matiere) ? $matiere->id_matiere : (isset($matiere->id) ? $matiere->id : null);
                    ?>
                    <?php if($matiereId !== null && !isset($renderedMatiereIds[(int) $matiereId])): ?>
                        <?php
                            $note = isset($matiere->note) && is_numeric($matiere->note) ? (float) $matiere->note : null;
                            $credit = isset($matiere->credit) ? $matiere->credit : 0;
                            $grade = $note !== null ? $soaGrade($note) : array('', '');
                            $matiereSession = isset($matiere->session) ? $matiere->session : 'N';
                            $markTotals($note, $credit);
                        ?>
                        <tr class="soa-module-row">
                            <td class="soa-code"><?php echo e(isset($matiere->id_code) ? $matiere->id_code : ''); ?></td>
                            <td class="soa-course"><?php echo e($soaCourseLabel($matiere)); ?></td>
                            <td class="soa-mark"><?php echo e($note !== null ? number_format($note / 5, 2) : ''); ?></td>
                            <td class="soa-grade"><?php echo e($grade[0]); ?></td>
                            <td class="soa-appreciation"><?php echo e($note !== null ? $soaMention($note) : ''); ?></td>
                            <td class="soa-credit"><?php echo e(number_format((float) $credit, 0)); ?></td>
                            <td class="soa-session"><?php echo e(isset($matiere->semestre) ? getSemestre($matiere->semestre).$matiereSession : ''); ?></td>
                            <td class="soa-year"><?php echo e($soaYear()); ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php
                    $average20 = $plannedCredits > 0 ? $weightedNote / $plannedCredits : 0;
                    $gpa = $plannedCredits > 0 ? $weightedGpa / $plannedCredits : 0;
                    $average100 = $average20 * 5;
                ?>
                <tr class="soa-summary-title">
                    <td colspan="8"><?php echo e($soaIsBamenda ? 'SUMMARY / SYNTHESE' : 'SYNTHESE / SUMMARY'); ?></td>
                </tr>
                <tr class="soa-summary">
                    <td colspan="2"><?php echo e($soaIsBamenda ? 'Total planned credits' : 'Total des credits prevus'); ?><br><?php echo e($soaIsBamenda ? 'Total des credits prevus' : 'Total planned credits'); ?></td>
                    <td colspan="2"><?php echo e(number_format($plannedCredits, 0)); ?></td>
                    <td colspan="2"><?php echo e($soaIsBamenda ? 'Total validated credits' : 'Total des credits valides'); ?><br><?php echo e($soaIsBamenda ? 'Total des credits valides' : 'Total validated credits'); ?></td>
                    <td colspan="2"><?php echo e(number_format($validatedCredits, 0)); ?></td>
                </tr>
                <tr class="soa-summary">
                    <td colspan="2"><?php echo e($soaIsBamenda ? 'Average /20' : 'Moyenne /20'); ?><br><?php echo e($soaIsBamenda ? 'Moyenne /20' : 'Average /20'); ?></td>
                    <td colspan="2"><?php echo e(number_format($average20, 2)); ?></td>
                    <td colspan="2"><?php echo e($soaIsBamenda ? 'Average /100' : 'Moyenne /100'); ?><br><?php echo e($soaIsBamenda ? 'Moyenne /100' : 'Average /100'); ?></td>
                    <td colspan="2"><?php echo e(number_format($average100, 2)); ?></td>
                </tr>
                <tr class="soa-summary">
                    <td colspan="2">MGP /4<br>GPA /4</td>
                    <td colspan="2"><?php echo e(number_format($gpa, 1)); ?></td>
                    <td colspan="2">Appreciation<br>Appreciation</td>
                    <td colspan="2"><?php echo e($soaMention($average100)); ?></td>
                </tr>
                <tr class="soa-summary">
                    <td colspan="2">Decision<br>Decision</td>
                    <td colspan="6"><?php echo e($soaDecision($gpa)); ?></td>
                </tr>
                <tr class="soa-scale">
                    <td colspan="8">
                        4.0=A+=[18-20]&nbsp;&nbsp;&nbsp; 3.7=A=[16-18[&nbsp;&nbsp;&nbsp; 3.3=B+=[14-16[&nbsp;&nbsp;&nbsp; 3.0=B=[13-14[&nbsp;&nbsp;&nbsp; 2.7=B-=[12-13[&nbsp;&nbsp;&nbsp; 2.3=C+=[11-12[&nbsp;&nbsp;&nbsp; 2.0=C=[10-11[&nbsp;&nbsp;&nbsp; 1.7=C-=[9-10[&nbsp;&nbsp;&nbsp; 1.3=D=[7-9[
                    </td>
                </tr>
                <tr class="soa-scale">
                    <td colspan="8">
                        N=NORMAL&nbsp;&nbsp; R=RATTRAPAGE&nbsp;&nbsp; MGP=MOYENNE GENERALE PONDEREE&nbsp;&nbsp; GPA=GRADE POINT AVERAGE
                    </td>
                </tr>
                <tr class="soa-scale">
                    <td colspan="8">
                        D/C- = INSUFFISANT&nbsp;&nbsp; C/C+ = PASSABLE&nbsp;&nbsp; B-/B = ASSEZ BIEN&nbsp;&nbsp; B+ = BIEN&nbsp;&nbsp; A = TRES BIEN&nbsp;&nbsp; A+ = EXCELLENT
                    </td>
                </tr>
                </tbody>
            </table>

            <div class="soa-signature">
                <div><?php echo e($soaSignatureCity); ?>, le............................</div>
                <?php if($soaIsEbolowa): ?>
                    <div style="display: table; width: 100%; margin-top: 10px;">
                        <div style="display: table-cell; width: 50%; text-align: center;">
                            <div>LE DIRECTEUR HINTEL</div>
                            <div><em>The Director of Hintel</em></div>
                        </div>
                        <div style="display: table-cell; width: 50%; text-align: center;">
                            <div><?php echo e($soaIsBamenda ? 'THE DEAN' : 'LE DOYEN'); ?></div>
                            <div><em><?php echo e($soaIsBamenda ? 'Le Doyen' : 'The Dean'); ?></em></div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="soa-dean-block">
                        <div><?php echo e($soaIsBamenda ? 'THE DEAN' : 'LE DOYEN'); ?></div>
                        <div><em><?php echo e($soaIsBamenda ? 'Le Doyen' : 'The Dean'); ?></em></div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="soa-nb">
                <?php if($soaIsBamenda): ?>
                    <strong>N.B.:</strong> <em>Only one copy of this transcript shall be delivered. It is in the interest of the owner to make as many certified true copies as he/she may desire.</em><br>
                    Il n'est delivre qu'un seul releve de notes. Il appartient au titulaire d'etablir lui-meme et faire certifier conformes les copies qui peuvent lui etre necessaires.
                <?php else: ?>
                    <strong>N.B.:</strong> Il n'est delivre qu'un seul releve de notes. Il appartient au titulaire d'etablir lui-meme et faire certifier conformes les copies qui peuvent lui etre necessaires.<br>
                    <em>Only one copy of this transcript shall be delivered. It is in the interest of the owner to make as many certified true copies as he/she may desire.</em>
                <?php endif; ?>
            </div>
        </div>
        <?php $ne++; ?>
    <?php endforeach; ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li><strong><a href="<?php echo e(url('eleves')); ?>"><?php echo \Illuminate\Support\Facades\Lang::get('menu.gestion_notes'); ?></a></strong></li>
    <li><strong><a href="<?php echo e(url(ltrim($soaBasePath, '/'))); ?>"><?php echo \Illuminate\Support\Facades\Lang::get('admin.choix_classe'); ?></a></strong></li>
    <li class="active"><strong><?php echo e($titre); ?></strong></li>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('skeleton', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
