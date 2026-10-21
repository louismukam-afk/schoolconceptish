<?php namespace gesecole;

use Illuminate\Database\Eloquent\Model;

/**
 * gesecole\payement
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $id_eleve
 * @property string $annee_scolaire
 * @property string $date_payement
 * @property float $montant
 * @property string $motif
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\payement whereAnneeScolaire($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\payement whereDatePayement($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\payement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\payement whereIdEleve($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\payement whereMontant($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\payement whereMotif($value)
 * @property string $numero
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\payement whereNumero($value)
 */
class payement extends Model {

    protected $table='payement';

    public $fillable=['id_eleve','annee_scolaire','date_payement','montant','motif','numero','lettre','mode_paye','ref_versement','type','id_inscription','desinscrire','id_campus','operation_source','id_caisse','id_banque','numero_versement','reference_operation','demande_operation','id_creator','id_last_editor','edition_details'];



    public function eleve() {
        return $this->belongsTo(eleve::class, 'id_eleve');
    }

    public function campus() {
        return $this->belongsTo(Campus::class, 'id_campus');
    }

    public function caisse() {
        return $this->belongsTo(Caisse::class, 'id_caisse');
    }

    public function banque() {
        return $this->belongsTo(Banque::class, 'id_banque');
    }

    public function creator() {
        return $this->belongsTo(User::class, 'id_creator');
    }

    public function lastEditor() {
        return $this->belongsTo(User::class, 'id_last_editor');
    }

}
