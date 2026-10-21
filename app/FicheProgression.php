<?php namespace gesecole;

use Illuminate\Database\Eloquent\Model;

class FicheProgression extends Model {

    protected $table = 'fiches_progression';

    public $fillable = array(
        'id_personnel',
        'id_cours_enseignant',
        'annee_scolaire',
        'id_campus',
        'titre',
        'description',
        'fichier_original',
        'fichier_path',
        'fichier_extension',
        'fichier_mime',
        'id_user'
    );

    public function personnel()
    {
        return $this->belongsTo(personnel::class, 'id_personnel');
    }

    public function cours()
    {
        return $this->belongsTo(CoursEnseignant::class, 'id_cours_enseignant');
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class, 'id_campus');
    }
}
