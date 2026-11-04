<?php namespace gesecole\Services;

/** Lit des valeurs SQL phpMyAdmin. Aucune instruction du fichier n'est exécutée. */
class TestokSqlDumpReader {
    private $required = array('annee','cycle','niveaux','filiere','specialite','codematiere','matiere','programme_specialite','groupe_matiere','session_examen','etudiant','inscription','notes');
    private $optional = array('domaine','parent','ecole','tutelle');

    public function read($file)
    {
        $handle=fopen($file,'rb');
        if(!$handle)throw new \RuntimeException('Fichier SQL illisible.');
        $tables=array();$ignored=array();
        try {
            foreach($this->statements($handle) as $sql){
                if(preg_match('/^CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`([^`]+)`/i',$sql,$m)){
                    $table=strtolower($m[1]);
                    if($this->allowed($table) && !isset($tables[$table]))$tables[$table]=array();
                }
                if(!preg_match('/^(?:INSERT|REPLACE)\s+INTO\s+`([^`]+)`/i',$sql,$m))continue;
                $table=strtolower($m[1]);
                if(!$this->allowed($table)){$ignored[$table]=true;continue;}
                if(!preg_match('/^INSERT\s+INTO\s+`[^`]+`\s*\(([^)]+)\)\s+VALUES\s*/i',$sql,$header))throw new \RuntimeException('INSERT non pris en charge : '.$table);
                preg_match_all('/`([^`]+)`/',$header[1],$columns);
                $keys=array_map('strtoupper',$columns[1]);
                if(!count($keys))throw new \RuntimeException('Colonnes absentes : '.$table);
                if(!isset($tables[$table]))$tables[$table]=array();
                $position=strlen($header[0]);$length=strlen($sql);
                while($position<$length){
                    $this->space($sql,$position);
                    if($position===$length)break;
                    if($sql[$position]!=='(')throw new \RuntimeException('Liste de valeurs invalide : '.$table);
                    $position++;$row=array();
                    foreach($keys as $index=>$key){
                        if($index){$this->space($sql,$position);if(!isset($sql[$position]) || $sql[$position++]!==',')throw new \RuntimeException('Nombre de colonnes invalide : '.$table);}
                        $row[$key]=$this->value($sql,$position);
                    }
                    $this->space($sql,$position);
                    if(!isset($sql[$position]) || $sql[$position++]!==')')throw new \RuntimeException('Fin de ligne invalide : '.$table);
                    $tables[$table][]=$row;$this->space($sql,$position);
                    if($position<$length && $sql[$position++]!==',')throw new \RuntimeException('Expression SQL non prise en charge : '.$table);
                }
            }
        } catch(\Exception $e){fclose($handle);throw $e;}
        fclose($handle);
        foreach($this->required as $table)if(!isset($tables[$table]))throw new \RuntimeException('Table absente du SQL : '.$table);
        return array('format'=>'testok-schoolconcept-v1','exported_at'=>date('c'),'sql_sha256'=>hash_file('sha256',$file),'excluded_tables'=>array_keys($ignored),'tables'=>$tables);
    }

    private function allowed($table){return in_array($table,$this->required,true) || in_array($table,$this->optional,true);}
    private function space($sql,&$p){$n=strlen($sql);while($p<$n && ctype_space($sql[$p]))$p++;}
    private function value($sql,&$p)
    {
        $this->space($sql,$p);$n=strlen($sql);
        if($p<$n && ($sql[$p]==="'" || $sql[$p]==='"')){
            $quote=$sql[$p++];$value='';
            $escapes=array('0'=>"\0",'n'=>"\n",'r'=>"\r",'b'=>chr(8),'t'=>"\t",'Z'=>chr(26));
            while($p<$n){
                $c=$sql[$p++];
                if($c==='\\'){
                    if($p===$n)break;
                    $c=$sql[$p++];$value.=isset($escapes[$c])?$escapes[$c]:$c;
                }elseif($c===$quote){
                    if($p<$n && $sql[$p]===$quote){$value.=$quote;$p++;}else return $value;
                }else $value.=$c;
            }
            throw new \RuntimeException('Chaîne SQL non terminée.');
        }
        $start=$p;while($p<$n && $sql[$p]!==',' && $sql[$p]!==')')$p++;
        $token=trim(substr($sql,$start,$p-$start));
        if(strtoupper($token)==='NULL')return null;
        // Conserver les nombres sous forme de chaînes comme l'export mysqli.
        if(preg_match('/^[+-]?(?:\d+(?:\.\d*)?|\.\d+)(?:[eE][+-]?\d+)?$/',$token))return $token;
        throw new \RuntimeException('Valeur SQL non littérale refusée.');
    }

    private function statements($handle)
    {
        $buffer='';$quote=null;$block=false;
        while(($line=fgets($handle))!==false){
            $n=strlen($line);
            for($i=0;$i<$n;$i++){
                $c=$line[$i];$next=$i+1<$n?$line[$i+1]:'';
                if($block){if($c==='*' && $next==='/'){$block=false;$i++;$buffer.=' ';}continue;}
                if($quote!==null){
                    $buffer.=$c;
                    if($c==='\\' && $next!==''){$buffer.=$next;$i++;}
                    elseif($c===$quote){if($next===$quote){$buffer.=$next;$i++;}else $quote=null;}
                    continue;
                }
                if($c==='#' || ($c==='-' && $next==='-' && ($i+2===$n || ctype_space($line[$i+2])))){ $buffer.="\n";break; }
                if($c==='/' && $next==='*'){$block=true;$i++;continue;}
                if($c==="'" || $c==='"' || $c==='`')$quote=$c;
                if($c===';'){$statement=trim($buffer);$buffer='';if($statement!=='')yield $statement;}
                else $buffer.=$c;
            }
        }
        if($quote!==null || $block || trim($buffer)!=='')throw new \RuntimeException('Export SQL incomplet : fin de fichier inattendue.');
    }
}
