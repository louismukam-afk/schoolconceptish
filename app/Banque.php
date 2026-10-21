<?php namespace gesecole;

use Illuminate\Database\Eloquent\Model;

class Banque extends Model {
    protected $table = 'banques';

    protected $fillable = array('nom', 'localisation', 'numero_compte', 'ville', 'solde', 'description', 'id_creator', 'id_last_editor');

    public function mouvements()
    {
        return $this->hasMany(MouvementFinancier::class, 'id_banque');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'banque_user', 'id_banque', 'id_user')->withTimestamps();
    }
}
