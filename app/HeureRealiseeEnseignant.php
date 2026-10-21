<?php namespace gesecole;

use Illuminate\Database\Eloquent\Model;

class HeureRealiseeEnseignant extends Model {
    protected $table='heures_realisees_enseignants';
    public $fillable=['id_biometrie_import','id_cours_enseignant','id_seance_cours','id_personnel','id_cycle','id_niveau','id_nniveau','id_classe','id_matiere','semestre','date_seance','jour_semaine','id_plage_horaire','heure_debut_prevue','heure_fin_prevue','heure_debut_reelle','heure_fin_reelle','duree_prevue','duree_realisee','montant_taux','montant_total','annee_scolaire','statut','observation','id_user'];

    public function import(){ return $this->belongsTo(BiometrieImport::class,'id_biometrie_import'); }
    public function personnel(){ return $this->belongsTo(personnel::class,'id_personnel'); }
    public function cycle(){ return $this->belongsTo(cycle::class,'id_cycle'); }
    public function filiere(){ return $this->belongsTo(niveau::class,'id_niveau'); }
    public function nniveau(){ return $this->belongsTo(NNiveau::class,'id_nniveau'); }
    public function cours(){ return $this->belongsTo(CoursEnseignant::class,'id_cours_enseignant'); }
    public function seance(){ return $this->belongsTo(SeanceCours::class,'id_seance_cours'); }
    public function classe(){ return $this->belongsTo(classe::class,'id_classe'); }
    public function matiere(){ return $this->belongsTo(matiere::class,'id_matiere'); }
    public function plage(){ return $this->belongsTo(PlageHoraire::class,'id_plage_horaire'); }
}
