<?php
    $tutelle=$context['tutelle']; $campus=$context['campus']; $student=$r['student'];
    $degree=$context['cycle']->nom;
    $degreeEn=stripos($degree,'licence')!==false?"BACHELOR’S DEGREE":(stripos($degree,'master')!==false?'MASTER’S DEGREE':(stripos($degree,'BTS')!==false?'HIGHER NATIONAL DIPLOMA':$degree));
    $mentionEn=array('Très bien'=>'Very good','Bien'=>'Good','Assez bien'=>'Fairly good','Passable'=>'Pass');
    $birth=$student->date_naiss; if($birth && preg_match('/^\d{4}-\d{2}-\d{2}/',$birth))$birth=date('d/m/Y',strtotime($birth));
?>
<section class="individual certificate" aria-label="Attestation {{ $context['profile']['label'] }}">
    @if($context['modele']==='ngaoundere')
        @include('eleves.academic_documents.attestation_ngaoundere_header')
    @else
    <div class="certificate-top {{ $context['modele']==='polytechnique'?'certificate-polytechnique-top':'' }}">
        <div class="certificate-institution" lang="fr">
            <strong>{{ $tutelle->republique_francophone ?: 'RÉPUBLIQUE DU CAMEROUN' }}</strong><br>
            {{ $tutelle->patrie_francophone ?: 'Paix – Travail – Patrie' }}<br>
            {{ $tutelle->ministere_tutelle ?: 'Ministère de l’Enseignement Supérieur' }}<br>
            <strong>{{ $tutelle->nom }}</strong><br>{{ $tutelle->faculte }}<br>
            <strong>{{ $campus->nom }}</strong><br>
            B.P. : {{ $campus->boite_postale ?: config('academic_stationery.boite_postale') }}<br>
            Tél. : {{ $campus->telephone ?: config('academic_stationery.telephone') }}<br>
            {{ config('academic_stationery.email') }}<br>{{ $campus->site_web ?: config('academic_stationery.site_web') }}
        </div>
        <div class="certificate-campus"><img src="{{ $logo_campus ?: URL::asset('img/logo.jpg') }}" alt="Logo du campus"></div>
        @if($context['modele']==='polytechnique')
        <div class="certificate-polytechnique"><img src="{{ $logo_polytechnique ?: URL::asset('images/tutelles/testok/polytechnique.png') }}" alt="Logo Polytechnique ENSPY"></div>
        @endif
        <div class="certificate-tutelle">
            @if($logo_tutelle)<img src="{{ $logo_tutelle }}" alt="Logo tutelle">@endif
            @if($logo_faculte && $context['modele']!=='polytechnique')<img src="{{ $logo_faculte }}" alt="Logo faculté">@endif
        </div>
        <div class="certificate-institution" lang="en">
            <strong>{{ $tutelle->republique_anglophone ?: 'REPUBLIC OF CAMEROON' }}</strong><br>
            {{ $tutelle->patrie_anglophone ?: 'Peace – Work – Fatherland' }}<br>
            {{ $tutelle->ministere_tutelle_anglophone ?: 'Ministry of Higher Education' }}<br>
            <strong>{{ $tutelle->tutelle_anglophone ?: $tutelle->nom }}</strong><br>{{ $tutelle->faculte_anglophone }}<br>
            <strong>{{ $campus->nom }}</strong><br>
            P.O. Box: {{ $campus->boite_postale ?: config('academic_stationery.boite_postale') }}<br>
            Tel.: {{ $campus->telephone ?: config('academic_stationery.telephone') }}<br>
            {{ config('academic_stationery.email') }}<br>{{ $campus->site_web ?: config('academic_stationery.site_web') }}
        </div>
    </div>
    @endif
    <div class="certificate-heading">
        <h1>ATTESTATION DE RÉUSSITE — {{ $degree }} <small>N° {{ $attestation_reference ?: '……………………' }}</small></h1>
        <div class="certificate-english">{{ $degreeEn }} SUCCESS TESTIMONIAL</div>
    </div>
    <div class="certificate-content">
        <p class="certificate-certification">{{ $certificate_authority['authority_fr'] }} de <strong>{{ $tutelle->nom }}</strong> et le Président du Conseil d’Établissement de <strong>{{ $campus->nom }}</strong> certifient que :<br>
            <em lang="en">{{ $certificate_authority['authority_en'] }} of {{ $tutelle->tutelle_anglophone ?: $tutelle->nom }} and the President of the School Council of {{ $campus->nom }} hereby certify that:</em>
        </p>
        <div class="certificate-identity">
            <div>M. / Mlle : <strong>{{ $student->nom }} {{ $student->prenom }}</strong><br><em>Mr / Miss</em></div>
            <div>Matricule N° : <strong>{{ $student->matricule }}</strong><br><em>Registration number</em></div>
        </div>
        <div class="certificate-birth">Né(e) le : <strong>{{ $birth ?: '……………………' }}</strong> <span>à : <strong>{{ $student->lieu_naiss ?: '……………………' }}</strong></span><br><em>Born on <span>at</span></em></div>
        @if($context['modele']==='ngaoundere')
            @include('eleves.academic_documents.attestation_ngaoundere_references')
        @endif
        <p>Vu le procès-verbal du jury en date du <strong>{{ $attestation_jury_date }}</strong><br><em lang="en">Mindful of the official report of the jury dated {{ $attestation_jury_date }}</em></p>
        <p>A suivi avec succès la formation en <strong>{{ $degree }}</strong>, spécialité <strong>{{ $context['classe']->nom }}</strong>, niveau <strong>{{ $context['nniveau']->nom }}</strong>.<br>
            <em lang="en">Has successfully completed the course in {{ $degreeEn }}, specialising in {{ $context['classe']->nom }}, level {{ $context['nniveau']->nom }}.</em>
        </p>
        <div class="certificate-results">
            <div>Année académique : <strong>{{ $context['year'] }}</strong><br><em>Academic year</em></div>
            <div>Mention globale : <strong>{{ $r['summary']['mention'] }}</strong><br><em>Overall grade: {{ isset($mentionEn[$r['summary']['mention']])?$mentionEn[$r['summary']['mention']]:$r['summary']['mention'] }}</em></div>
            <div>Moyenne : <strong>{{ number_format($r['summary']['average'],2) }}/20</strong><br><em>Average</em></div>
        </div>
        @if($date_soutenance)<p>Date de soutenance / Defence date : {{ date('d/m/Y',strtotime($date_soutenance)) }}</p>@endif
        <p>En foi de quoi, la présente attestation est délivrée pour servir et valoir ce que de droit.<br><em lang="en">This certificate is issued to the above named to serve the purposes for which it is intended.</em></p>
    </div>
    <div class="certificate-issued">{{ isset($certificate_authority['city'])?$certificate_authority['city']:($campus->ville ?: 'Yaoundé') }}, le {{ $attestation_issue_date }}<br><em>Issued on</em></div>
    <div class="certificate-signatures">
        <div><strong>{{ isset($certificate_authority['campus_signatory_fr'])?$certificate_authority['campus_signatory_fr']:'Le Président du Conseil d’Établissement' }}</strong><br><em>{{ isset($certificate_authority['campus_signatory_en'])?$certificate_authority['campus_signatory_en']:'The President of the School Council' }}</em><div class="certificate-signature-space"></div></div>
        <div><strong>{{ $certificate_authority['authority_fr'] }}</strong><br><em>{{ $certificate_authority['authority_en'] }}</em><div class="certificate-signature-space"></div></div>
    </div>
    <div class="certificate-bottom">
        <p>Il n’est délivré qu’un seul exemplaire de cette attestation. Le titulaire peut faire certifier des copies conformes.<br><em>Only one original is issued. The holder may obtain certified true copies.</em></p>
        <p class="certificate-legal">{{ $campus->autorisation_ouverture ?: config('academic_stationery.autorisation') }} · {{ $campus->arrete_creation ?: config('academic_stationery.arrete') }}</p>
        @include('eleves.academic_documents.footer')
    </div>
</section>