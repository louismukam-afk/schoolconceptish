<?php

function getRole($n){
    if($n==2)
        return trans('admin.editeur');
    if($n==16)
        return trans('admin.g_comptabilite');
    if($n==17)
        return trans('admin.g_personnel');
    if($n==18)
        return trans('admin.g_notes');
    if($n==18.5)
        return trans('admin.enseignant');
    if($n==19)
        return trans('admin.g_inscriptions');

    return trans('admin.aucun');
}


    function getGrade($note){
        if($note>=80){
            $grade[0]='A';
            $grade[1]=4;
            return $grade;
        }
        if($note>=75){
            $grade[0]='A-';
            $grade[1]=3.7;
            return $grade;
        }
        if($note>=70){
            $grade[0]='B+';
            $grade[1]=3.3;
            return $grade;
        }
        if($note>=65){
            $grade[0]='B';
            $grade[1]=3;
            return $grade;
        }
        if($note>=60){
            $grade[0]='B-';
            $grade[1]=2.7;
            return $grade;
        }
        if($note>=55){
            $grade[0]='C+';
            $grade[1]=2.3;
            return $grade;
        }
        if($note>=50){
            $grade[0]='C';
            $grade[1]=2;
            return $grade;
        }

        if($note>=45){
            $grade[0]='C-';
            $grade[1]=1.7;
            return $grade;
        }
        if($note>=40){
            $grade[0]='D+';
            $grade[1]=1.3;
            return $grade;
        }
        if($note>=35){
            $grade[0]='D';
            $grade[1]=1;
            return $grade;
        }
        if($note>=30){
            $grade[0]='E';
            $grade[1]=0;
            return $grade;
        }
            $grade[0]='F';
            $grade[1]=0;
            return $grade;

    }

    function getDecision($mgp){
        if($mgp>=2){
            return trans('admin.admis');

        }else
            return trans('admin.echec');
    }

function getSemestre($val){
    if ($val==1){
        return 1;
    }
    if ($val==2){
        return 2;
    }
    if ($val==0){
        return '1 & 2';
    }
}


    function getMention($note,$max){
        $palier=10;
        if($note<($max*$palier)/100){
            $mention= (session('locale')=='en')?  "Almost zero" :  "Presque nul";
            return $mention;
        }

        $palier=20;
        if($note<($max*$palier)/100){
            $mention= (session('locale')=='en')?  "Very weak" :  "Très faible";
            return $mention;
        }
        $palier=30;
        if($note<($max*$palier)/100)
        {
            $mention= (session('locale')=='en')?  "Weak" :  "Faible";
            return $mention;
        }
        $palier=40;
        if($note<($max*$palier)/100)
        {
            $mention= (session('locale')=='en')?  "Insufficient" :  "Insuffisant";
            return $mention;
        }
        $palier=50;
        if($note<($max*$palier)/100)
        {
            $mention= (session('locale')=='en')?  "Poor" :  "Médiocre";
            return $mention;
        }
        $palier=60;
        if($note<($max*$palier)/100)
        {
            $mention= (session('locale')=='en')?  "Fair" :  "Passable";
            return $mention;
        }
        $palier=70;
        if($note<($max*$palier)/100)
        {
            $mention= (session('locale')=='en')?  "Pretty good" :  "Assez bien";
            return $mention;
        }        $palier=80;
        if($note<($max*$palier)/100)
        {
            $mention= (session('locale')=='en')?  "Good" :  "Bien";
            return $mention;
        }
        $palier=90;
        if($note<($max*$palier)/100)
        {
            $mention= (session('locale')=='en')?  "Very good" :  "Très bien";
            return $mention;
        }
        $palier=100;
        if($note<($max*$palier)/100)
            return "Excellent";



        $mention= (session('locale')=='en')?  "Perfect" :  "Parfait";
        return $mention;

    }

function jour($n){
    if ($n==1)
        return trans('admin.jour1');
    if ($n==2)
        return trans('admin.jour2');
    if ($n==3)
        return trans('admin.jour3');
    if ($n==4)
        return trans('admin.jour4');
    if ($n==5)
        return trans('admin.jour5');
    if ($n==6)
        return trans('admin.jour6');
    if ($n==7)
        return trans('admin.jour7');
    return trans('admin.aucun');
}

function getIeme ($num) {
        switch ($num) {
            case 1 : $val = trans('admin.ordre1');
                break;
            case 2 : $val = trans('admin.ordre2');
                break;
            case 3 : $val = trans('admin.ordre3');
                break;
            default : $val = trans('admin.ordre4');
        }
    return $val;

}

function sanitize($str){
    $str=str_replace("'","\'",$str);
    $str=str_replace("\n","\\n",$str);
    $str=str_replace("\r","\\r",$str);
    return $str;
}

function purify($str){
    $str=str_replace("\n","",$str);
    $str=str_replace("\r","",$str);
    return $str;
}

?>


