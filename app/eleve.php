<?php namespace gesecole;

use Illuminate\Database\Eloquent\Model;

/**
 * gesecole\eleve
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\gesecole\discipline[] $disciplines
 * @property-read \Illuminate\Database\Eloquent\Collection|\gesecole\inscription[] $inscriptions
 * @property-read \Illuminate\Database\Eloquent\Collection|\gesecole\note[] $notes
 * @property-read \Illuminate\Database\Eloquent\Collection|\gesecole\parents[] $parents
 * @property-read \Illuminate\Database\Eloquent\Collection|\gesecole\payement[] $payements
 * @mixin \Eloquent
 * @property int $id
 * @property string $nom
 * @property string|null $prenom
 * @property string $date_naiss
 * @property string $lieu_naiss
 * @property string $sexe
 * @property string $nationalite
 * @property string $quartier
 * @property int $transport
 * @property int $parent_ens
 * @property string|null $lien_tuteur
 * @property string $matricule
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\eleve whereDateNaiss($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\eleve whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\eleve whereLienTuteur($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\eleve whereLieuNaiss($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\eleve whereMatricule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\eleve whereNationalite($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\eleve whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\eleve whereParentEns($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\eleve wherePrenom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\eleve whereQuartier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\eleve whereSexe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\eleve whereTransport($value)
 * @property string|null $email
 * @property string|null $tel
 * @property string|null $status_matrimonial
 * @property string|null $region_origine
 * @property string|null $departement_origine
 * @property string|null $diplome_preinscription
 * @property string|null $annee_obtention
 * @property string|null $pays_obtention
 * @property string|null $dernier_etablissement
 * @property string|null $annee_sortie
 * @property string|null $diplome_obtenu
 * @property string|null $premiere_langue
 * @property string|null $deuxieme_langue
 * @property string|null $autre_langue
 * @property string|null $profession
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\eleve inscrits()
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\eleve whereAnneeObtention($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\eleve whereAnneeSortie($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\eleve whereAutreLangue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\eleve whereDepartementOrigine($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\eleve whereDernierEtablissement($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\eleve whereDeuxiemeLangue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\eleve whereDiplomeObtenu($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\eleve whereDiplomePreinscription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\eleve whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\eleve wherePaysObtention($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\eleve wherePremiereLangue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\eleve whereProfession($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\eleve whereRegionOrigine($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\eleve whereStatusMatrimonial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\eleve whereTel($value)
 */
class eleve extends Model {

    protected $table = 'eleve';

    public $fillable = ['nom', 'prenom', 'date_naiss','lieu_naiss','ne_vers','sexe','nationalite','quartier','parent_ens','lien_tuteur','matricule','email','tel','telephone_wathsapp_eleve','status_matrimonial','region_origine','departement_origine','diplome_preinscription','serie','moyenne_generale','annee_obtention','pays_obtention','dernier_etablissement','localisation_ecole','annee_sortie','diplome_obtenu','premiere_langue','deuxieme_langue','autre_langue','profession','source_financement','canal_decouvert','site_internet','abonnement','abonnement_valide','chaine','numero_conseille','telephone_wathsapp_pere','telephone_wathsapp_mere','telephone_wathsapp_tuteur','telephone_wathsapp_autre'];


    public function parents(){
        return $this->hasMany(parents::class,'id_eleve');
    }
    public function inscriptions(){
        return $this->hasMany(inscription::class,'id_eleve');
    }
    public function payements(){
        return $this->hasMany(payement::class,'id_eleve');
    }
    public function notes(){
        return $this->hasMany(note::class,'id_eleve');
    }
    public function disciplines(){
        return $this->hasMany(discipline::class,'id_eleve');
    }
    public function Etudiant_users()
    {
        return $this->belongsTo(Etudiant_user::class,'id_eleve');
    }
    
     /* public function scopeInscrits(Builder $query) {
        $currentYear = Controller::getSchoolYear();
        return $query->whereHas('inscriptions', function (Builder $query) use ($currentYear) {
            $query->where('annee_scolaire', '=', $currentYear);
        });
    }
*/
    public function getFullName() {
        return (strlen($this->prenom) > 0) ? $this->nom . ' ' . $this->prenom : $this->nom;
    }
}

