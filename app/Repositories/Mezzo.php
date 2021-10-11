<?php
// namespace App\Repositories;

// use Carbon\Carbon;


// class Mezzo
// {
//     CONST CACHE_KEY="MEZZO";
//     CONST CACHE_TIME=1;

//     function getAll()
//     {
//         // ? Per marca modello targa telaio
//         return cache()->remember($this->getCacheKey("ALL"), Carbon::now()->addMinutes(self::CACHE_TIME), function(){
//             sleep(10);
//             return 8;
//         });
//     }

//     function getCacheKey($key)
//     {
//         $key = strtoupper($key);
        
//         return self::CACHE_KEY .".$key";
//     }
// }
