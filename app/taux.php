<?php namespace gesecole;

use Illuminate\Database\Eloquent\Model;

/**
 * gesecole\taux
 *
 * @property int $id
 * @property int $id_personnel
 * @property int $id_matiere
 * @property float $taux
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\taux whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\taux whereIdMatiere($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\taux whereIdPersonnel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\taux whereTaux($value)
 * @mixin \Eloquent
 */
class taux extends Model {

    protected  $table='taux';

    public $fillable=['id_personnel','id_matiere','taux'];

    public $timestamps=false;

}
