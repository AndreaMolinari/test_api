<?php
namespace App\Repositories;

use Illuminate\Http\Request;

class iHelpU{

    public function groupBy($list, string $params = 'id')
    {
        $results = []; 
        if( (is_object($list) || is_array($list)) and count($list) >= 1 )
        {
            foreach($list as $singolo){
                $singolo = (object) $singolo;
                // exit(json_encode($si ngolo));
                if( isset($singolo->{$params}) ){
                    $ref = $singolo->{$params};
                    if( !empty($ref) )
                    {
                        if( array_key_exists($ref, $results) ){
                            $results[$ref][] = $singolo; 
                        }else{
                            $results[$ref] = [$singolo];
                        }
                    }
                }
            }
        }
        return $results;
    }

    public function listBy($list, string $params = 'id')
    {
        $results = (object) []; 

        foreach($list as $singolo){
            if(gettype($singolo) == "object"){
                $tmp = $singolo->$params;
            }else{
                $tmp = $singolo[$params];
            }

            $results->$tmp = $singolo;
        }

        return $results;
    }

    public function mkRequest(Array $richiesta, int $idOperatore)
    {
        if( !array_key_exists('idOperatore', $richiesta)  )
        {
            $richiesta['idOperatore'] = $idOperatore;
        }

        $req = new Request();
        $req->headers->set('content-type', 'application/json');
        $req->initialize((array) $richiesta);

        return $req;
    }
}