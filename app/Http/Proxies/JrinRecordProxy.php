<?php

namespace App\Http\Proxies;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class JrinRecordProxy
{

    const BASE_URL        = 'https://gps.recorditalia.net/webtrax/api/v2/';
    const SECRET          = 'fje4930f394jfcl4j349347t8374ryg3';
    const DEFAULT_HEADERS = [
        'Content-Type: application/x-www-form-urlencoded',
        "Key: iu4kj34g"
    ];
    const CACHE_KEY_TOKEN = 'jRin:Record:token';
    const CACHE_KEY_USER  = 'jRin:Record:user';

    static $token;
    static $userId;

    private static function getToken()
    {
        return cache()->remember(static::CACHE_KEY_TOKEN, now()->addHours(71), function () {
            return static::call('account/authenticate', 'POST')['token'];
        });
    }

    private static function login()
    {
        return cache()->remember(static::CACHE_KEY_USER, now()->addHours(71), function () {
            return (object) static::call('account/login', 'POST', ['user' => 'record', 'pass' => md5('recordmeno20')])['login'][0];
        });
    }

    private static function init()
    {
        if (!isset(static::$token))
            static::$token = static::getToken();
        if (!isset(static::$userId))
            static::$userId = static::login()->userid;
    }

    private static function call(string $url, string $method = 'POST', array $body = [], bool $debug = false)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        // ? URL
        if (!empty(static::$token)) {
            $url .= '/' . static::$token;
        }

        // ? Body
        if (!empty(static::$userId) && !array_key_exists('userid', $body)) {
            $body['userid'] = static::$userId;
        }
        if (!empty(static::SECRET) && !array_key_exists('secret', $body)) {
            $body['secret'] = static::SECRET;
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => static::BASE_URL . $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => http_build_query($body),
            CURLOPT_HTTPHEADER => static::DEFAULT_HEADERS,
        ));
        $response = curl_exec($curl);

        $err = curl_error($curl);

        if ($debug) {
            dd(static::DEFAULT_HEADERS, $body, $method, static::BASE_URL . $url, $response, $err);
        }

        curl_close($curl);

        if ($err) {
            throw new HttpException(500, 'cURL Error #:' . $err);
            return 'cURL Error #:' . $err;
        } else {
            $response = json_decode($response, true);
            // dd($body, $response, static::url);
            if ($response == '' || (isset($response['system']['status']) && strcasecmp($response['system']['status'], 'ok') == 0)) {
                return $response;
            } else {
                throw new HttpException(500, 'qualcosa è andato storto nella chiamata TO');
            }
        }
    }

    public static function isConnected($unitcode)
    {
        static::init();
        // Il doppio not lo converte da int a bool
        return !!isset(static::call('unit/connected', 'POST', ['unitcode' => $unitcode])['unit']);
    }

    public static function getStatus($unitcode)
    {
        if (!static::isConnected($unitcode)) throw new BadRequestHttpException('Periferica non connessa');

        static::init();
        return static::call('unit/io', 'POST', ['unitcode' => $unitcode], false)['unit'];
    }

    public static function richiediPosizione($unitcode)
    {
        if (!static::isConnected($unitcode)) throw new BadRequestHttpException('Periferica non connessa');

        return !!isset(static::call('unit/position', 'POST', ['unitcode' => $unitcode])['unit'][0]['OK']);
    }

    public static function setStatus($unitcode, array $outputs)
    {
        if (static::isConnected($unitcode)) //? Se torna la posizione vuol dire che il mezzo è connesso e posso cambiare gli stati
        {
            $io = static::getStatus($unitcode);
            $thisOut = null;

            foreach ($io as $o) {
                $tmp = (object) $o;
                if ((strcasecmp($tmp->type, 'O') == 0) && (strcasecmp($tmp->label, $outputs['label']) == 0)) {
                    $thisOut = clone ($tmp);
                    $thisOut->status = ($thisOut->status == 0) ? (int) 0 : (int) 1;
                }
            }
            if ($outputs['newStatus'] == 1 || $outputs['newStatus'] === true || strcasecmp($outputs['newStatus'], 'true') == 0) {
                $outputs['newStatus'] = (int) 1;
            } else {
                $outputs['newStatus'] = (int) 0;
            }
            // exit( json_encode($outputs['newStatus']) );
            $newPosizione = false;
            if (!empty($thisOut) && $thisOut->status !== $outputs['newStatus']) {
                $richiesta = static::setSingleStatus($unitcode, (int) $thisOut->num, (int) $outputs['newStatus']);
            }
            // exit( json_encode($richiesta) );

            $newPosizione = static::richiediPosizione($unitcode);

            if ($newPosizione && isset($richiesta)) {
                // $return = (new TraxController)->mezzoPosizione((new Request()), $idServizio);
                $return = ["Success" => 'OK'];
            } elseif ($newPosizione && !isset($richiesta)) {
                $return = ["Success" => "Lo stato era già settato come richiesto"];
            } elseif (!$newPosizione && isset($richiesta)) {
                $return = ["Success" => "Lo stato dovrebbe essere cambiato ma la periferica ora risulta disconnessa"];
            } else {
                $return = ["Error" => "La periferica risulta disconnessa"];
            }
            // ? L'uscita è stata cambiata (se necessario). Richiedo un posizione e la torno fratè
            // TODO : qui sarebba carino controllare se lo stato è cambiato o no... magari aspettando qualche secondo!

            return $return;
        } else {
            throw new BadRequestHttpException('Periferica non connessa');
        }
    }

    public static function setSingleStatus($unitcode, int $outId, int $newStatus)
    {
        if (!static::isConnected($unitcode)) throw new BadRequestHttpException('Periferica non connessa');

        return static::call('unit/output/set', 'POST', [
            'channel' => '0',
            'unitcode' => $unitcode,
            'outid' => $outId,
            'outst' => $newStatus
        ]);
    }

    public static function allineaKM($unitcode, $km)
    {
        if (!static::isConnected($unitcode)) throw new BadRequestHttpException('Periferica non connessa');
        static::call('unit/km/set', 'POST', [
            'unitcode' => $unitcode,
            'km' => $km,
        ]);

        return static::richiediPosizione($unitcode);
    }

    public static function allineaMezzo($unitcode, $infoMezzo) {

        if (static::isConnected($unitcode)) {
            $allowed = ["unitcode", "brand", "model", "color", "plate", "year", "chassis", "type", "km"];

            $body = [];
            foreach ($infoMezzo as $key => $val) {
                if (in_array($key, $allowed) && (!empty($val) || ($key == 'km'))) {
                    if ($key == 'plate') {
                        $body[$key] = str_replace(" ", "", trim($val));
                    } else {
                        $body[$key] = str_replace(" ", "_", trim($val));
                    }
                }
            }
            $body['unitcode'] = $unitcode;
            static::call('unit/vehicle/set', 'POST', $body);
            return true;
        } else {
            throw new BadRequestHttpException('Periferica non connessa');
        }

    }
}
