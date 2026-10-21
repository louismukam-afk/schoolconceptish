<?php namespace gesecole;

use Illuminate\Database\Eloquent\Model;

class Caisse extends Model {
    protected $table = 'caisses';

    protected $fillable = array('nom', 'type_caisse', 'description', 'solde', 'id_creator', 'id_last_editor');

    public function mouvements()
    {
        return $this->hasMany(MouvementFinancier::class, 'id_caisse');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'caisse_user', 'id_caisse', 'id_user')->withTimestamps();
    }
}
