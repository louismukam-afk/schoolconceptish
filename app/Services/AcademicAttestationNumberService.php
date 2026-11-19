<?php namespace gesecole\Services;

use gesecole\AcademicDocumentGeneration;

class AcademicAttestationNumberService {
    public function references($context, $results)
    {
        $suffixes=array('ngaoundere'=>'UN/IUT/UN/CD-ISH/CS-ISH','soa'=>'/ISH/YDE II - SOA','esstic'=>'/ISH/YDE II-ESSTIC','isabee'=>' UN/ISABEE-UN/CD-ISH/CS-ISH','polytechnique'=>'/ISH/YDE I-ENSPY');
        if(!isset($suffixes[$context['modele']]))throw new \InvalidArgumentException('Modele sans numerotation automatique.');
        $suffix=$suffixes[$context['modele']];
        $existing = array(); $maximum = 0;
        $archives = AcademicDocumentGeneration::withTrashed()
            ->where('id_campus',$context['id_campus'])->where('modele',$context['modele'])
            ->where('annee_academique',$context['year'])->where('document','attestation')
            ->orderBy('id')->get(array('contexte'));
        foreach ($archives as $archive) {
            $snapshot = json_decode($archive->contexte,true);
            if (!is_array($snapshot)) continue;
            if (isset($snapshot['attestation_numbers']) && is_array($snapshot['attestation_numbers'])) {
                foreach ($snapshot['attestation_numbers'] as $studentId=>$reference)
                    $this->remember($existing,$maximum,$studentId,$reference,$suffix);
            }
            if (isset($snapshot['document_selection']['attestation_reference'])) {
                $studentId = isset($snapshot['document_selection']['id_eleve']) ? $snapshot['document_selection']['id_eleve'] : null;
                $this->remember($existing,$maximum,$studentId,$snapshot['document_selection']['attestation_reference'],$suffix);
            }
        }
        $references = array();
        foreach ($results as $result) {
            $studentId = (int) $result['student']->id;
            if (!isset($existing[$studentId])) $existing[$studentId] = str_pad((string)++$maximum,3,'0',STR_PAD_LEFT).$suffix;
            $references[$studentId] = $existing[$studentId];
        }
        return $references;
    }

    private function remember(&$existing, &$maximum, $studentId, $reference, $suffix)
    {
        if (!is_string($reference)) return;
        if (!preg_match('/^(?:N[°º]\s*)?(\d+)'.preg_quote($suffix,'/').'$/u',trim($reference),$matches)) return;
        $maximum = max($maximum,(int)$matches[1]);
        if ($studentId && !isset($existing[(int)$studentId]))
            $existing[(int)$studentId] = str_pad($matches[1],3,'0',STR_PAD_LEFT).$suffix;
    }
}
