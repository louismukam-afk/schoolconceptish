<?php namespace gesecole;

use Illuminate\Database\Eloquent\Model;

/**
 * gesecole\presence_e
 *
 * @property int $id
 * @property int $id_personnel
 * @property int $id_dispense
 * @property string $date_p
 * @property string $annee_scolaire
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\presence_e whereAnneeScolaire($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\presence_e whereDateP($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\presence_e whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\presence_e whereIdDispense($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\presence_e whereIdPersonnel($value)
 * @mixin \Eloquent
 * @property int $jour
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\presence_e whereJour($value)
 */
class presence_e extends Model {

    protected $table='presence_enseignant';

    public $fillable=['id_personnel','jour','date_p','annee_scolaire'];


    public $timestamps=false;

}
