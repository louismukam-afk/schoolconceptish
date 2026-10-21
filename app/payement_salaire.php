<?php namespace gesecole;

use Illuminate\Database\Eloquent\Model;

/**
 * gesecole\payement_salaire
 *
 * @property int $id
 * @property int $id_eleve
 * @property string $annee_scolaire
 * @property string $date_payement
 * @property float $montant
 * @property string $motif
 * @property string $numero
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\payement_salaire whereAnneeScolaire($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\payement_salaire whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\payement_salaire whereMontant($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\payement_salaire whereNumero($value)
 * @mixin \Eloquent
 * @property int $id_personnel
 * @property int $id_user
 * @property string $date_p
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\payement_salaire whereDateP($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\payement_salaire whereIdPersonnel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\payement_salaire whereIdUser($value)
 * @property int $heure_realise
 * @property float $primes
 * @property float $acomptes
 * @property float $cnss
 * @property float $cas_sociales
 * @property float $assurances
 * @property float $autre_retenues
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\payement_salaire whereAcomptes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\payement_salaire whereAssurances($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\payement_salaire whereAutreRetenues($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\payement_salaire whereCasSociales($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\payement_salaire whereCnss($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\payement_salaire whereHeureRealise($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\payement_salaire wherePrimes($value)
 * @property float $taux
 * @property float $total
 * @property float $total_retenu
 * @property float $net_a_payer
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\payement_salaire whereNetAPayer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\payement_salaire whereTaux($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\payement_salaire whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\payement_salaire whereTotalRetenu($value)
 * @property int $mois
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\payement_salaire whereMois($value)
 */
class payement_salaire extends Model {

    protected $table='payement_salaire';

    public $fillable=['id_personnel','annee_scolaire','date_p','montant','id_user','numero','mois','heure_realise','primes','acomptes','cnss','cas_sociales','assurances','autre_retenues','taux','total','total_retenu','net_a_payer','id_campus','id_creator','id_last_editor'];

    public function agent() {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function creator() {
        return $this->belongsTo(User::class, 'id_creator');
    }

    public function campus() {
        return $this->belongsTo(Campus::class, 'id_campus');
    }


}
