<?php

namespace App\Repositories;

use App\Http\Controllers\Trax\ProxyBSDController;
use App\Http\Controllers\v4\TraxController;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Redis;

class Posizione {
    const CACHE_KEY = "POSIZIONE";

    public static function setLatests($retry = 3) {
        ini_set('memory_limit', '2048M');
        $fields = ["PartitionKey", "fixGps", "latitude", "longitude", "speed", "heading", "satellite", "altitude", "km", "battery", "fuel", "inputs", "outputs", "analog1", "analog2", "counter1", "counter2", "rftag_active", "rftag_battery", "rftag_buttons", "message", "driverid", "drivername", "stato", "inputsLed", "outputsLed", "inputsLedLabels", "outputsLedLabels", "event", "address"];

        $headers = [
            "Content-Type: application/json",
            "Accept: close"
        ];

        $data = (object) [
            'body' => [
                // 'Fields' => $fields,
                'TimeZoneAdjustment' => (new DateTimeZone('Europe/Rome'))->getOffset(new DateTime()) / 3600
            ]
        ];
        $new_pos = [];
        //? $old_positions =  prendo le posizioni attuali
        $current_all = TraxController::azure('api/v3/query/currentPositionsAll', 'GET', $data, $headers);
        if ((is_object($current_all) && property_exists($current_all, 'Positions')) || (is_array($current_all) && array_key_exists('Positions', $current_all))) {
            $current_all = $current_all->Positions ?? $current_all['Positions'];
        } else {
            if ($retry >= 1) {
                static::setLatests(($retry - 1));
            } else {
                exit("Non so cosa sia successo ma non riesco ad aggiornare le posizioni!<br>" . $current_all);
            }
        }
        // $current_all = ((object) (new ProxyBSDController((new Request([])), 'GET', 'v3/query/currentPositionsAll', $data))->call())->Positions;

        foreach ($current_all as $current) {
            $current = (object) $current;
            if (!is_numeric($current->PartitionKey) || in_array($current->PartitionKey, ['0', '0000000001']) && !empty($current)) {
                continue;
            }
            $new_pos[$current->PartitionKey] = json_encode($current);
            // Redis::hset('current', $current->PartitionKey, json_encode($current));
        }
        if (!empty($new_pos)) {
            Redis::hmset('current', $new_pos);
        }


        return true;
    }

    public static function getLatests() {
        $results = [];
        foreach (Redis::hgetAll('current') as $current) {
            $results[] = json_decode($current);
        }

        return $results;
    }

    public static function getLatestsIDs(array $IDs) {
        $results = [];
        foreach ($IDs as $current) {
            if (!is_null(Redis::hget('current', $current)))
                $results[] = json_decode(Redis::hget('current', $current));
        }

        return $results;
    }
}
