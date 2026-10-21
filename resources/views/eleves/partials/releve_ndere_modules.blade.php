@if(!empty($module_groups))
<?php $isSecondSemester = isset($trimestre) && (int) $trimestre === 6; ?>
<table class="table table-striped table-condensed table-inverse module-summary" style="margin-top:8px;margin-bottom:8px;">
    <thead><tr>
        <th>Code</th><th colspan="{{ $isSecondSemester ? 1 : 2 }}">Intitulé de l'UE / Course title</th>
        @if($isSecondSemester)
            <th>Crédits<br>Credits</th><th>Notes / Marks /20</th><th>Notes / Marks /100</th>
            <th>Notation<br>Grade</th><th>Moyenne des notes<br>GPA</th><th>Note pondérée<br>Weighted Point</th><th>Année<br>Year</th>
        @else
            <th>Semestre / Semester</th><th>Année / Year</th><th>Crédits / Credits</th><th>Note /20</th><th>Qualité /4</th><th>Grade</th><th>Appréciation / Appreciation</th>
        @endif
    </tr></thead>
    <tbody>
    @foreach($module_groups as $moduleGroup)
        <?php
            $module = is_array($moduleGroup) ? $moduleGroup['module'] : $moduleGroup->module;
            $moduleRows = is_array($moduleGroup) ? $moduleGroup['matieres'] : $moduleGroup->matieres;
            $moduleCode = !empty($module->id_code) ? $module->id_code : (isset($module->code) ? $module->code : $module->id);
            $moduleName = !empty($module->altName) ? $module->altName : $module->nom;
            $moduleCredit = !empty($module->credit) ? $module->credit : (isset($module->coef) ? $module->coef : 0);
            $moduleNote = null;
            $moduleNoteTotal = 0;
            $moduleCreditTotal = 0;
            foreach ($moduleRows as $moduleRow) {
                $rowId = isset($moduleRow->id) ? $moduleRow->id : null;
                $rowCreditValue = !empty($moduleRow->credit) ? (float) $moduleRow->credit : (isset($moduleRow->coef) ? (float) $moduleRow->coef : 0);
                if ($rowId === null) continue;
                foreach ($releve->matieres as $item) {
                    if (isset($item->id_matiere) && (int) $item->id_matiere === (int) $rowId && is_numeric($item->note)) {
                        $moduleNoteTotal += ((float) $item->note) * $rowCreditValue;
                        $moduleCreditTotal += $rowCreditValue;
                        break;
                    }
                }
            }
            if ($moduleCreditTotal > 0) {
                $moduleNote = $moduleNoteTotal / $moduleCreditTotal;
            }
            $moduleGradePoint = $moduleNote !== null ? getGrade($moduleNote)[1] : null;
            $moduleWeightedPoint = $moduleGradePoint !== null ? $moduleGradePoint * (float) $moduleCredit : null;
        ?>
        <tr class="module-row">
            <td><strong>{{ $moduleCode }}</strong></td><td colspan="{{ $isSecondSemester ? 1 : 2 }}"><strong>{{ $moduleName }}</strong></td>
            @if($isSecondSemester)
                <td style="text-align:center">{{ number_format((float)$moduleCredit, 0) }}</td>
                <td style="text-align:center">{{ $moduleNote !== null ? number_format((float)$moduleNote / 5, 2) : '' }}</td>
                <td style="text-align:center">{{ $moduleNote !== null ? number_format((float)$moduleNote, 2) : '' }}</td>
                <td style="text-align:center">{{ $moduleNote !== null ? getGrade($moduleNote)[0] : '' }}</td>
                <td style="text-align:center">{{ $moduleGradePoint !== null ? number_format($moduleGradePoint, 1) : '' }}</td>
                <td style="text-align:center">{{ $moduleWeightedPoint !== null ? number_format($moduleWeightedPoint, 1) : '' }}</td>
                <td style="text-align:center">{{ session('year') }}</td>
            @else
                <td style="text-align:center">Moy / Av</td><td style="text-align:center">{{ session('year') }}</td><td style="text-align:center">{{ number_format((float)$moduleCredit, 0) }}</td>
                <td style="text-align:center">{{ $moduleNote !== null ? number_format((float)$moduleNote / 5, 2) : '' }}</td>
                <td style="text-align:center">{{ $moduleNote !== null ? getGrade($moduleNote)[1] : '' }}</td>
                <td style="text-align:center">{{ $moduleNote !== null ? getGrade($moduleNote)[0] : '' }}</td>
                <td style="text-align:center">{{ $moduleNote !== null ? getMention($moduleNote, 100) : '' }}</td>
            @endif
        </tr>
        @foreach($moduleRows as $moduleRow)
            <?php
                $rowId = isset($moduleRow->id) ? $moduleRow->id : null;
                $rowCode = !empty($moduleRow->id_code) ? $moduleRow->id_code : (isset($moduleRow->code) ? $moduleRow->code : $rowId);
                $rowName = !empty($moduleRow->altName) ? $moduleRow->altName : (isset($moduleRow->nom) ? $moduleRow->nom : '');
                $rowCredit = !empty($moduleRow->credit) ? $moduleRow->credit : (isset($moduleRow->coef) ? $moduleRow->coef : 0);
                $rowSemester = isset($moduleRow->semestre) ? $moduleRow->semestre : '';
                $rowNote = null;
                foreach ($releve->matieres as $item) {
                    if ($rowId !== null && isset($item->id_matiere) && (int) $item->id_matiere === (int) $rowId && is_numeric($item->note)) {
                        $rowNote = $item->note;
                        break;
                    }
                }
            ?>
            <?php
                $rowGradePoint = $rowNote !== null ? getGrade($rowNote)[1] : null;
                $rowWeightedPoint = $rowGradePoint !== null ? $rowGradePoint * (float) $rowCredit : null;
            ?>
            <tr>
                <td>{{ $rowCode }}</td>
                @if($isSecondSemester)
                    <td style="padding-left:20px">{{ $rowName }}</td>
                @else
                    <td></td><td style="padding-left:20px">{{ $rowName }}</td>
                @endif
                @if($isSecondSemester)
                    <td style="text-align:center">{{ number_format((float)$rowCredit, 0) }}</td>
                    <td style="text-align:center">{{ $rowNote !== null ? number_format((float)$rowNote / 5, 2) : '' }}</td>
                    <td style="text-align:center">{{ $rowNote !== null ? number_format((float)$rowNote, 2) : '' }}</td>
                    <td style="text-align:center">{{ $rowNote !== null ? getGrade($rowNote)[0] : '' }}</td>
                    <td style="text-align:center">{{ $rowGradePoint !== null ? number_format($rowGradePoint, 1) : '' }}</td>
                    <td style="text-align:center">{{ $rowWeightedPoint !== null ? number_format($rowWeightedPoint, 1) : '' }}</td>
                    <td style="text-align:center">{{ session('year') }}</td>
                @else
                    <td style="text-align:center">{{ $rowSemester !== '' ? getSemestre($rowSemester) : '' }}</td>
                    <td style="text-align:center">{{ session('year') }}</td>
                    <td style="text-align:center">{{ number_format((float)$rowCredit, 0) }}</td>
                    <td style="text-align:center">{{ $rowNote !== null ? number_format((float)$rowNote / 5, 2) : '' }}</td>
                    <td style="text-align:center">{{ $rowNote !== null ? getGrade($rowNote)[1] : '' }}</td>
                    <td style="text-align:center">{{ $rowNote !== null ? getGrade($rowNote)[0] : '' }}</td>
                    <td style="text-align:center">{{ $rowNote !== null ? getMention($rowNote, 100) : '' }}</td>
                @endif
            </tr>
        @endforeach
    @endforeach
    </tbody>
</table>
@endif
