<?php

use Illuminate\Database\Seeder;
use gesecole\TutelleAcademique;

class TestokTutellesSeeder extends Seeder {
    public function run()
    {
        $definitions = array(
            'isabee' => array('nom'=>'ISABEE - Université d’Ebolowa','ville'=>'Ebolowa','faculte'=>'Institut supérieur d’agriculture, du bois, de l’eau et de l’environnement','tutelle_anglophone'=>'ISABEE - University of Ebolowa','logo'=>'images/tutelles/testok/logoUEb.png'),
            'esstic' => array('nom'=>'ESSTIC - Université de Yaoundé II','ville'=>'Yaoundé','faculte'=>'École supérieure des sciences et techniques de l’information et de la communication','tutelle_anglophone'=>'ESSTIC - University of Yaounde II','logo'=>'images/tutelles/testok/soa.jpeg','logo_faculte'=>'images/tutelles/testok/esstis.jpeg'),
            'ngaoundere' => array('nom'=>'Université de Ngaoundéré','ville'=>'Ngaoundéré','tutelle_anglophone'=>'The University of Ngaoundere','logo'=>'images/tutelles/testok/ngaroundere.jpg'),
            'soa' => array('nom'=>'Université de Yaoundé II - SOA','ville'=>'Soa','tutelle_anglophone'=>'The University of Yaounde II - SOA','logo'=>'images/tutelles/testok/soa.jpeg'),
            'polytechnique' => array('nom'=>'Polytechnique - Université de Yaoundé I','ville'=>'Yaoundé','faculte'=>'École nationale supérieure polytechnique','tutelle_anglophone'=>'Polytechnic - University of Yaounde I','logo'=>'images/tutelles/testok/univ_yaounde_1.png','logo_faculte'=>'images/tutelles/testok/polytechnique.png'),
        );
        DB::transaction(function() use($definitions) {
            foreach($definitions as $model=>$values) {
                // Une relance ne remplace jamais une personnalisation administrative.
                if(TutelleAcademique::where('modele_documents',$model)->exists())continue;
                $values['modele_documents']=$model;
                $values['republique_francophone']='RÉPUBLIQUE DU CAMEROUN';
                $values['republique_anglophone']='REPUBLIC OF CAMEROON';
                $values['patrie_francophone']='Paix – Travail – Patrie';
                $values['patrie_anglophone']='Peace – Work – Fatherland';
                $values['ministere_tutelle']='Ministère de l’Enseignement Supérieur';
                $values['ministere_tutelle_anglophone']='Ministry of Higher Education';
                TutelleAcademique::create($values);
            }
        });
    }
}
