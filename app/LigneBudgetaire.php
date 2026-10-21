<?php

namespace gesecole;

use Illuminate\Database\Eloquent\Model;

/**
 * gesecole\LigneBudgetaire
 *
 * @property int $id
 * @property string|null $libelle_ligne
 * @property string $date_debut
 * @property string $date_fin
 * @property float $total
 * @property int|null $id_campus
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\gesecole\decaissement[] $decaissement
 * @property-read \Illuminate\Database\Eloquent\Collection|\gesecole\DonneeLigneBudgetaire[] $donneesLigneBudgetaire
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\LigneBudgetaire whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\LigneBudgetaire whereDateDebut($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\LigneBudgetaire whereDateFin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\LigneBudgetaire whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\LigneBudgetaire whereLibelleLigne($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\LigneBudgetaire whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\LigneBudgetaire whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LigneBudgetaire extends Model
{
    protected $guarded = [];

    public function donneesLigneBudgetaire() {
        return $this->hasMany(DonneeLigneBudgetaire::class, 'id_ligne_budgetaire');
    }

    public function decaissement() {
        return $this->hasMany(decaissement::class, 'id_Ligne_Budgetaire');
    }

    public function campus() {
        return $this->belongsTo(Campus::class, 'id_campus');
    }
}
