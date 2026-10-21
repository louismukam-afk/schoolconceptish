<?php namespace gesecole;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

/**
 * gesecole\niveau
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\gesecole\classe[] $classes
 * @mixin \Eloquent
 * @property int $id
 * @property string $nom
 * @property int $id_cycle
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\niveau whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\niveau whereNom($value)
 * @property-read \gesecole\scolarite $scolarite
 * @property string $nom_anglais
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\niveau whereNomAnglais($value)
 */
class niveau extends Model {

	protected $table='niveau';

    public $fillable=['nom', 'domaines'];



    public function getNom()
    {
        if(App::getLocale() == 'en') {
            if($this->nom_anglais){
                return $this->nom_anglais;
            }
        }
        return $this->nom;
    }

    public function classes(){
        return $this->hasMany(classe::class,'id_niveau');
    }

    public function scolarite(){
        return $this->hasOne(scolarite::class,'id_niveau');

    }


}
