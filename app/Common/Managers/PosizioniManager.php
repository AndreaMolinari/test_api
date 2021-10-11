<?php

namespace App\Common\Managers;

use App\Http\Controllers\v4\TraxController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

/**
 * Acts as a layer between redis and the api
 *
 * Manage all the get and set mechanism with redis,
 * used primarily for updating the positions every minute
 *
 * @category BSD
 * @category Redis
 *
 * @author Damiano Pellegrini
 *
 * @see App\Console\Commands\Posizioni
 * @see App\Jobs\UpdatePosizioniJob
 *
 */
class PosizioniManager {
    const LATEST_KEY = "current";
    const LATEST_LOCK_KEY = "current:lock";

    /**
     * Aggiorna tutte le ultime posizioni del redis
     *
     * @category BSD
     * @category Redis
     *
     * @api
     *
     * @author Andrea Molinari
     * @author Damiano Pellegrini
     */
    public static function fetchAndUpdateLatests($retries = 3) {
        ini_set('memory_limit', '2048M');
        sleep(10);
        // $fields = ["PartitionKey", "fixGps", "latitude", "longitude", "speed", "heading", "satellite", "altitude", "km", "battery", "fuel", "inputs", "outputs", "analog1", "analog2", "counter1", "counter2", "rftag_active", "rftag_battery", "rftag_buttons", "message", "driverid", "drivername", "stato", "inputsLed", "outputsLed", "inputsLedLabels", "outputsLedLabels", "event", "address"];

        $headers = [
            "Content-Type: application/json",
            "Accept: close"
        ];

        $data = (object) [
            'body' => [
                // 'Fields' => $fields,
                'TimeZoneAdjustment' => round((new \Carbon\CarbonTimeZone('Europe/Rome'))->getOffset(new \Carbon\Carbon()) / 3600, 0), // Ottengo un numero intero dalla timezone eg.: +01:00 -> 1; -12:30 -> -12;
            ]
        ];

        $current_all = TraxController::azure('api/v3/query/currentPositionsAll', 'GET', $data, $headers);
        // Se è un oggetto o array con la proprieta 'Positions'
        if ((is_object($current_all) && property_exists($current_all, 'Positions')) || (is_array($current_all) && array_key_exists('Positions', $current_all))) {
            $current_all = $current_all->Positions ?? $current_all['Positions'];
        } else {
            if ($retries >= 1) {
                Log::channel('dev')->error('Riprovo il fetch delle posizioni number:' . $retries . '::file:' . __FILE__ . ' ::line: ' . __LINE__ . '::method:' . __METHOD__);
                return static::fetchAndUpdateLatests(($retries - 1));
            } else {
                Log::channel('dev')->error('Il fetch delle posizioni non è riuscito file:' . __FILE__ . ' ::line: ' . __LINE__ . '::method:' . __METHOD__);
                //? Non ritorno le posizioni invece di uscire
                return null;
                // exit("Non so cosa sia successo ma non riesco ad aggiornare le posizioni!<br>" . $current_all);
            }
        }

        $new_pos = [];
        $to_return = [];
        foreach ($current_all as $current) {
            $current = (object) $current;
            if (isset($current->ch)) {
                if (!App::environment('produzione') && env('APP_DEBUG', false) && env('LOG_POS_ROTTE', false))
                    Log::channel('dev')->critical('POS ROTTA: ' . json_encode($current));
            }
            if (!is_numeric($current->PartitionKey) || in_array($current->PartitionKey, ['0', '0000000001']) || empty($current) || !isset($current->latitude)) {
                continue;
            }
            $new_pos[$current->PartitionKey] = json_encode($current);
            $to_return[] = $current;
            // Redis::hset('current', $current->PartitionKey, json_encode($current));
        }

        if (!empty($new_pos)) {
            // Se lockato aspetta 2 millisecondi
            while (Redis::get(static::LATEST_LOCK_KEY)) {
                usleep(2000);
            }
            Redis::set(static::LATEST_LOCK_KEY, true);
            Redis::hmset(static::LATEST_KEY, $new_pos);
            Redis::set(static::LATEST_LOCK_KEY, false);
        }

        return $to_return;
    }

    /**
     * Ritorna tutte le ultime posizioni
     *
     * @return array Tutte le ultime posizioni nel redis
     *
     * @category BSD
     * @category Redis
     *
     * @api
     *
     * @author Damiano Pellegrini
     */
    public static function getLatests(): array {
        $positions = (Redis::hvals(static::LATEST_KEY) ?? []);
        foreach ($positions as &$position) {
            $position = json_decode($position);
        }

        return $positions;
    }

    /**
     * Ritorna l'ultima posizione per gli unitcode richiesti
     *
     * @param mixed|int[] $unitcodes Gli unitcode da cui prendere le posizioni
     *
     * @return array L'array delle posizioni
     *
     * @category BSD
     * @category Redis
     *
     * @api
     *
     * @author Damiano Pellegrini
     */
    public static function getLatestUnitcodes(...$unitcodes): array {
        if (!$unitcodes || count($unitcodes) === 0) return [];

        $positions = (Redis::hmget(static::LATEST_KEY, $unitcodes) ?? []);
        foreach ($positions as $key => &$position) {
            // Se lo unitcode richiesto non è nel redis mi da null e quindi lo rimuovo
            if (!$position) {
                unset($positions[$key]);
                continue;
            }
            $position = json_decode($position);
        }

        return $positions;
    }

    /**
     * Ritorna l'ultima posizione per l'unitcode richiesto
     *
     * @param mixed|int[] $unitcodes L'unitcode da cui prendere la posizione
     *
     * @return ?object La posizione
     *
     * @category BSD
     * @category Redis
     *
     * @api
     *
     * @author Damiano Pellegrini
     */
    public static function getLatestUnitcode(string $unitcode): ?object {
        return static::getLatestUnitcodes($unitcode)[0] ?? null;
    }
}
