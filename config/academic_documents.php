<?php

return array(
    // Règles relevées dans les listes d'admission de Test OK. Les seuils de
    // capitalisation (8) et de validation d'une UE (10) restent distincts.
    'profiles' => array(
        'soa' => array('label' => 'SOA', 'credit_threshold' => 8, 'admission' => 'credits', 'credits_required' => 45, 'average' => 'arithmetic'),
        'ngaoundere' => array('label' => 'Ngaoundéré', 'credit_threshold' => 8, 'admission' => 'average_strict', 'average_required' => 10, 'average' => 'weighted_semesters'),
        'esstic' => array('label' => 'ESSTIC', 'credit_threshold' => 8, 'admission' => 'credits', 'credits_required' => 45, 'average' => 'arithmetic'),
        'isabee' => array('label' => 'ISABEE / Ebolowa', 'credit_threshold' => 10, 'admission' => 'credits', 'credits_required' => 45, 'average' => 'weighted'),
        'polytechnique' => array('label' => 'Polytechnique', 'credit_threshold' => 10, 'admission' => 'credits', 'credits_required' => 45, 'average' => 'weighted'),
    ),
    'documents' => array(
        'pv' => 'Procès-verbal détaillé',
        'pv_groupes' => 'Procès-verbal par unités d’enseignement',
        'pv_modules' => 'Procès-verbal par modules (groupes de matières)',
        'admis' => 'Admis par ordre alphabétique',
        'merite' => 'Admis par ordre de mérite',
        'non_admis' => 'Non admis',
        'a_deliberer' => 'Dossiers à délibérer / incomplets',
        'releve' => 'Relevés de notes',
        'releve_modules' => 'Relevés de notes par modules (groupes de matières)',
        'attestation' => 'Attestations de réussite',
        'rattrapage' => 'Étudiants concernés par le rattrapage',
        'rattrapage_effectue' => 'Rattrapages effectués',
        'rattrapage_global' => 'État global des rattrapages',
        'presence' => 'Liste de présence',
        'anonymat_codes' => 'Liste confidentielle des correspondances d’anonymats',
        'anonymat_report' => 'Fiche de report par anonymat (sans identités)',
        'anonymat_presence' => 'Liste de présence des examens anonymés',
        'anonymat_fiches' => 'Fiches individuelles d’anonymat',
        'anonymat' => 'Fiche d’anonymat',
        'report' => 'Fiche de report des notes',
        'deliberation_rapport' => 'Rapport des délibérations',
        'statistiques' => 'Statistiques des résultats',
        'matiere_cc' => 'État des contrôles continus',
        'matiere_sn' => 'État de session normale',
        'matiere_tpe' => 'État des TPE',
        'matiere_rat' => 'État de rattrapage',
        'programme' => 'Programme et pondérations',
    ),
);
