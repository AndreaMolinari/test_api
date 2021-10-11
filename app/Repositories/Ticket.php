<?php
namespace App\Repositories;

use Facades\App\Http\Controllers\Trax\RssController;

use Carbon\Carbon;

class Ticket
{
    CONST CACHE_KEY="TICKET";

    function getCacheKey($key)
    {
        $key = strtoupper($key);
        
        return self::CACHE_KEY .".$key";
    }

    function set($ticket)
    {
        if( cache()->has( $this->getCacheKey("ALL") ) )
        {
            cache()->append( $this->getCacheKey("ALL"), $ticket );
        }
    }

    function getAll()
    {
        return cache()->get( $this->getCacheKey("ALL") );
    }
}
