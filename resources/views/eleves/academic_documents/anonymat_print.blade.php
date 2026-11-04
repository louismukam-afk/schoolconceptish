@if($document==='anonymat_fiches')
@foreach($anonymat_rows as $row)
<section class="individual">
@include('eleves.academic_documents.header')
<h3>Fiche individuelle d’anonymat</h3>
<p><strong>Étudiant :</strong> {{ $row->student->nom }} {{ $row->student->prenom }} · <strong>Matricule :</strong> {{ $row->student->matricule }}</p>
<p><strong>Matière :</strong> {{ $anonymat_session->nom }} · S{{ $anonymat_session->semestre }} · {{ $anonymat_sequence<=4?'Session normale':'Rattrapage' }} · {{ $anonymat_mode==='examen'?'Code commun au semestre':'Code par matière' }}</p>
<p style="font-size:24px;text-align:center"><strong>{{ $row->code }}</strong></p>
<p>Reporter ce code sur la copie selon les instructions du centre d’examen. Cette fiche contient l’identité et doit être conservée séparément de la copie destinée à la correction.</p>
<div class="signature"><span>Le responsable des examens</span><span>Signature de l’étudiant</span></div>
</section>
@endforeach
@else
@include('eleves.academic_documents.header')
<p><strong>Matière :</strong> {{ $anonymat_session->nom }} · S{{ $anonymat_session->semestre }} · {{ $anonymat_sequence<=4?'Session normale':'Rattrapage' }} · {{ $anonymat_mode==='examen'?'Code commun au semestre':'Code par matière' }}</p>
@if($document==='anonymat_codes')
<p><strong>CONFIDENTIEL — Correspondances réservées à l’administration des examens.</strong></p>
@endif
<table><thead><tr><th>N°</th>
@if($document!=='anonymat_presence')
<th>Code d’anonymat</th>
@endif
@if($document!=='anonymat_report')
<th>Matricule</th><th>Nom et prénom</th>
@endif
@if($document==='anonymat_report')
<th>Note /20</th><th>Observations</th>
@elseif($document==='anonymat_presence')
<th>Signature</th><th>Observations</th>
@endif
</tr></thead><tbody>
@foreach($anonymat_rows as $i=>$row)
<tr><td>{{ $i+1 }}</td>
@if($document!=='anonymat_presence')
<td>{{ $row->code }}</td>
@endif
@if($document!=='anonymat_report')
<td>{{ $row->student->matricule }}</td><td class="left">{{ $row->student->nom }} {{ $row->student->prenom }}</td>
@endif
@if(in_array($document,array('anonymat_report','anonymat_presence')))
<td style="height:25px"></td><td></td>
@endif
</tr>
@endforeach
</tbody></table>
<div class="signature"><span>Le responsable des examens</span><span>Le surveillant / correcteur</span></div>
@endif
@if(!count($anonymat_rows))
@if($document==='anonymat_fiches')
@include('eleves.academic_documents.header')
@endif
<p class="empty">Aucun code attribué pour cette sélection. Générez les anonymats avant d’éditer les fiches.</p>
@endif