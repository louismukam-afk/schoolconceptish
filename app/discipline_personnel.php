<?php namespace gesecole;

use Illuminate\Database\Eloquent\Model;

/**
 * gesecole\discipline_personnel
 *
 * @property int $id
 * @property int $id_personnel
 * @property int $type
 * @property string $date_d
 * @property int $nombre
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\discipline_personnel whereDateD($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\discipline_personnel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\discipline_personnel whereIdPersonnel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\discipline_personnel whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\discipline_personnel whereType($value)
 * @mixin \Eloquent
 * @property string $annee_scolaire
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\discipline_personnel whereAnneeScolaire($value)
 * @property string|null $heure_arrive
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\discipline_personnel whereHeureArrive($value)
 */
class discipline_personnel extends Model {

    protected  $table='discipline_personnel';

    public $fillable=['id_personnnel','type','date_d','abscence_just','nombre','annee_scolaire','heure_arrive'];

    public $timestamps=false;

}
