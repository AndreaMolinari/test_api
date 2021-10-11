<?php

namespace App\Common\Managers\Redis;

use App\Common\Managers\PosizioniManager;
use Illuminate\Support\Facades\Redis;

class SogliaTemperaturaManager extends PosizioniManager {
    const LATEST_KEY = "latestTemp";
    const LATEST_LOCK_KEY = "latestTemp:lock";

    public static function fetchAndUpdateLatests($retries = 3) {
        throw new \Exception('Method not implemented yet.');
    }

    public static function setLatests(array $temps): void {
        Redis::hmset(static::LATEST_KEY, $temps);
    }

    public static function setLatest(string $unitcode, array $temp): void {
        Redis::hset(static::LATEST_KEY, $unitcode, json_encode($temp));
    }
}
