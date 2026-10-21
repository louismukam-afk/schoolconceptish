<?php namespace gesecole\Services;

use gesecole\acces_eleve;
use gesecole\Vars;
use Illuminate\Support\Facades\Schema;

class EnregistrementService {
  /**
   * Create a new scholarship entry
   * @param \gesecole\bourse $bourse
   * @return \gesecole\bourse
   */
  public static function getStudentAccessStatus($id, $idCampus = null) {
    $status = Vars::DECISION_UNSET;
    $acces = acces_eleve::where('id_eleve', '=', $id)
      ->where('annee_scolaire', '=', session('year'));

    if ($idCampus && Schema::hasColumn('acces_eleves', 'id_campus')) {
      $acces->where('id_campus', '=', $idCampus);
    }

    $acces = $acces->first();

    if (!$acces) {
      return Vars::DECISION_UNSET;
    }

    $currentDate = date('Y-m-d');

    if ($acces->date_debut && $acces->date_fin) {
      if ($acces->date_debut <= $currentDate && $acces->date_fin >= $currentDate && $acces->decision == Vars::DECISION_ALLOW) {
        $status = Vars::DECISION_ALLOW;
      } else {
        $status = Vars::DECISION_DENY;
      }
    } else {
      $status = $acces->decision;
    }

    return $status;
  }
}
