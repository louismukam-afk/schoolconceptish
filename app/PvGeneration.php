<?php 
namespace gesecole;



use Illuminate\Database\Eloquent\Model;

class PvGeneration extends Model
{
    protected $table = 'pv_generations';

    protected $fillable = [
        'id_classe',
        'id_cycle',
        'id_nniveau',
        'annee_academique',
        'semestre',
        'type_pv',
        'date_debut',
        'date_fin',
        'date_generation',
        'nombre_etudiants',
        'nom_fichier',
        'id_user',
        'statut'
    ];

    protected $dates = [
        'date_debut',
        'date_fin',
        'date_generation'
    ];
}