<?php
namespace App\Repositories;

use App\Http\Controllers\v3\FlottaController;
use Carbon\Carbon;
use Illuminate\Http\Request;

class Flotta
{
    CONST CACHE_KEY="FLOTTA";
    CONST CACHE_TIME=2;

    function getUnitcodes($idFlotta)
    {
        return cache()->remember($this->getCacheKey("unitcodes.".$idFlotta), Carbon::now()->addMinutes(self::CACHE_TIME), function() use($idFlotta){
            $idPerifericaList = [];
            $flotta = $this->getId($idFlotta);
            
            if (!isset($flotta->servizio)) {
                return $flotta;
            }
            foreach ($flotta->servizio as $singolo) {
                if (!empty($singolo->unitcode)) {
                    $idPerifericaList[] = $singolo->unitcode;
                }
            }
            return $idPerifericaList;
        });
    }

    function getId($idFlotta)
    {
        return cache()->remember($this->getCacheKey("all.".$idFlotta), Carbon::now()->addMinutes(self::CACHE_TIME), function() use($idFlotta){
            $tmp = (object)(new FlottaController)->getId((new Request([])), $idFlotta)[0];
            return $tmp;
        });
    }

    function getCacheKey($key)
    {
        $key = strtoupper($key);
        
        return self::CACHE_KEY .".$key";
    }
}
