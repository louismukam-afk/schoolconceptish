<?php namespace gesecole;

use Illuminate\Database\Eloquent\Model;

class CoursEnseignant extends Model {
    protected $table='cours_enseignants';
    public $fillable=['id_personnel','id_cycle','id_niveau','id_nniveau','id_classe','id_matiere','id_taux','id_trimestre','date_debut','date_fin','semestre','volume_horaire_prevu','annee_scolaire','statut','id_user'];

    public function personnel(){ return $this->belongsTo(personnel::class,'id_personnel'); }
    public function cycle(){ return $this->belongsTo(cycle::class,'id_cycle'); }
    public function filiere(){ return $this->belongsTo(niveau::class,'id_niveau'); }
    public function nniveau(){ return $this->belongsTo(NNiveau::class,'id_nniveau'); }
    public function classe(){ return $this->belongsTo(classe::class,'id_classe'); }
    public function matiere(){ return $this->belongsTo(matiere::class,'id_matiere'); }
    public function trimestre(){ return $this->belongsTo(trimestre::class,'id_trimestre'); }
    public function seances(){ return $this->hasMany(SeanceCours::class,'id_cours_enseignant'); }
}
