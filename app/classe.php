<?php namespace gesecole;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;


/**
 * gesecole\classe
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\gesecole\classeMatiere[] $classe_matieres
 * @property-read \Illuminate\Database\Eloquent\Collection|\gesecole\dispense[] $dispenses
 * @property-read \Illuminate\Database\Eloquent\Collection|\gesecole\titulaire[] $titulaires
 * @mixin \Eloquent
 * @property int $id
 * @property string $nom
 * @property int $id_niveau
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\classe whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\classe whereIdNiveau($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\classe whereNom($value)
 * @property int $capacite
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\classe whereCapacite($value)
 * @property-read \gesecole\niveau $niveau
 * @property-read \Illuminate\Database\Eloquent\Collection|\gesecole\inscription[] $inscriptions
 * @property string $code
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\classe whereCode($value)
 * @property int $id_cycle
 * @property string $nom_anglais
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\classe whereNomAnglais($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\classe whereIdCycle($value)
 */
class classe extends Model {
     protected  $table='classe';

    public $fillable=['nom','nom_anglais','code','id_niveau','capacite'];

    




    public function getNom()
    {
        if(App::getLocale() == 'en') {
            if($this->nom_anglais){
                return $this->nom_anglais;
            }
        }
        return $this->nom;
    }


    public function titulaires(){
        return $this->hasMany(titulaire::class ,'id_classe');
}

    public function classe_matieres(){
        return $this->hasMany(classeMatiere::class,'id_classe');

    }
    public function dispenses(){
        return $this->hasMany(dispense::class,'id_classe');

    }

    public  function niveau(){
        return $this->belongsTo(niveau::class,'id_niveau');
    }

    public function inscriptions(){
        return $this->hasMany(inscription::class,'id_classe');
    }

    public function campus(){
        return $this->belongsToMany(Campus::class, 'campus_specialite', 'id_classe', 'id_campus')->withTimestamps();
    }

    public function tutelles(){
        return $this->belongsToMany(TutelleAcademique::class, 'specialite_tutelle', 'id_classe', 'id_tutelle')->withTimestamps();
    }




}
