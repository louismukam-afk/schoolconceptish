<?php namespace gesecole;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * gesecole\scolarite
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $id_classe
 * @property float|null $tranche1
 * @property float|null $tranche2
 * @property float|null $tranche3
 * @property float|null $tranche4
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\scolarite whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\scolarite whereIdClasse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\scolarite whereTranche1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\scolarite whereTranche2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\scolarite whereTranche3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\scolarite whereTranche4($value)
 * @property int $id_niveau
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\scolarite whereIdNiveau($value)
 * @property float $total
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\scolarite whereTotal($value)
 * @property int $tranche5
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\scolarite whereTranche5($value)
 * @property int $id_cycle
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\scolarite whereIdCycle($value)
 */
class scolarite extends Model {

	protected  $table='scolarite';

    public $fillable=['id_niveau','id_cycle','id_classe','id_nniveau','annee_scolaire','libelle','inscription','tranche1','tranche2','tranche3','tranche4','tranche5','type_scolarite'];

    public static function forInscription($inscription, $classe = null)
    {
        if (!$inscription) {
            return null;
        }

        if (Schema::hasColumn('inscription', 'id_scolarite') && isset($inscription->id_scolarite) && $inscription->id_scolarite) {
            $scolarite = static::find($inscription->id_scolarite);
            if ($scolarite) {
                return $scolarite;
            }
        }

        if (!$classe) {
            $classe = classe::find($inscription->id_classe);
        }
        if (!$classe) {
            return null;
        }

        $idNiveau = isset($classe->id_niveau) ? $classe->id_niveau : null;
        if (!$idNiveau) {
            return null;
        }

        return static::matching(
            $inscription->id_cycle,
            $idNiveau,
            $inscription->type_scolarite,
            $inscription->id_classe,
            $inscription->id_nniveau,
            $inscription->annee_scolaire
        )->first();
    }

    public static function matching($idCycle, $idNiveau, $typeScolarite, $idClasse = null, $idNniveau = null, $anneeScolaire = null)
    {
        $query = static::where('id_cycle', '=', $idCycle)
            ->where('id_niveau', '=', $idNiveau)
            ->where('type_scolarite', '=', $typeScolarite);

        return static::applyProfileFilters($query, $idClasse, $idNniveau, $anneeScolaire);
    }

    public static function matchingProfiles($idCycle, $idNiveau, $idClasse = null, $idNniveau = null, $anneeScolaire = null, $preferredTypeScolarite = null)
    {
        $query = static::where('id_cycle', '=', $idCycle)
            ->where('id_niveau', '=', $idNiveau);

        if ($preferredTypeScolarite !== null) {
            $query->orderByRaw('CASE WHEN type_scolarite = ? THEN 0 ELSE 1 END', array($preferredTypeScolarite));
        }

        return static::applyProfileFilters($query, $idClasse, $idNniveau, $anneeScolaire);
    }

    public static function matchingOldStyle($idCycle, $idNiveau, $typeScolarite)
    {
        $query = static::where('id_cycle', '=', $idCycle)
            ->where('id_niveau', '=', $idNiveau)
            ->where('type_scolarite', '=', $typeScolarite);

        if (Schema::hasColumn('scolarite', 'annee_scolaire')) {
            $query->where(function ($q) {
                $q->whereNull('annee_scolaire')
                    ->orWhere('annee_scolaire', '=', '');
            });
        }

        if (Schema::hasColumn('scolarite', 'id_classe')) {
            $query->where(function ($q) {
                $q->whereNull('id_classe')
                    ->orWhere('id_classe', '=', 0);
            });
        }

        if (Schema::hasColumn('scolarite', 'id_nniveau')) {
            $query->where(function ($q) {
                $q->whereNull('id_nniveau')
                    ->orWhere('id_nniveau', '=', 0);
            });
        }

        return $query->orderBy('id', 'desc');
    }

    private static function applyProfileFilters($query, $idClasse = null, $idNniveau = null, $anneeScolaire = null)
    {
        if (Schema::hasColumn('scolarite', 'annee_scolaire') && $anneeScolaire !== null) {
            $annees = static::schoolYearVariants($anneeScolaire);
            $query->where(function ($q) use ($annees) {
                $q->whereIn('annee_scolaire', $annees)
                    ->orWhereNull('annee_scolaire')
                    ->orWhere('annee_scolaire', '=', '');
            });
        }

        if (Schema::hasColumn('scolarite', 'id_classe') && $idClasse !== null) {
            $query->where(function ($q) use ($idClasse) {
                $q->where('id_classe', '=', $idClasse)
                    ->orWhereNull('id_classe')
                    ->orWhere('id_classe', '=', 0);
            });
            $query->orderByRaw('CASE WHEN id_classe = ? THEN 0 ELSE 1 END', array($idClasse));
        }

        if (Schema::hasColumn('scolarite', 'id_nniveau') && $idNniveau !== null) {
            $query->where(function ($q) use ($idNniveau) {
                $q->where('id_nniveau', '=', $idNniveau)
                    ->orWhereNull('id_nniveau')
                    ->orWhere('id_nniveau', '=', 0);
            });
            $query->orderByRaw('CASE WHEN id_nniveau = ? THEN 0 ELSE 1 END', array($idNniveau));
        }

        if (Schema::hasColumn('scolarite', 'annee_scolaire') && $anneeScolaire !== null) {
            $query->orderByRaw('CASE WHEN annee_scolaire = ? THEN 0 ELSE 1 END', array($anneeScolaire));
        }

        return $query->orderBy('id', 'desc');
    }

    private static function schoolYearVariants($anneeScolaire)
    {
        $annee = trim((string) $anneeScolaire);
        $anneeSansEspaces = str_replace(' ', '', $annee);
        $anneeAvecEspaces = str_replace('-', ' - ', $anneeSansEspaces);

        return array_values(array_unique(array(
            $annee,
            $anneeSansEspaces,
            $anneeAvecEspaces
        )));
    }

}
