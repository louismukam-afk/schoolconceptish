<?php namespace gesecole;

use Illuminate\Database\Eloquent\Model;

class Campus extends Model {
    protected $table = 'campus';

    protected $fillable = array(
        'nom', 'sigle', 'localisation', 'ville', 'numero_agrement', 'reference',
        'date_creation', 'telephone', 'email', 'logo', 'site_web',
        'autorisation_ouverture', 'arrete_creation', 'boite_postale',
        'id_creator', 'id_last_editor'
    );

    public static function attributesFromParametres($params)
    {
        return array(
            'nom' => isset($params['nom_etablissement']) && $params['nom_etablissement'] ? $params['nom_etablissement'] : 'Campus principal',
            'sigle' => isset($params['initiales_etablissement']) ? $params['initiales_etablissement'] : null,
            'localisation' => isset($params['adresse']) ? $params['adresse'] : null,
            'telephone' => isset($params['telephone']) ? $params['telephone'] : null,
            'email' => isset($params['email']) ? $params['email'] : null,
            'site_web' => isset($params['web']) ? $params['web'] : null
        );
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'campus_user', 'id_campus', 'id_user')->withTimestamps();
    }

    public function specialites()
    {
        return $this->belongsToMany(classe::class, 'campus_specialite', 'id_campus', 'id_classe')->withTimestamps();
    }
}
