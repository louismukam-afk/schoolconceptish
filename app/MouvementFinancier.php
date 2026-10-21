<?php namespace gesecole;

use Illuminate\Database\Eloquent\Model;

class MouvementFinancier extends Model {
    protected $table = 'mouvements_financiers';

    protected $fillable = array(
        'type_operation', 'source', 'id_caisse', 'id_banque', 'id_caisse_source',
        'id_caisse_destination', 'id_banque_source', 'id_banque_destination',
        'montant', 'date_operation', 'motif', 'numero_versement',
        'reference', 'demande', 'id_payement', 'id_inscription', 'id_campus', 'id_user',
        'id_creator', 'id_last_editor'
    );

    public function caisse()
    {
        return $this->belongsTo(Caisse::class, 'id_caisse');
    }

    public function banque()
    {
        return $this->belongsTo(Banque::class, 'id_banque');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'id_creator');
    }

    public function lastEditor()
    {
        return $this->belongsTo(User::class, 'id_last_editor');
    }
}
