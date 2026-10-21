<?php

namespace gesecole;

use Illuminate\Database\Eloquent\Model;

/**
 * gesecole\CategorieBudgetaire
 *
 * @property int $id
 * @property string|null $libelle
 * @property string $numero_compte
 * @property string $description
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\gesecole\decaissement[] $decaissement
 * @property-read \Illuminate\Database\Eloquent\Collection|\gesecole\DonneeLigneBudgetaire[] $donneesLigneBudgetaire
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\CategorieBudgetaire whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\CategorieBudgetaire whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\CategorieBudgetaire whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\CategorieBudgetaire whereLibelle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\CategorieBudgetaire whereNumeroCompte($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\CategorieBudgetaire whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CategorieBudgetaire extends Model
{
    protected $guarded = [];

    public function donneesLigneBudgetaire() {
        return $this->hasMany(DonneeLigneBudgetaire::class, 'id_categorie_budgetaire');
    }

    public function decaissement() {
        return $this->hasMany(decaissement::class, 'id_categorie_budgetaire');
    }
}
