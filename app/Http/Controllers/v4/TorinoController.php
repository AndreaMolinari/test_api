<?php

namespace App\Http\Controllers\v4;

use App\Http\Controllers\Controller;
use App\Models\{TC_AnagraficaAnagraficaModel, TC_FlottaServizioModel, TT_ComponenteModel, TT_FlottaModel, TT_ServizioModel};
use App\Http\Requests\Trax\MesaroliRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class TorinoController extends Controller
{
    protected $record_baseUrl = "https://gps.recorditalia.net/webtrax/api/v2/";
    protected $record_secret = "fje4930f394jfcl4j349347t8374ryg3";
    protected $record_key = "iu4kj34g";

    protected $mls_baseUrl = "https://centrale.mechanicallinesolutions.net/webtrax/api/v2/";
    protected $mls_secret = "jdfkwe32ju34324nbjkbhjkbh888";
    protected $mls_key = "Pod854s6";

    protected $baseUrl = null;
    protected $secret = null;
    protected $key = null;
    protected $token = null;
    protected $userID = null;

    public $url = null;
    public $method = null;
    public $headers = null;
    public $body = null;

    const CACHE_KEY = "jRin";

    private function getCacheKey($key)
    {
        $key = strtoupper($key);
        return self::CACHE_KEY . ".$key";
    }

    public function getToken(bool $mls)
    {
        if ($mls) {
            return cache()->remember($this->getCacheKey("mls_token"), now()->addHours(71), function () {
                $this->method = 'POST';
                $this->url = "account/authenticate";
                $this->body = ["secret" => $this->secret];

                return $this->call()['token'];
            });
        } else {
            return cache()->remember($this->getCacheKey("token"), now()->addHours(71), function () {
                $this->method = 'POST';
                $this->url = "account/authenticate";
                $this->body = ["secret" => $this->secret];

                return $this->call()['token'];
            });
        }
    }

    public function login(bool $mls)
    {
        if ($mls) {
            return cache()->remember($this->getCacheKey("mls_utente"), now()->addHours(71), function () {
                $this->method = 'POST';
                $this->url = "account/login";
                $this->body = ["user" => "record", "pass" => md5("recordmeno20")];

                return (object) $this->call()['login'][0];
            });
        } else {
            return cache()->remember($this->getCacheKey("utente"), now()->addHours(71), function () {
                $this->method = 'POST';
                $this->url = "account/login";
                $this->body = ["user" => "record", "pass" => md5("recordmeno20")];

                return (object) $this->call()['login'][0];
            });
        }
    }

    public function call(bool $debug = false)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        // ? Headers
        if (!is_array($this->headers)) {
            $this->headers = ["Content-Type: application/x-www-form-urlencoded"];
        }
        if (!empty($this->key)) {
            $this->headers[] = 'Key: ' . $this->key;
        }

        // ? URL
        if (!empty($this->token)) {
            $this->url .= "/" . $this->token;
        }

        // ? Method
        if (empty($this->method)) {
            $this->method = 'POST';
        }

        // ? Body
        if (!empty($this->userID) && !array_key_exists('userid', $this->body)) {
            $this->body['userid'] = $this->userID;
        }
        if (!empty($this->secret) && !array_key_exists('secret', $this->body)) {
            $this->body['secret'] = $this->secret;
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->baseUrl . $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $this->method,
            CURLOPT_POSTFIELDS => http_build_query($this->body),
            CURLOPT_HTTPHEADER => $this->headers,
        ));
        $response = curl_exec($curl);

        $err = curl_error($curl);

        if ($debug) {
            dd($this->headers, $this->body, $this->method, $this->baseUrl . $this->url, $response, $err);
        }

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            $response = json_decode($response, true);
            // dd($this->body, $response, $this->url);
            if (isset($response['system']['status']) && strcasecmp($response['system']['status'], 'ok') == 0) {
                return $response;
            } else {
                return response()->json(["errore" => "qualcosa è andato storto nella chiamata TO"], 500);
            }
        }
    }

    // ? Questo metodo cerca lo unitcode principale del servizio fornito e logga nel servizio giusto per effettuare le richieste
    protected function find_unitcode_and_set_service(int $idServizio)
    {
        $device = null;
        $servizio = TT_ServizioModel::findOrFail($idServizio);

        $parent = TC_AnagraficaAnagraficaModel::where('idChild', $servizio->idAnagrafica)->first();

        if (is_null($parent) || $parent->idParent !== 40) {
            $this->baseUrl  = $this->record_baseUrl;
            $this->secret   = $this->record_secret;
            $this->key      = $this->record_key;
            $this->token    = $this->getToken(false);
            $this->userID   = $this->login(false)->userid;
        } else {
            $this->baseUrl  = $this->mls_baseUrl;
            $this->secret   = $this->mls_secret;
            $this->key      = $this->mls_key;
            $this->token    = $this->getToken(true);
            $this->userID   = $this->login(true)->userid;
        }

        if (count($servizio->gps) >= 1) {
            foreach ($servizio->gps as $i => $gps) {
                if ($i == 0 || $gps->servizioComponente->principale == 1) {
                    $device = $gps;
                }
            }
        }


        return $device->unitcode;
    }

    public function reqStatoIO($unitcode)
    {
        $this->method = 'POST';
        $this->url = 'unit/io';
        $this->headers = null;
        $this->body = [
            "unitcode" => $unitcode
        ];
        return $this->call()['unit'];
    }

    public function is_connected($unitcode)
    {
        $this->method = 'POST';
        $this->url = 'unit/connected';
        $this->headers = null;
        $this->body = [
            "unitcode" => $unitcode
        ];

        return (isset($this->call()['unit'])) ? true : false;
    }

    public function reqPosizione($unitcode)
    {
        $this->method = 'POST';
        // $this->url = 'unit/connected';
        $this->url = 'unit/position';
        $this->headers = null;
        $this->body = [
            "unitcode" => $unitcode
        ];

        return (isset($this->call()['unit'][0]['OK'])) ? true : false;
    }

    public function reqCambioIO($unitcode, int $outID, int $newStatus)
    {
        $this->method = 'POST';
        $this->url = "unit/output/set";
        $this->headers = null;
        $this->body = [
            "channel" => '0',
            "unitcode" => $unitcode,
            "outid" => $outID,
            "outst" => $newStatus
        ];
        return $this->call();
    }

    public function cambioStato(Request $request, $idServizio)
    {
        // $unitcode = json_decode((new ServizioController)->getSingolo($request, $idServizio), true)['componente'][0]['unitcode'];
        $unitcode = $this->find_unitcode_and_set_service($idServizio);

        if ($this->is_connected($unitcode)) //? Se torna la posizione vuol dire che il mezzo è connesso e posso cambiare gli stati
        {
            $io = $this->reqStatoIO($unitcode);
            $thisOut = null;

            foreach ($io as $o) {
                $tmp = (object) $o;
                if ((strcasecmp($tmp->type, 'O') == 0) && (strcasecmp($tmp->label, $request->label) == 0)) {
                    $thisOut = clone ($tmp);
                    $thisOut->status = ($thisOut->status == 0) ? (int) 0 : (int) 1;
                }
            }
            if ($request->newStatus == 1 || $request->newStatus === true || strcasecmp($request->newStatus, 'true') == 0) {
                $request->newStatus = (int) 1;
            } else {
                $request->newStatus = (int) 0;
            }
            // exit( json_encode($request->newStatus) );
            $newPosizione = false;
            if (!empty($thisOut) && $thisOut->status !== $request->newStatus) {
                $richiesta = $this->reqCambioIO($unitcode, (int) $thisOut->num, (int) $request->newStatus);
            }
            // exit( json_encode($richiesta) );

            $newPosizione = $this->reqPosizione($unitcode);

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
            return ["Error" => "Mezzo non connesso"];
        }
    }

    public function newPosition(Request $request, $idServizio)
    {
        // $unitcode = json_decode((new ServizioController)->getSingolo($request, $idServizio), true)['componente'][0]['unitcode'];
        $unitcode = $this->find_unitcode_and_set_service($idServizio);

        if ($this->reqPosizione($unitcode)) {
            return ["Success" => "La nuova posizione è stata richiesta"];
        } else {
            return ["Error" => "Mezzo non connesso"];
        }
    }

    public function vehicle(Request $request, $idServizio) // ! Ricorda che è finto
    {
        // $unitcode = json_decode((new ServizioController)->getSingolo($request, $idServizio), true)['componente'][0]['unitcode'];
        $unitcode = $this->find_unitcode_and_set_service($idServizio);
        // $unitcode = "2064112559";

        $to_update = [];

        // TODO: Abilitare solo quando WG è allineato!
        // $servizio = TT_ServizioModel::find($idServizio);
        // if( isset($servizio->mezzo) && count($servizio->mezzo) == 1 )
        // {
        //     $mezzo = $servizio->mezzo[0];
        //     // return $mezzo;
        //     $to_update['model']     = ( isset($mezzo->modello->modello) ) ? $mezzo->modello->modello : null;
        //     $to_update['brand']     = ( isset($mezzo->modello->brand->marca) ) ? $mezzo->modello->brand->marca : null;
        //     $to_update['plate']     = ( isset($mezzo->targa) ) ? $mezzo->targa : null;
        //     $to_update['color']     = ( isset($mezzo->colore) ) ? $mezzo->colore : null;
        //     $to_update['chassis']   = ( isset($mezzo->telaio) ) ? $mezzo->telaio : null;
        //     $to_update['year']      = ( isset($mezzo->anno) ) ? $mezzo->anno : null;
        // }

        $request->request->add($to_update);     // ? █▀█ █▀▀ █▀▀ █ █ █ █▀█
        $request->merge($to_update);            // ? █▄█ █▄▄ █▄▄ █▀█ █ █▄█
        // ? fatto cosi perchè con alclune request usa request->add e con altre il merge... non ho capito da cosa dipende, ma dove usa uno l'altro non fa un cazzo! QUINDI VA BENE!

        if ($this->is_connected($unitcode)) {
            $allowed = ["unitcode", "brand", "model", "color", "plate", "year", "chassis", "type", "km"];

            $this->method = 'POST';
            $this->url = 'unit/vehicle/set';
            $this->headers = null;
            $this->body = [];
            foreach ($request->all() as $key => $val) {
                if (in_array($key, $allowed) && (!empty($val) || ($key == 'km'))) {
                    if ($key == 'plate') {
                        $this->body[$key] = str_replace(" ", "", trim($val));
                    } else {
                        $this->body[$key] = str_replace(" ", "_", trim($val));
                    }
                }
            }
            $this->body['unitcode'] = $unitcode;
            $this->call();
            return true;
        } else {
            return ["error" => "mezzo non connesso"];
        }
        // return ( isset($this->call()['unit'][0]['OK']) ) ? true : false;
    }

    public function real_km(array $req)
    {
        if ($this->is_connected($req['unitcode'])) {
            $this->method = 'POST';
            $this->url = 'unit/km/set';
            $this->headers = null;
            $this->body = $req;
            $this->call();
            return true;
        } else {
            return ["error" => "mezzo non connesso"];
        }
    }

    public function km(Request $req, int $idServizio)
    {
        $validated = $req->validate(
            ['km' => 'required|numeric|max:9999999']
        );
        $validated['unitcode'] = $this->find_unitcode_and_set_service($idServizio);

        return $this->real_km($validated);
    }

    public function sync_vehicle(int $idServizio)
    {
        $servizio = TT_ServizioModel::findOrFail($idServizio);
        $unitcode = $this->find_unitcode_and_set_service($idServizio);

        $vehicle_info = [];

        if ($servizio->has('mezzo')) {
            $mezzo = $servizio->mezzo()->with('modello.brand')->first();
            // return $mezzo;
            $vehicle_info['model']     = $mezzo->modello->modello ?? null;
            $vehicle_info['brand']     = $mezzo->modello->brand->marca ?? null;
            $vehicle_info['plate']     = $mezzo->targa ?? null;
            $vehicle_info['color']     = $mezzo->colore ?? null;
            $vehicle_info['chassis']   = $mezzo->telaio ?? null;
            $vehicle_info['year']      = $mezzo->anno ?? null;
        } else {
            throw new UnprocessableEntityHttpException('Questo servizio non ha un mezzo associato!');
        }

        if ($this->is_connected($unitcode)) {
            $this->update_vehicle($unitcode, $vehicle_info);
            //TODO: Chiedi un altra posizione per vedere l'aggiornamento
            return ['message' => 'Mezzo aggiornato correttamente'];
        } else {
            throw new UnprocessableEntityHttpException('GPS irraggiungibile');
        }
        // return ( isset($this->call()['unit'][0]['OK']) ) ? true : false;
    }

    public function get_flotta(string $flotta = "MESAROLI")
    {
        $this->method = 'POST';
        $this->url = 'fleet/summary';
        $this->headers = null;
        $this->body = [
            "unitgroup" => $flotta
        ];

        return $this->call()['fleet'];
    }

    private function flotta_uc_servizi(int $idFlotta)
    {
        $flotta_servizi = TT_FlottaModel::findOrFail($idFlotta)->servizi;

        $servizi = [];
        foreach ($flotta_servizi as $servizio) {
            if (isset($servizio->gps[0]->unitcode)) {
                $servizi[$servizio->gps[0]->unitcode] = $servizio;
            }
        }
        return $servizi;
    }

    public function get_mesaroli(int $idFlotta)
    {
        $this->baseUrl  = $this->record_baseUrl;
        $this->secret   = $this->record_secret;
        $this->key      = $this->record_key;
        $this->token    = $this->getToken(false);
        $this->userID   = $this->login(false)->userid;

        $ibrido = [];

        $servizi = $this->flotta_uc_servizi($idFlotta);

        foreach ($this->get_flotta() ?? [] as $dev) {
            $uc = $dev['unitcode'];
            if (!array_key_exists($uc, $servizi)) continue;

            $servizio = $servizi[$uc];

            $tmp = (object)[];
            $tmp->idServizio = $servizio->id;
            $tmp->unitcode = $uc;
            $tmp->nickname = $dev['unitname'];
            $tmp->disponente = $dev['model'];
            $tmp->brand = (isset($servizio->mezzo[0])) ? $servizio->mezzo[0]->modello->brand->marca : null;
            $tmp->modello = (isset($servizio->mezzo[0])) ? $servizio->mezzo[0]->modello->modello : null;
            $tmp->targa = (isset($servizio->mezzo[0])) ? $servizio->mezzo[0]->targa : null;
            $tmp->telaio = (isset($servizio->mezzo[0])) ? $servizio->mezzo[0]->telaio : null;
            $tmp->icona = $servizio->pivot->icona ?? null;
            $ibrido[] = $tmp;
        }

        return $ibrido;
    }

    public function set_mesaroli(MesaroliRequest $request, int $idFlotta, int $idServizio)
    {
        $req = $request->validated();
        $unitcode = $this->find_unitcode_and_set_service($idServizio);

        $servizi = $this->flotta_uc_servizi($idFlotta);
        if (array_key_exists($unitcode, $servizi)) {
            $tc_flotta_servizio = TC_FlottaServizioModel::where(['idFlotta' => $servizi[$unitcode]->pivot->idFlotta, 'idServizio' => $servizi[$unitcode]->pivot->idServizio])->first();

            if (isset($req['nickname']) || isset($req['disponente'])) {
                $body = [];
                if (isset($req['nickname'])) {
                    $body['unitname'] = $req['nickname'];
                }
                if (isset($req['disponente'])) {
                    $body['model'] = str_replace(" ", "_", trim($req['disponente']));
                }
            }

            if (isset($body) && count($body) >= 1) {
                $body['unitcode'] = $unitcode;

                $this->method = 'POST';
                $this->url = 'unit/vehicle/set';
                $this->headers = null;
                $this->body = $body;

                $torino = $this->call();
            }

            if (isset($req['nickname']) || isset($req['icona'])) {
                $tc_flotta_servizio->nickname = $req['nickname'] ?? $tc_flotta_servizio->nickname;
                $tc_flotta_servizio->icona    = $req['icona'] ?? $tc_flotta_servizio->icona;

                $tc_flotta_servizio->save();
            }
        }

        return true;
    }
}
