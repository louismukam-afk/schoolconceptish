<?php namespace gesecole;

use Illuminate\Database\Eloquent\Model;

class PlageHoraire extends Model {
    protected $table='plage_horaires';
    public $fillable=['libelle','heure_debut','heure_fin','duree_payable','type_plage','type_personnel','periode_journee','format_plage','ordre','statut','id_user'];
}
