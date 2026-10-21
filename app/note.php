<?php namespace gesecole;

use Illuminate\Database\Eloquent\Model;

/**
 * gesecole\note
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $id_matiere
 * @property int $id_eleve
 * @property float $note
 * @property int $sequence
 * @property string $annee_scolaire
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\note whereAnneeScolaire($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\note whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\note whereIdEleve($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\note whereIdMatiere($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\note whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\gesecole\note whereSequence($value)
 */
class note extends Model {

	protected  $table='note';

    public $fillable=['id_matiere','id_eleve','note','sequence','annee_scolaire','id_campus','id_classe',
        'id_classe_matiere',
        'id_session',
        'id_creator',
        'id_last_editor',
        'edition_detail',
        'edition_details'

        ];

    public function session_exam()
    {
        return $this->belongsTo(Session_exam::class, 'id_session');
    }

    public function classe()
    {
        return $this->belongsTo(Classe::class, 'id_classe');
    }

    public function classeMatiere()
    {
        return $this->belongsTo(ClasseMatiere::class, 'id_classe_matiere');
    }


}
