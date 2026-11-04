<!DOCTYPE html>
<html lang="fr"><head><meta charset="utf-8"><title>{{ $label }} — {{ $context['profile']['label'] }} — {{ strtoupper($page_size) }}</title>
<style>
@page {
    size: {{ strtoupper($page_size) }} {{ $paper_orientation }};
    margin: {{ $paper_margin }}mm {{ $paper_margin }}mm {{ $certificate?$paper_margin:($page_size==='a3'?28:24) }}mm;
    @if(!$certificate)
    @bottom-center {
        content:
        @foreach($footer_lines as $line)
            {!! json_encode(str_replace(array("\r","\n"),' ',$line),JSON_UNESCAPED_UNICODE) !!} "\A"
        @endforeach
        ;
        white-space:pre-line;font:{{ $page_size==='a3'?10:8 }}px Arial,sans-serif;line-height:1.4;text-align:center;vertical-align:middle;border-top:1px solid #333;
    }
    @endif
}
body{font:11px Arial,sans-serif;color:#111;margin:16px}table{border-collapse:collapse;width:100%;margin:12px 0}td,th{border:1px solid #222;padding:5px;text-align:center}th{background:#eee}.left{text-align:left}.document-header{display:flex;align-items:center;justify-content:space-between;gap:15px;text-align:center}.document-header>div{flex:1}.logos img{max-width:80px;max-height:75px;object-fit:contain}.school{text-align:center;margin:12px}.school strong{font-size:17px}h2{text-align:center;font-size:16px}.context{text-align:center;line-height:1.6}.signature{display:flex;justify-content:space-around;margin:35px 0 60px}.individual{break-after:page;page-break-after:always}.individual:last-of-type{break-after:auto;page-break-after:auto}.incomplete{background:#fff5d8}.toolbar{padding:12px;background:#eef3f8;margin-bottom:15px}.summary{font-weight:bold}.preview{text-align:center;color:#9e1717;font-size:14px}.notice{font-size:10px;margin-top:15px}tr{break-inside:avoid}thead{display:table-header-group}.attestation{font-size:16px;line-height:2.2;padding:25px}.empty{padding:30px;text-align:center}
@media print{.toolbar{display:none}body{margin:0}.preview{color:#555}a{color:inherit}}
.document-pv table,.document-pv_groupes table{font-size:8px;table-layout:fixed}.document-pv td,.document-pv th,.document-pv_groupes td,.document-pv_groupes th{padding:3px;overflow-wrap:anywhere}.document-pv th:nth-child(3),.document-pv_groupes th:nth-child(3){width:110px}
.document-header{display:block;break-inside:avoid}.branding-row{display:flex;align-items:center;justify-content:space-between;gap:12px;min-height:85px}.campus-logo-block{flex:0 0 100px;text-align:left}.campus-logo{max-width:95px;max-height:85px;object-fit:contain}.branding-title{flex:1;text-align:center}.branding-title strong{font-size:17px}.branding-title span{font-size:10px}.tutelle-logos{flex:0 0 140px;display:flex;align-items:center;justify-content:flex-end;gap:6px}.tutelle-logos img{max-width:65px;max-height:75px;object-fit:contain}.official-headings{display:flex;justify-content:space-between;gap:20px;margin-top:8px;font-size:10px;line-height:1.4}.official-headings>div{flex:1}.school{line-height:1.5;margin:8px}.school span{font-size:10px}.document-footer{border-top:1px solid #333;text-align:center;font-size:9px;line-height:1.4;margin-top:20px;padding-top:5px;break-inside:avoid}.document-footer p{margin:0}.transcript-notice{font-style:italic}
@media print{.document-footer{display:none}}
.official-headings{align-items:flex-start;margin:0 0 10px}.official-fr{text-align:left}.official-en{text-align:right}
.branding-polytechnique .campus-logo-block,.branding-polytechnique .tutelle-logos{flex-basis:100px}.polytechnique-logo{max-width:90px;max-height:85px;object-fit:contain}
@include('eleves.academic_documents.paper')
@if($certificate)
@include('eleves.academic_documents.attestation_style')
@endif
</style></head><body class="modele-{{ $context['modele'] }} document-{{ $document }} paper-{{ $page_size }}">
<div class="toolbar"><button onclick="window.print()">Imprimer / enregistrer en PDF</button> · Format {{ strtoupper($page_size) }} {{ $paper_orientation==='landscape'?'paysage':'portrait' }}
    @if(!$archived) · <a href="{{ $paper_links['a4'] }}" aria-current="{{ $page_size==='a4'?'page':'false' }}">A4</a> · <a href="{{ $paper_links['a3'] }}" aria-current="{{ $page_size==='a3'?'page':'false' }}">A3</a>@endif
    · {{ $archived?'Copie archivée':'Aperçu : utiliser « Générer et archiver » pour conserver ce document' }} · {{ $generated_at }}</div>
@if(!$archived)<p class="preview">APERÇU — NON ARCHIVÉ</p>@endif
@if($certificate && count($results))
    @foreach($results as $r)
        @include('eleves.academic_documents.attestation')
    @endforeach
@elseif(isset($deliberation_report))
@include('eleves.academic_documents.deliberation_print')
@elseif(isset($anonymat_rows))
@include('eleves.academic_documents.anonymat_print')
@elseif(in_array($document,array('releve','releve_modules','attestation')))
    @foreach($results as $r)
    <section class="individual">
        @include('eleves.academic_documents.header')
        <p><strong>Étudiant :</strong> {{ $r['student']->nom }} {{ $r['student']->prenom }} · <strong>Matricule :</strong> {{ $r['student']->matricule }}<br><strong>Né(e) le :</strong> {{ $r['student']->date_naiss }} à {{ $r['student']->lieu_naiss }}</p>
        @if($document==='attestation')
        <div class="attestation">Il est attesté que <strong>{{ $r['student']->nom }} {{ $r['student']->prenom }}</strong>, inscrit(e) en {{ $context['classe']->nom }}, cycle {{ $context['cycle']->nom }}, niveau {{ $context['nniveau']->nom }}, pour l’année académique {{ $context['year'] }}, figure sur la liste des admis selon les règles du modèle {{ $context['profile']['label'] }}.<br>Moyenne : <strong>{{ number_format($r['summary']['average'],2) }}/20</strong>. Crédits capitalisés : <strong>{{ $r['summary']['credits'] }}</strong> sur {{ $r['summary']['expected'] }}.
        @if($date_soutenance)<br>Date de soutenance : {{ $date_soutenance }}.@endif
        </div>
        @else
        @if($document==='releve_modules')
        @include('eleves.academic_documents.releve_modules')
        @else
        <table><thead><tr><th>Code</th><th>Unité / matière</th><th>Semestre</th><th>Crédits</th><th>Note /20</th><th>Grade</th><th>Validation</th><th>Session</th></tr></thead><tbody>
        @foreach($r['subjects'] as $subject)<tr class="{{ !$subject['result']['complete']?'incomplete':'' }}"><td>{{ $subject['code'] }}</td><td class="left">{{ $subject['nom'] }}<br><small>{{ $subject['module'] }}@if($subject['enseignants']) · {{ $subject['enseignants'] }}@endif</small></td><td>{{ $subject['semestre'] }}</td><td>{{ $subject['credit'] }}</td><td>{{ $subject['result']['final']===null?'Absente':number_format($subject['result']['final'],2) }}</td><td>{{ $subject['result']['grade'] }}</td><td>{{ $subject['result']['valid']?'V':'NV' }}</td><td>{{ $subject['result']['session'] }}</td></tr>@endforeach
        </tbody></table>
        @endif
        <p class="summary">Moyenne : {{ $r['summary']['incomplete']?'Dossier incomplet':number_format($r['summary']['average'],2).'/20' }} · Crédits capitalisés : {{ $r['summary']['credits'] }} / {{ $r['summary']['expected'] }} · Décision : {{ $r['summary']['decision'] }}</p>
        @if(!$r['summary']['incomplete'])
        <p><strong>Grade :</strong> {{ $r['summary']['grade'] }} · <strong>Mention :</strong> {{ $r['summary']['mention'] }}
            @if(in_array($context['modele'],array('isabee','soa','polytechnique')))
                · <strong>MGP :</strong> {{ number_format($r['summary']['mgp'],2) }} /4
            @endif
        </p>
        @endif
        @endif
        <div class="signature"><span>Le responsable académique</span><span>Le représentant de la tutelle</span></div>
        <p class="notice">N = session normale ; R = rattrapage ; V = unité validée à 10/20. La capitalisation des crédits suit les règles du modèle retenu. Document établi le {{ $generated_at }}.</p>
    </section>
    @endforeach
@else
    @include('eleves.academic_documents.header')
    @if($document==='pv_modules')
    @include('eleves.academic_documents.pv_modules')
    @elseif(in_array($document,array('admis','merite','non_admis','a_deliberer')))
    <table><thead><tr><th>N°</th><th>Rang</th><th>Matricule</th><th>Nom et prénom</th><th>Moyenne /20</th><th>Crédits capitalisés</th><th>Décision</th></tr></thead><tbody>
    @foreach($results as $i=>$r)<tr><td>{{ $i+1 }}</td><td>{{ $r['rank'] }}</td><td>{{ $r['student']->matricule }}</td><td class="left">{{ $r['student']->nom }} {{ $r['student']->prenom }}</td><td>{{ $r['summary']['incomplete']?'Incomplet':number_format($r['summary']['average'],2) }}</td><td>{{ $r['summary']['credits'] }}</td><td>{{ $r['summary']['decision'] }}</td></tr>@endforeach
    </tbody></table>
    @elseif($document==='programme')
    <table><thead><tr><th>Session</th><th>Code</th><th>Matière / UE</th><th>Semestre</th><th>Crédits</th><th>CC %</th><th>TPE %</th><th>SN %</th><th>Rattrapage %</th></tr></thead><tbody>
    @foreach($sessions as $s)<?php $parity=(int)$s->semestre%2?1:2; ?><tr><td>{{ $s->id }}</td><td>{{ $s->code }}</td><td class="left">{{ $s->nom }}<br>{{ $s->module }}</td><td>{{ $s->semestre }}</td><td>{{ $s->credit }}</td><td>{{ $s->{'cc'.$parity} }}</td><td>{{ $s->{'tpe'.$parity} }}</td><td>{{ $s->{'sn'.$parity} }}</td><td>{{ $s->{'ratrappage'.$parity} }}</td></tr>@endforeach
    </tbody></table>
    @elseif($document==='statistiques')
    <?php $stats=$context['periode']==='annuel'?array('ADMIS'=>0,'NON ADMIS'=>0,'A DELIBERER'=>0):array('SEMESTRE EVALUE'=>0,'A DELIBERER'=>0);foreach($results as $r)$stats[$r['summary']['decision']]++; ?>
    <table><thead><tr><th>Décision</th><th>Nombre</th><th>Pourcentage</th></tr></thead><tbody>@foreach($stats as $decision=>$count)<tr><td>{{ $decision }}</td><td>{{ $count }}</td><td>{{ count($results)?number_format($count*100/count($results),2):'0' }} %</td></tr>@endforeach<tr><th>Total</th><th>{{ count($results) }}</th><th>100 %</th></tr></tbody></table>
    @elseif(in_array($document,array('presence','anonymat','report','matiere_cc','matiere_sn','matiere_tpe','matiere_rat','rattrapage','rattrapage_effectue','rattrapage_global')))
        @foreach($sessions as $session)
        <h3>{{ $session->code }} — {{ $session->nom }} · S{{ $session->semestre }} · session {{ $session->id }}</h3>
        <table><thead><tr><th>N°</th><th>Matricule</th><th>Nom et prénom</th>
        @if($document==='presence')<th>Signature</th><th>Observations</th>
        @elseif($document==='anonymat')<th>Anonymat</th><th>Signature</th>
        @else<th>CC /20</th><th>TPE /20</th><th>SN /20</th><th>RAT /20</th><th>Final /20</th><th>Session</th>@endif
        </tr></thead><tbody>
        <?php $number=0; ?>
        @foreach($results as $r)
        @foreach($r['subjects'] as $subject)
        @if($subject['session_id']==$session->id && $subject['semestre']==$session->semestre)
        <?php
            $res=$subject['result'];$skip=false;
            if($document==='rattrapage' && $res['normal']!==null && $res['normal']>=10)$skip=true;
            if($document==='rattrapage_effectue' && $res['components']['rat']===null)$skip=true;
            $seq=(int)$subject['semestre']%2?3:4;
            $meta=isset($metadata[$r['student']->id][$session->id][$seq])?$metadata[$r['student']->id][$session->id][$seq]:null;
        ?>
        @if(!$skip)
        <tr><td>{{ ++$number }}</td><td>{{ $r['student']->matricule }}</td><td class="left">{{ $r['student']->nom }} {{ $r['student']->prenom }}</td>
        @if($document==='presence')<td style="height:24px"></td><td></td>
        @elseif($document==='anonymat')<td>{{ $meta?$meta->anonymat:'' }}</td><td></td>
        @else
            @foreach(array('cc','tpe','sn','rat') as $component)<td>{{ $res['components'][$component]!==null && $subject['weights'][$component]>0?number_format($res['components'][$component]*20/$subject['weights'][$component],2):'—' }}</td>@endforeach
            <td>{{ $res['final']===null?'Incomplet':number_format($res['final'],2) }}</td><td>{{ $res['session'] }}</td>
        @endif
        </tr>
        @endif
        @endif
        @endforeach
        @endforeach
        </tbody></table>
        @endforeach
    @else
        <table class="results-matrix"><thead><tr><th>N°</th><th>Matricule</th><th>Nom et prénom</th>
        @foreach($sessions as $s)<th>{{ $s->code ?: $s->nom }}<br>S{{ $s->semestre }} · {{ $s->credit }} cr.</th>@endforeach
        <th>Moyenne /20</th><th>Crédits</th><th>Décision</th></tr></thead><tbody>
        @foreach($results as $i=>$r)<tr><td>{{ $i+1 }}</td><td>{{ $r['student']->matricule }}</td><td class="left">{{ $r['student']->nom }} {{ $r['student']->prenom }}</td>@foreach($r['subjects'] as $subject)<td>{{ $subject['result']['final']===null?'—':number_format($subject['result']['final'],2) }} {{ $subject['result']['session'] }}</td>@endforeach<td>{{ $r['summary']['incomplete']?'Incomplet':number_format($r['summary']['average'],2) }}</td><td>{{ $r['summary']['credits'] }}</td><td>{{ $r['summary']['decision'] }}</td></tr>@endforeach
        </tbody></table>
        @if($document==='pv_groupes')
        <h3>Synthèse par unité d’enseignement</h3>
        <table><thead><tr><th>Étudiant</th><th>UE</th><th>Moyenne pondérée /20</th><th>Crédits prévus</th><th>Crédits capitalisés</th></tr></thead><tbody>
        @foreach($results as $r)
        <?php $groups=array();foreach($r['subjects'] as $s){$key=$s['module'];if(!isset($groups[$key]))$groups[$key]=array('sum'=>0,'credits'=>0,'capitalized'=>0,'complete'=>true);$groups[$key]['credits']+=$s['credit'];if(!$s['result']['complete'])$groups[$key]['complete']=false;else{$groups[$key]['sum']+=$s['result']['final']*$s['credit'];if($s['result']['final']>=$context['profile']['credit_threshold'])$groups[$key]['capitalized']+=$s['credit'];}} ?>
        @foreach($groups as $name=>$g)<tr><td>{{ $r['student']->nom }} {{ $r['student']->prenom }}</td><td>{{ $name }}</td><td>{{ $g['complete'] && $g['credits']>0?number_format($g['sum']/$g['credits'],2):'Incomplet' }}</td><td>{{ $g['credits'] }}</td><td>{{ $g['capitalized'] }}</td></tr>@endforeach
        @endforeach
        </tbody></table>
        @endif
    @endif
    <div class="signature"><span>Le responsable de filière</span><span>Le président du jury</span><span>Le représentant de la tutelle</span></div>
@endif
@if(!isset($deliberation_report) && !isset($anonymat_rows) && !count($results))@if(in_array($document,array('releve','releve_modules','attestation')))@include('eleves.academic_documents.header')@endif<p class="empty">Aucun étudiant ne correspond à ce document et aux filtres choisis.</p>@endif
@if(!$certificate && !isset($deliberation_report) && !isset($anonymat_rows))
<p class="notice">Règles {{ $context['profile']['label'] }} : UE validée à 10/20 ; capitalisation dès {{ $context['profile']['credit_threshold'] }}/20 ; {{ $context['profile']['admission']==='credits'?'admission à partir de '.$context['profile']['credits_required'].' crédits':'admission pour une moyenne strictement supérieure à '.$context['profile']['average_required'].'/20' }}. Une donnée manquante impose une délibération. Établi le {{ $generated_at }}.</p>
@endif
@if(!$certificate || !count($results))
@include('eleves.academic_documents.footer')
@endif
</body></html>
