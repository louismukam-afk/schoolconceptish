<?php namespace gesecole\Http\Controllers\eleves;

class bamendareleveController extends esticreleveController
{
    protected function releveType()
    {
        return 'licence_bamenda';
    }

    protected function englishPriority()
    {
        return true;
    }

    protected function releveBasePath()
    {
        return '/eleves/releves_bamenda';
    }

    protected function classeRelevesView()
    {
        return 'eleves/classe_releves_bamenda';
    }

    protected function historiqueView()
    {
        return 'eleves/releves_bamenda_historique';
    }

    protected function historiqueTitle()
    {
        return 'Bamenda transcripts history';
    }

    protected function tutelleKeywords()
    {
        return array('BAMENDA', 'UNIVERSITY OF BAMENDA');
    }
}
