<?php
namespace App\Repositories;

use Facades\App\Http\Controllers\Trax\RssController;
use Carbon\Carbon;

class RSS
{
    CONST CACHE_KEY  = "RSS";
    CONST CACHE_TIME = 20;

    function setAll()
    {
        $lista = RssController::getAll();
        cache()->forget($this->getCacheKey("ALL"));
        return cache()->remember($this->getCacheKey("ALL"), Carbon::now()->addMinutes(self::CACHE_TIME), function() use ($lista){
            return $lista;
        });
    }

    function getAll()
    {
        return cache()->remember($this->getCacheKey("ALL"), Carbon::now()->addMinutes(self::CACHE_TIME), function(){
            return $this->setAll();
        });
    }

    function getCacheKey($key)
    {
        $key = strtoupper($key);
        
        return self::CACHE_KEY .".$key";
    }
}
