<section class="individual certificate certificate-isabee" aria-label="Attestation de r&eacute;ussite ISABEE">
    <?php
        $juryTimestamp = DateTime::createFromFormat('d/m/Y', $attestation_jury_date);
        $months = array(1=>'janvier',2=>'fevrier',3=>'mars',4=>'avril',5=>'mai',6=>'juin',7=>'juillet',8=>'aout',9=>'septembre',10=>'octobre',11=>'novembre',12=>'decembre');
        $juryDate = $juryTimestamp ? $juryTimestamp->format('j').' '.$months[(int)$juryTimestamp->format('n')].' '.$juryTimestamp->format('Y') : $attestation_jury_date;
        $filiere = isset($isabee_filiere) && $isabee_filiere ? $isabee_filiere->nom : $context['nniveau']->nom;
        $birthDate = $student->date_naiss ? date('d-m-Y', strtotime($student->date_naiss)) : '........................';
    ?>
    <div class="isabee-certificate-border" aria-hidden="true"></div>
    <i class="isabee-corner isabee-corner-tl" aria-hidden="true"></i><i class="isabee-corner isabee-corner-tr" aria-hidden="true"></i><i class="isabee-corner isabee-corner-bl" aria-hidden="true"></i><i class="isabee-corner isabee-corner-br" aria-hidden="true"></i>
    @if($logo_faculte)<img class="isabee-watermark" src="{{ $logo_faculte }}" alt="" aria-hidden="true">@endif
    <div class="isabee-certificate-top">
        <div lang="fr"><strong>Republique du Cameroun</strong><br>Paix &ndash; Travail &ndash; Patrie<br>********<br>Minist&egrave;re de l&rsquo;Enseignement Sup&eacute;rieur<br>****<br>UNIVERSIT&Eacute; D&rsquo;EBOLOWA<br>INSTITUT SUPERIEUR D&rsquo;AGRICULTURE, DU BOIS, DE L&rsquo;EAU ET DE L&rsquo;ENVIRONNEMENT (ISABEE)<br>www.univ-Ebolowa-isabee.com<br>Email : isabee@gmail.com<br>BP: 118 Ebolowa (Cameroun)<br>T&eacute;l./Fax : (237) 695320131/656016406</div>
        <div class="isabee-certificate-logo">@if($logo_campus)<img src="{{ $logo_campus }}" alt="Logo de l'ISH">@endif</div>
        <div class="isabee-certificate-logo">@if($logo_faculte)<img src="{{ $logo_faculte }}" alt="Logo de l'Universit&eacute; d'Ebolowa">@endif</div>
        <div class="isabee-certificate-logo">@if($logo_tutelle)<img src="{{ $logo_tutelle }}" alt="Logo ISABEE">@endif</div>
        <div lang="en"><strong>Republic of Cameroon</strong><br>Peace &ndash; Work &ndash; Fatherland<br>********<br>Ministry of Higher Education<br>****<br>UNIVERSITY OF EBOLOWA<br>HIGHER INSTITUTE OF AGRICULTURE, FORESTRY, WATER AND ENVIRONMENT (HIAFWE)<br>www.univ-Ebolowa-isabee.com<br>Email : isabee@gmail.com<br>PO.BOX: 118 Ebolowa (Cameroon)<br>phone./Fax : (237) 695320131/656016406</div>
    </div>
    <div class="isabee-certificate-heading">
        <h1>ATTESTATION DE R&Eacute;USSITE INGENIEUR DE TRAVAUX N&deg;{{ $attestation_reference ?: '........................' }}</h1>
        <strong>ENGINEER SUCCESS TESTIMONIAL</strong>
    </div>
    <div class="isabee-certificate-body">
        <p class="isabee-certify">Le Directeur de <strong>l'INSTITUT SUP&Eacute;RIEUR D'AGRICULTURE, DU BOIS, DE L'EAU ET DE L'ENVIRONNEMENT (ISABEE)</strong> et le Directeur de <strong>l'INSTITUT SUP&Eacute;RIEUR HINTEL</strong> certifient que : / The Director of <strong>HIGHER INSTITUTE OF AGRICULTURE, FORESTRY, WATER AND ENVIRONMENT (HIAFWE)</strong> and the Chairman of the <strong>HINTEL HIGHER INSTITUTE</strong> hereby certify that:</p>
        <div class="isabee-certificate-identity"><div><strong>M. / Mlle : {{ $student->nom }} {{ $student->prenom }}</strong><br>Mr/Mss</div><div><strong>Date de naissance : {{ $birthDate }}</strong><br>Born on</div><div><strong>Lieu de naissance : {{ $student->lieu_naiss ?: '........................' }}</strong><br>At</div><div><strong>Matricule : {{ $student->matricule }}</strong><br>Registration</div></div>
        <p>Vu la r&eacute;glementation en vigueur, / Mindful of the regulations in force,</p>
        <p>Vu l'Accord-Cadre du 15 Avril 2024 entre <strong>L'UNIVERSIT&Eacute; D'EBOLOWA</strong> et <strong>L'INSTITUT SUP&Eacute;RIEUR HINTEL</strong> / Mindful of the Cooperation Agreement of April 15, 2024 between the <strong>UNIVERSITY OF EBOLOWA</strong> and the <strong>HINTEL HIGHER INSTITUTE</strong>,</p>
        <p>Vu la Convention Sp&eacute;cifique entre <strong>L'UNIVERSIT&Eacute; D'EBOLOWA</strong> &agrave; travers <strong>L'INSTITUT SUP&Eacute;RIEUR D'AGRICULTURE, DU BOIS, DE L'EAU ET DE L'ENVIRONNEMENT (ISABEE)</strong> et <strong>L'INSTITUT SUP&Eacute;RIEUR HINTEL (ISH)</strong> relative aux formations pr&eacute;parant aux dipl&ocirc;mes d'<strong>INGENIEUR DE TRAVAUX</strong> dans les fili&egrave;res <strong>{{ mb_strtoupper($filiere, 'UTF-8') }}</strong> en date du 15 Avril 2024, / Mindful of the Specific Agreement between the <strong>UNIVERSITY OF EBOLOWA</strong> through its <strong>HIGHER INSTITUTE OF AGRICULTURE, FORESTRY, WATER AND ENVIRONMENT</strong> and the <strong>HINTEL HIGHER INSTITUTE</strong> on courses in preparation for the award of an <strong>ENGINEER OF WORKS</strong> of April 15, 2024,</p>
        <p>Vu le proc&egrave;s-verbal du jury en date du <strong>{{ $juryDate }}</strong> / Mindful of the official report of the jury of <strong>{{ $juryDate }}</strong>,</p>
        <p>A suivi avec succ&egrave;s la formation pour l'obtention du dipl&ocirc;me de <strong>INGENIEUR DE TRAVAUX</strong>, fili&egrave;re <strong>{{ mb_strtoupper($filiere, 'UTF-8') }}</strong>, parcours <strong>{{ mb_strtoupper($context['classe']->nom, 'UTF-8') }}</strong> / Has successfully completed the course work for award of an <strong>ENGINEER</strong> in Specialization <strong>{{ mb_strtoupper($filiere, 'UTF-8') }}</strong>.</p>
        <div class="isabee-certificate-results"><div>Ann&eacute;e acad&eacute;mique : {{ $context['year'] }}</div><div>Mention Globale : {{ $r['summary']['mention'] }} / {{ isset($mentionEn[$r['summary']['mention']]) ? $mentionEn[$r['summary']['mention']] : $r['summary']['mention'] }}</div></div>
        <p>En foi de quoi, la pr&eacute;sente attestation lui est d&eacute;livr&eacute;e pour servir et valoir ce que de droit. / This certificate is, therefore, issued to the above named to serve the purposes for which it is intended.</p>
    </div>
    <div class="isabee-certificate-signatures"><strong>Le Directeur de l'Institut Sup&eacute;rieur Hintel</strong><strong>Le Directeur de l'ISABEE</strong></div>
    <div class="isabee-certificate-issued">EBOLOWA, le {{ $attestation_issue_date }}</div>
    <div class="isabee-certificate-bottom">NB : Il n'est d&eacute;livr&eacute; qu'un seul exemplaire de relev&eacute; de notes. Le titulaire peut &eacute;tablir et faire certifier des copies conformes. Only one transcript shall be delivered. It is the owner's interest to make certified true copies.</div>
</section>
