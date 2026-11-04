<?php namespace gesecole\Services;

class TestokTutellePolicy {
    // Identifiants des filières du SQL Test OK fourni ; modèles convenus pour la reprise.
    public static function models($filiere)
    {
        $map=array(1=>array('isabee'),2=>array('soa'),3=>array('soa'),4=>array('esstic'),
            5=>array('ngaoundere','polytechnique'),6=>array('ngaoundere','polytechnique'),
            7=>array('ngaoundere','polytechnique'),8=>array('ngaoundere','polytechnique'),
            9=>array('ngaoundere','polytechnique'),10=>array('ngaoundere','polytechnique'));
        if(!isset($map[$filiere]))throw new \InvalidArgumentException('Filière Test OK sans règle de tutelle : '.$filiere);
        return $map[$filiere];
    }
}
