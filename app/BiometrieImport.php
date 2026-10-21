<?php namespace gesecole;

use Illuminate\Database\Eloquent\Model;

class BiometrieImport extends Model {
    protected $table='biometrie_imports';

    public $fillable=['libelle','date_debut','date_fin','fichier','statut','type_import','total_lignes','total_consolidees','total_non_associees','observations','id_user'];

    public function pointages(){ return $this->hasMany(BiometriePointage::class,'id_biometrie_import'); }
    public function heures_realisees(){ return $this->hasMany(HeureRealiseeEnseignant::class,'id_biometrie_import'); }
}
