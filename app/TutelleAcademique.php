<?php namespace gesecole;

use Illuminate\Database\Eloquent\Model;

class TutelleAcademique extends Model {
    protected $table = 'tutelles_academiques';

    protected $fillable = array(
        'nom', 'ville', 'localisation', 'telephone', 'email', 'description1',
        'description2', 'ministere_tutelle', 'faculte', 'logo', 'logo_faculte',
        'republique_francophone', 'republique_anglophone',
        'patrie_francophone', 'patrie_anglophone',
        'ministere_tutelle_anglophone', 'faculte_anglophone', 'tutelle_anglophone',
        'id_creator', 'id_last_editor'
    );

    public function specialites()
    {
        return $this->belongsToMany(classe::class, 'specialite_tutelle', 'id_tutelle', 'id_classe')->withTimestamps();
    }
}
