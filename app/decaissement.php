<?php namespace gesecole;

use Illuminate\Database\Eloquent\Model;

/**
 * gesecole\decaissement
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $id_personnel
 * @property string|null $nom_beneficiaire
 * @property int|null $id_caisse
 * @property float $variation
 * @property string $motif
 * @property float $montant
 * @property float $total
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\decaissement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\decaissement whereIdPersonnel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\decaissement whereMontant($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\decaissement whereMotif($value)
 * @property string $date
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\decaissement whereDate($value)
 * @property string $annee_scolaire
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\decaissement whereAnneeScolaire($value)
 */
class decaissement extends Model {

	protected  $table='decaissement';

    public $fillable=['id_personnel','nom_beneficiaire','motif','montant','date','annee_scoaire','id_caisse','id_campus','id_creator','id_last_editor'];

    public function personnel() {
        return $this->belongsTo(personnel::class, 'id_personnel');
    }

    public function ligne_budgetaire() {
        return $this->belongsTo(LigneBudgetaire::class, 'id_ligne_budgetaire');
    }

    public function categorie_budgetaire() {
        return $this->belongsTo(CategorieBudgetaire::class, 'id_categorie_budgetaire');
    }

    public function creator() {
        return $this->belongsTo(User::class, 'id_creator');
    }

    public function lastEditor() {
        return $this->belongsTo(User::class, 'id_last_editor');
    }

    public function campus() {
        return $this->belongsTo(Campus::class, 'id_campus');
    }

    public function caisse() {
        return $this->belongsTo(Caisse::class, 'id_caisse');
    }



}
