<?php namespace gesecole;

use Illuminate\Database\Eloquent\Model;

class SeanceCours extends Model {
    protected $table='seance_cours';
    public $fillable=['id_cours_enseignant','id_plage_horaire','jour_semaine','duree_heures','statut','id_user'];

    public function cours(){ return $this->belongsTo(CoursEnseignant::class,'id_cours_enseignant'); }
    public function plage(){ return $this->belongsTo(PlageHoraire::class,'id_plage_horaire'); }
}
