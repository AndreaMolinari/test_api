<?php

namespace App\Common\Managers\Redis;

use App\Common\Managers\PosizioniManager;
use Illuminate\Support\Facades\Redis;

class TargetManager extends PosizioniManager {
    const LATEST_KEY = "latestTarget";
    const LATEST_LOCK_KEY = "latestTarget:lock";

    public static function fetchAndUpdateLatests($retries = 3) {
        throw new \Exception('Method not implemented yet.');
    }

    public static function setLatests(array $targetPos): void {
        Redis::hmset(static::LATEST_KEY, $targetPos);
    }

    public static function setLatest(string $unitcode, array $targetPos): void {
        Redis::hset(static::LATEST_KEY, $unitcode, json_encode($targetPos));
    }
}
