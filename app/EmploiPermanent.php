<?php namespace gesecole;

use Illuminate\Database\Eloquent\Model;

class EmploiPermanent extends Model {
    protected $table='emploi_permanents';
    public $fillable=['id_personnel','id_plage_horaire','jour_semaine','date_debut','date_fin','annee_scolaire','statut','observations','id_user'];

    public function personnel(){ return $this->belongsTo(personnel::class,'id_personnel'); }
    public function plage(){ return $this->belongsTo(PlageHoraire::class,'id_plage_horaire'); }
}
