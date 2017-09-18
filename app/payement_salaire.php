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
 */
class payement_salaire extends Model {

    protected $table='payement_salaire';

    public $fillable=['id_personnel','annee_scolaire','date_p','montant','id_user','numero'];


    public $timestamps=false;

}
