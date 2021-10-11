<?php
namespace App\Repositories;

use App\Http\Controllers\AutistaController;
use Carbon\Carbon;


class Autista
{
    CONST CACHE_KEY="AUTISTA";
    CONST CACHE_TIME=10;

    function getAll()
    {
        return cache()->remember($this->getCacheKey("ALL"), Carbon::now()->addMinutes(self::CACHE_TIME), function(){
            $listone = [];
            if ( is_file( base_path('autisti.json') ) ) {
                $autisti = file_get_contents( base_path('autisti.json') );
                $autisti = json_decode($autisti);

                foreach ($autisti as $autista) {
                    $t_nome = (!empty($autista->nome) && trim($autista->nome) != '-') ? trim($autista->nome) : '';
                    $t_cognome = (!empty($autista->cognome) && trim($autista->cognome) != '-') ? trim($autista->cognome) : '';
                    $listone[$autista->telecomando] = (object) [
                        'id' => null,
                        'nome' => trim($t_nome . " " . $t_cognome)
                    ];
                }
            } else {
                $listone = (new AutistaController())->getTagIDAnag();
            }
            return $listone;
        });
    }

    function getCacheKey($key)
    {
        $key = strtoupper($key);
        
        return self::CACHE_KEY .".$key";
    }
}
