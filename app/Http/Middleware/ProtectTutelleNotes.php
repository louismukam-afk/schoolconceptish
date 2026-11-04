<?php namespace gesecole\Http\Middleware;

use Closure;
use gesecole\Services\AcademicDocumentService;

class ProtectTutelleNotes {
    public function handle($request,Closure $next)
    {
        $action=$request->route()->getActionName();
        if($action==='Closure'){$definition=$request->route()->getAction();$action=isset($definition['uses']) && is_string($definition['uses'])?$definition['uses']:'';}
        if(!preg_match('/noteController@(create|create_deliberer|store|deliberer|deliberer_note|deliberer_rattrapage|deliberationsEtudiants|applyDeliberationsEtudiants)$/i',$action))return $next($request);
        $classId=$request->input('id_classe');
        if(!$classId)$classId=$request->route('id');
        $cycleId=$request->input('cycle',$request->input('id_cycle'));
        $levelId=$request->input('nniveau',$request->input('id_nniveau'));
        $year=$request->input('annee_scolaire',session('year'));
        if(AcademicDocumentService::isManagedContext($classId,$cycleId,$levelId,$year)){
            return redirect()->to('/eleves/documents-tutelles?'.http_build_query(array('id_classe'=>$classId,'id_cycle'=>$cycleId,'id_nniveau'=>$levelId,'choisir_tutelle'=>1,'choisir_deliberation'=>stripos($action,'deliberationsEtudiants')!==false?1:0)));
        }
        return $next($request);
    }
}
