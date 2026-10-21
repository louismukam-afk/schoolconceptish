<?php namespace gesecole;

use Illuminate\Database\Eloquent\Model;

/**
 * gesecole\verrouNote
 *
 * @property int $id
 * @property int $sequence
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\verrouNote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\verrouNote whereSequence($value)
 * @mixin \Eloquent
 * @property string $annee_scolaire
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\verrouNote whereAnneeScolaire($value)
 */
class verrouNote extends Model {

    protected  $table='verrou_note';

    public $fillable=['sequence','annee_scolaire','id_campus','target','is_active','starts_at','ends_at','objectif','message','id_creator','id_last_editor'];


}
