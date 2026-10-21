<?php namespace gesecole;

use Illuminate\Database\Eloquent\Model;

class ReleveNoteGeneration extends Model {

    protected $table = 'releve_note_generations';

    protected $fillable = [
        'type',
        'annee_academique',
        'id_niveau',
        'id_classe',
        'id_cycle',
        'id_nniveau',
        'periode_debut',
        'periode_fin',
        'semestre',
        'nb_releves',
        'hash_generation',
        'contenu',
        'id_creator',
    ];

    public function classe()
    {
        return $this->belongsTo(classe::class, 'id_classe');
    }

    public function niveau()
    {
        return $this->belongsTo(niveau::class, 'id_niveau');
    }

    public function cycle()
    {
        return $this->belongsTo(cycle::class, 'id_cycle');
    }

    public function nniveau()
    {
        return $this->belongsTo(NNiveau::class, 'id_nniveau');
    }
}
