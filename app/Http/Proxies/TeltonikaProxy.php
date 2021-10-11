<?php

namespace App\Http\Proxies;

use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TeltonikaProxy {

    const BASE_URL = 'http://api.recorditalia.net:30001/';

    public static function isConnected($unitcode) {
        foreach (Http
            ::acceptJson()
            ->contentType('application/json')
            ->post(static::BASE_URL . 'ConnectedDevices')
            ->json() as $device) {
            if ($device['DeviceImei'] == $unitcode)
                return true;
        }

        return false;
    }

    public static function getStatus($unitcode) {
        if (!static::isConnected($unitcode)) throw new BadRequestHttpException('Periferica non connessa');

        throw new HttpException(501, 'Not implemented');
    }

    // public static function richiediPosizione($unitcode) {
    //     if (!static::isConnected($unitcode)) throw new BadRequestHttpException('Periferica non connessa');

    //     return Http
    //         ::acceptJson()
    //         ->contentType('application/json')
    //         ->post(static::BASE_URL . 'Command', [
    //             'imei' => $unitcode,
    //             'command' => 'getinfo'
    //         ])
    //         ->json();
    // }

    public static function setStatus($unitcode, $outputs) {
        if (!static::isConnected($unitcode)) throw new BadRequestHttpException('Periferica non connessa');

        //? Prendo l'id dalla label DOUT1 o DOUT2, basta che sia scritto alla fine
        $outId = $outputs['label'][strlen($outputs['label']) - 1] - 1;

        if ($outputs['newStatus'] == 1 || $outputs['newStatus'] === true || strcasecmp($outputs['newStatus'], 'true') == 0) {
            $outputs['newStatus'] = (int) 1;
        } else {
            $outputs['newStatus'] = (int) 0;
        }

        static::setSingleStatus($unitcode, (int) $outId, (int) $outputs['newStatus']);

        return ['message' => 'Comando inoltrato con successo'];
    }

    public static function setSingleStatus($unitcode, int $outId, int $newStatus) {
        if (!static::isConnected($unitcode)) throw new BadRequestHttpException('Periferica non connessa');

        // Build command
        $command = 'setdigout ??';
        $command[strpos($command, '?') + $outId] = $newStatus;

        return Http::acceptJson()
            ->contentType('application/json')
            ->post(static::BASE_URL . 'Command', [
                'imei' => $unitcode,
                'command' => $command
            ])
            ->json();
    }

    // public static function allineaKM($unitcode, $km)
    // {
    //     if (!static::isConnected($unitcode)) throw new BadRequestHttpException('Periferica non connessa');
    //     static::call('unit/km/set', 'POST', [
    //         'unitcode' => $unitcode,
    //         'km' => $km,
    //     ]);

    //     return static::richiediPosizione($unitcode);
    // }

    // public static function allineaMezzo($unitcode, $infoMezzo) {

    //     if (static::isConnected($unitcode)) {
    //         $allowed = ["unitcode", "brand", "model", "color", "plate", "year", "chassis", "type", "km"];

    //         $body = [];
    //         foreach ($infoMezzo as $key => $val) {
    //             if (in_array($key, $allowed) && (!empty($val) || ($key == 'km'))) {
    //                 if ($key == 'plate') {
    //                     $body[$key] = str_replace(" ", "", trim($val));
    //                 } else {
    //                     $body[$key] = str_replace(" ", "_", trim($val));
    //                 }
    //             }
    //         }
    //         $body['unitcode'] = $unitcode;
    //         static::call('unit/vehicle/set', 'POST', $body);
    //         return true;
    //     } else {
    //         throw new BadRequestHttpException('Periferica non connessa');
    //     }
    // }
}
