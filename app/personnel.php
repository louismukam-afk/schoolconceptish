<?php namespace gesecole;

use Illuminate\Database\Eloquent\Model;

/**
 * gesecole\personnel
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\gesecole\discipline[] $disciplines
 * @property-read \Illuminate\Database\Eloquent\Collection|\gesecole\dispense[] $dispenses
 * @property-read \Illuminate\Database\Eloquent\Collection|\gesecole\fonction_personnel[] $fonction_personnels
 * @property-read \gesecole\salaire $salaire
 * @mixin \Eloquent
 * @property int $id
 * @property string $nom
 * @property string $date_naiss
 * @property string $lieu_naiss
 * @property string $diplome
 * @property string $sexe
 * @property string $date_entree
 * @property string $tel1
 * @property string $tel2
 * @property string $adresse
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\personnel whereAdresse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\personnel whereDateEntree($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\personnel whereDateNaiss($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\personnel whereDiplome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\personnel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\personnel whereLieuNaiss($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\personnel whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\personnel whereSexe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\personnel whereTel1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\personnel whereTel2($value)
 * @property string|null $email
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\personnel whereEmail($value)
 * @property string|null $matricule
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\personnel whereMatricule($value)
 * @property int|null $type
 * @property string|null $matieres
 * @property string|null $autre_information
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\personnel whereAutreInformation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\personnel whereMatieres($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\personnel whereType($value)
 * @property string $nationalite
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\personnel whereNationalite($value)
 */
class personnel extends Model {

	protected $table='personnel';

    public $fillable=['matricule','nom','date_naiss','lieu_naiss','nationalite','diplome','sexe','date_entree','tel1','tel2','adresse','email','type','type_personnel','mode_horaire','categorie_horaire','horaire_travail','actif','matieres','autre_information','id_campus'];

    public $timestamps=false;


    public function dispenses(){
        return $this->hasMany(dispense::class,'id_personnel');

    }

    public function salaire(){
        return $this->hasOne(salaire::class,'id_personnel');

    }
    public function disciplines(){
        return $this->hasMany(discipline::class,'id_eleve');

    }
    public function fonction_personnels(){
        return $this->hasMany(fonction_personnel::class,'id_personnel');

    }

    public function campus(){
        return $this->belongsTo(Campus::class,'id_campus');
    }

}
