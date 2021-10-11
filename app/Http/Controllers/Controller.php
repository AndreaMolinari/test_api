<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Routing\Controller as BaseController;

use Illuminate\Support\Facades\Auth; // ! messa da me

class Controller extends BaseController
{
    /**
         * @OA\Info(
         *    title="Api Record",
         *    version="1.0.0",
         * )
     */
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    // public $utenteGlobal = [];

    // public function __construct()
    // {
    //     $this->$utenteGlobal = (object) [];
    // }

    public function cleanByGeneric($list, $params = "id")
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

    public function cleanByGenericGroup($list, $params)
    {
        $results = [];
        if( (is_object($list) || is_array($list)) and count($list) >= 1 )
        {
            foreach($list as $singolo){
                // exit(json_encode($si ngolo));
                if( property_exists($singolo, $params) ){
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

    public function gestiscoIdOperatore($opName = null)
    {
        // if(is_null($idOp)){
        // }else{
        //     return Auth::user()->dataUtente();
        // }

        $ut = (object) [];

        if($opName != null){
            $id = $opName;
        }else{
            $id = 1;
        }

        $ut->id = $id;

        return $ut;
    }

    public function cleanRequest(Request $request)
    {
        $newRequ = [];

        foreach($request->all() as $key => $val)
        {
            if( !is_null($val) )
            {
                $newRequ[$key] = $val;
            }
        }

        $requestF = new Request();
        $requestF->headers->set('content-type', 'application/json');
        $requestF->initialize($newRequ);

        return $requestF;
    }

    public function trasformInArray($data, $params = 'id')
    {
        $return = [];

        foreach($data as $s){
            try{
                $return[] = $s->$params;
            }catch(\Exception $e){
                $return[] = $s[$params];
            }
        }

        return $return;
    }

    public function unsetParameter(array $oggetto, array $parametroList = [])
    {
        $return = [];

        foreach ($oggetto as $key => $value) {
            if(!in_array($key, $parametroList)){
                $return[$key] = $value;
            }
        }

        return $return;
    }



    // ? DOPO MIRKO

    public function mkRequest(Array $richiesta)
    {
        if( !array_key_exists('idOperatore', $richiesta)  )
        {
            $richiesta['idOperatore'] = Auth::user()->id;
        }

        $req = new Request();
        $req->headers->set('content-type', 'application/json');
        $req->initialize((array) $richiesta);


        // $req->request->add(['idOperatore' => $operatore]);

        return $req;
    }
}

