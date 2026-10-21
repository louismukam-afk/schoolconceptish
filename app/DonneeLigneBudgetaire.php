<?php

namespace gesecole;

use Illuminate\Database\Eloquent\Model;

/**
 * gesecole\DonneeLigneBudgetaire
 *
 * @property int $id
 * @property int $id_ligne_budgetaire
 * @property int $id_categorie_budgetaire
 * @property float $montant
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \gesecole\CategorieBudgetaire $categorieBudgetaire
 * @property-read \gesecole\LigneBudgetaire $ligneBudgetaire
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\DonneeLigneBudgetaire whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\DonneeLigneBudgetaire whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\DonneeLigneBudgetaire whereIdCategorieBudgetaire($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\DonneeLigneBudgetaire whereIdLigneBudgetaire($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\DonneeLigneBudgetaire whereMontant($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\DonneeLigneBudgetaire whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DonneeLigneBudgetaire extends Model
{
    public function categorieBudgetaire () {
        return $this->belongsTo(CategorieBudgetaire::class, 'id_categorie_budgetaire');
    }
    public function ligneBudgetaire() {
        return $this->belongsTo(LigneBudgetaire::class, 'id_ligne_budgetaire');
    }
}
