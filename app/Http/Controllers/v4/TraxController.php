<?php

namespace App\Http\Controllers\v4;

use App\Http\Controllers\Controller;
use App\Models\{TC_FlottaServizioModel, TC_UtenteFlottaModel, TT_ComponenteModel, TT_FlottaModel, TT_ServizioModel, TT_UtenteModel};
use App\Common\Managers\PosizioniManager;
use App\Http\Requests\Targets\StoricoTargetRequest;
use App\Http\Requests\Targets\StoricoTriggerEventoRequest;
use App\Http\Requests\Trax\{ParzialeRequest, ResolveMezzoRequest, RigeneraIndirizziRequest, StoricoRequest};
use App\Models\Targets\TT_StoricoEventoModel;
use App\Models\Targets\TT_StoricoTargetModel;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use DateTime;
use DateTimeZone;
use Exception;
use Facades\App\Repositories\iHelpU;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Redis};
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class TraxController extends Controller {
    public static function azure($path, $method, $data, $headers = null) {
        if (is_null($headers)) {
            $headers = [
                "Content-Type: application/json",
                "Accept: application/json"
            ];
        }
        $url = env('AZURE');
        if (property_exists($data, 'body')) {
            $url .= $path;
            $body = json_encode($data->body);
        } elseif (property_exists($data, 'query')) {
            $url .= $path . '?' . http_build_query($data->query);
        }
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        // exit(json_encode($this));

        $curl_array = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 90,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
        ];

        if (isset($body)) {
            $curl_array[CURLOPT_POSTFIELDS] = $body;
        }

        curl_setopt_array($curl, $curl_array);

        $response = curl_exec($curl);

        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            throw new Exception($err);
        } else {
            return json_decode($response, true);
        }
    }
    protected function request_validator_get_records(Request $request) {
    }
    protected function request_validator_get_records_from_events(Request $request) {
    }
    protected function request_validator_heartbeat(Request $request) {
        return $request->validate([
            'level'     => 'required|in:low,high',
            'title'     => 'present',
            'message'   => 'required',
            'expire_in' => 'nullable|numeric'
        ]);
    }

    private function get_unitcode(int $idServizio) {
        $servizio = TT_ServizioModel::findOrFail($idServizio);

        $gps = null;
        foreach ($servizio->gps as $g) {
            if (is_null($gps) || $g->servizioComponente->principale == 1) {
                $gps = $g;
            }
        }
        if (is_null($gps)) {
            return null;
        } else {
            return $gps->unitcode;
        }
    }

    /**
     * @OA\Get(
     *  path="/owner/flotta", summary="Lista flotte per utente ed eventuali sottoutenti",
     *     @OA\Parameter(
     *         name="secret", required=true, in="header",
     *         @OA\Schema(type="string", format="string", example="*******************")
     *     ),
     *     @OA\Parameter(
     *         name="X-Requested-With", required=true, in="header",
     *         @OA\Schema(type="string", format="string", example="XMLHttpRequest")
     *     ),
     *     @OA\Parameter(
     *         name="Content-Type", required=true, in="header",
     *         @OA\Schema(type="string", format="string", example="application/json")
     *     ),
     *     @OA\Parameter(
     *         name="Accept", required=true, in="header",
     *         @OA\Schema(type="string", format="string", example="application/json")
     *     ),
     *     @OA\Response(response=200, description="OK")
     * )
     */
    public function utenti_flotte() {
        $listaUtenti = [];
        /** @var TT_UtenteModel */
        $user = Auth::user();
        if ($user->getRoleLevel() <= 3) {
            // ? Sei almeno un operatore, ti do la lista di tutti gli utenti
            $utenti = DB::table('TT_Utente')
                ->select(['id', 'idParent', 'username'])
                ->get();
        } elseif ((!is_null($user)) && $user->getRoleLevel() == 4) {
            $utenti = RivenditoreController::get_utenti();
        } else {
            // ? Sei un utente, ti do la lista di tutti i tuoi utenti!
            $utenti = DB::table('TT_Utente')
                ->select(['id', 'idParent', 'username'])
                ->where('id', Auth::user()->id)
                ->orWhere('idParent', Auth::user()->id)
                ->get();
        }
        $utenti      = iHelpU::groupBy($utenti, 'id');
        $flotta      = iHelpU::groupBy(TT_FlottaModel::all(), 'id');
        $user_flotta = iHelpU::groupBy(TC_UtenteFlottaModel::all(), 'idUtente');

        foreach (array_keys($utenti) ?? [] as $idUtente) {
            $tmp = (object) [];

            $tmp->id = $idUtente;

            $tmp->username = (array_key_exists($idUtente, $utenti)) ? $utenti[$idUtente][0]->username : null;

            if (array_key_exists($idUtente, $user_flotta)) {
                $tmp->flotta = [];

                foreach ($user_flotta[$idUtente] as $una_flotta) {
                    if (array_key_exists($una_flotta->idRiferimento, $flotta)) {
                        $f = (object) clone ($flotta[$una_flotta->idRiferimento][0]);
                        $f->nickname = $una_flotta->nickname;
                        $f->principale = $una_flotta->principale;
                        $tmp->flotta[] = $f;
                    }
                }
                // ? Sei sicuro di non dare l'utente se non ha flotte?
                if (!empty($tmp->flotta)) {
                    $listaUtenti[] = $tmp;
                }
            }
        }

        return $listaUtenti;
    }

    /**
     * @OA\Post(
     *  path="/owner/posizione/flotta/{idFlotta}",
     *  summary="Posizione di tutti i dispositivi nella flotta selezionata",
     *     @OA\Parameter(
     *         name="secret", required=true, in="header",
     *         @OA\Schema(type="string", format="string", example="*******************")
     *     ),
     *     @OA\Parameter(
     *         name="X-Requested-With", required=true, in="header",
     *         @OA\Schema(type="string", format="string", example="XMLHttpRequest")
     *     ),
     *     @OA\Parameter(
     *         name="Content-Type", required=true, in="header",
     *         @OA\Schema(type="string", format="string", example="application/json")
     *     ),
     *     @OA\Parameter(
     *         name="Accept", required=true, in="header",
     *         @OA\Schema(type="string", format="string", example="application/json")
     *     ),
     *  @OA\Parameter(
     *       name="idFlotta", required=true, in="path", @OA\Schema(type="integer")
     *  ),
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       @OA\Property(property="TimeZoneAdjustment", type="number", format="number", example="2")
     *    ),
     * ),
     *     @OA\Response(response=200, description="OK")
     * )
     */
    public function flotta_posizione(Request $request, string $params, int $idFlotta) {
        $servizi = TT_FlottaModel::findOrFail($idFlotta)->servizi->load('mezzo.modello.tipologia');
        // $servizi = TT_FlottaModel::findOrFail($idFlotta)->servizi()->with('mezzo')->get();

        $servizi_trax = [];
        foreach ($servizi as $servizio) {
            $tmp             = (object) [];
            $tmp->idServizio = $servizio->id;
            $tmp->nickname   = $servizio->pivot->nickname;
            $tmp->icona      = $servizio->pivot->icona;
            $tmp->dataInizio = $servizio->dataInizio;
            $tmp->dataFine   = $servizio->dataFine;
            $tmp->targa      = null;
            $tmp->km_totali  = null;
            $tmp->ore_totali = null;
            $tmp->telaio     = null;
            $tmp->modello    = null;
            $tmp->tipologia  = null;
            $tmp->brand      = null;
            $tmp->unitcode   = null;
            $tmp->parziale   = null;
            $tmp->posizione  = null;
            // $tmp->movediamo = [1,2,3,4];

            if (count($servizio->mezzo) >= 1) {
                $tmp->targa      = $servizio->mezzo[0]->targa;
                $tmp->telaio     = $servizio->mezzo[0]->telaio;
                $tmp->modello    = $servizio->mezzo[0]->modello->modello;
                $tmp->tipologia  = $servizio->mezzo[0]->modello->tipologia;
                $tmp->brand      = $servizio->mezzo[0]->modello->brand->marca;
                $tmp->km_totali  = $servizio->mezzo[0]->km_totali;
                $tmp->ore_totali = $servizio->mezzo[0]->ore_totali;
            }

            $tmp_gps = null;
            if (count($servizio->gps) >= 1) {
                foreach ($servizio->gps as $i => $gps) {
                    if ($i == 0 || $gps->servizioComponente->principale == 1) {
                        $tmp_gps = $gps;
                    }
                }
            }
            try {
                if (!is_null($tmp_gps)) {
                    $tmp->unitcode = $tmp_gps->unitcode;
                    $tmp->parziale = $tmp_gps->servizioComponente->parziale;

                    $tmp->posizione = PosizioneController::latest($tmp->unitcode);
                    if ($tmp->posizione) {
                        $tmp->km_totali = $tmp->posizione->km;
                    }
                }
            } catch (\Throwable $th) {
                $tmp->posizione = null;
                // throw $th;
            }
            finally {
                $servizi_trax[] = $tmp;
            }
        }
        // krsort($servizi_trax);

        return array_values($servizi_trax);
    }

    /**
     * @OA\Post(
     *  path="/owner/posizione/servizio/{idServizio}", summary="Posizione di un servizio specifico",
     *     @OA\Parameter(
     *         name="secret", required=true, in="header",
     *         @OA\Schema(type="string", format="string", example="*******************")
     *     ),
     *     @OA\Parameter(
     *         name="X-Requested-With", required=true, in="header",
     *         @OA\Schema(type="string", format="string", example="XMLHttpRequest")
     *     ),
     *     @OA\Parameter(
     *         name="Content-Type", required=true, in="header",
     *         @OA\Schema(type="string", format="string", example="application/json")
     *     ),
     *     @OA\Parameter(
     *         name="Accept", required=true, in="header",
     *         @OA\Schema(type="string", format="string", example="application/json")
     *     ),
     *     @OA\Parameter(
     *          name="idServizio", required=true, in="path", @OA\Schema(type="integer")
     *     ),
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       @OA\Property(property="TimeZoneAdjustment", type="number", format="number", example="2")
     *    ),
     * ),
     *     @OA\Response(response=200, description="OK")
     * )
     */
    public function mezzo_posizione(int $idServizio) {
        $uc = $this->get_unitcode($idServizio);
        if (is_null($uc)) {
            return [];
        } else {
            return PosizioneController::latest($uc);
        }
    }

    /**
     * @OA\Post(
     *  path="/owner/storico/{idServizio}",
     *  summary="Storico di posizioni per un servizio",
     *     @OA\Parameter(
     *         name="secret", required=true, in="header",
     *         @OA\Schema(type="string", format="string", example="*******************")
     *     ),
     *     @OA\Parameter(
     *         name="X-Requested-With", required=true, in="header",
     *         @OA\Schema(type="string", format="string", example="XMLHttpRequest")
     *     ),
     *     @OA\Parameter(
     *         name="Content-Type", required=true, in="header",
     *         @OA\Schema(type="string", format="string", example="application/json")
     *     ),
     *     @OA\Parameter(
     *         name="Accept", required=true, in="header",
     *         @OA\Schema(type="string", format="string", example="application/json")
     *     ),
     *  @OA\Parameter(
     *       name="idServizio", required=true, in="path", @OA\Schema(type="integer")
     *  ),
     *
     * @OA\RequestBody(
     *    required=true,
     *    @OA\JsonContent(
     *       @OA\Property(property="TimeZoneAdjustment", type="number", format="number", example="2"),
     *       @OA\Property(property="FromDate", type="string", format="string", example="2020-08-01 00:00:00"),
     *       @OA\Property(property="ToDate", type="string", format="string", example="2020-08-01 23:59:59")
     *    ),
     * ),
     *     @OA\Response(response=200, description="OK")
     * )
     */
    public function storico(StoricoRequest $request, int $idServizio) //! TU SEI STORICO
    {
        return $this->real_storico($request->validated(), $idServizio);
    }

    private function real_storico(array $req, int $idServizio) {
        $uc = $this->get_unitcode($idServizio);
        $req['FromDate']           = isset($req['FromDate']) ? $req['FromDate'] : (new Carbon())->format('Y-m-d 00:00:00');
        $req['ToDate']             = isset($req['ToDate']) ? $req['ToDate'] : (new DateTime('now', new DateTimeZone('Europe/Rome')))->format('Y-m-d H:i:s');
        $req['TimeZoneAdjustment'] = isset($req['TimeZoneAdjustment']) ? $req['TimeZoneAdjustment'] : ((new DateTimeZone('Europe/Rome'))->getOffset(new DateTime()) / 3600);

        $req['DeviceId'] = $uc;
        $req['Filter'] = null;
        $req['Fields'] = [];

        $posizioni = $this->azure('api/v3/query/getRecords', 'GET', (object)['query' => $req]);
        if ((is_object($posizioni) && property_exists($posizioni, 'ReturnData')) || (is_array($posizioni) && array_key_exists('ReturnData', $posizioni))) {
            $posizioni = $posizioni->ReturnData ?? $posizioni['ReturnData'];
        }

        $storico = PosizioneController::cast_bulk($posizioni);

        return $storico;
    }

    public function storicoTarget(StoricoTargetRequest $request) {
        $data = $request->validated();

        $builder = TT_StoricoTargetModel::with([
            'trigger',
            'servizio',
            'tipologia',
        ]);

        // Se c'è un target per cui filtrare
        if (isset($data['target']['id'])) {
            $builder->where('trigger_id', $data['target']['id']);
        }

        // Se è specificato un filtro tipologia
        if (isset($data['tipologia']['id'])) {
            $builder->where('idTipologia', $data['trigger']['id']);
        }

        // Filtro per flotta o servizio ANCHE SCADUTI
        if (isset($data['flotta']['id'])) {
            $servicesIds = TC_FlottaServizioModel::query()
                ->where('idFlotta', $data['flotta']['id'])
                ->get()
                ->pluck('idServizio')
                ->toArray();
            $builder->whereIn('idServizio', $servicesIds);
        } else if (isset($data['servizio']['id'])) {
            $builder->where('idServizio', $data['servizio']['id']);
        }

        $fromDate = $data['FromDate'] ?? new \Carbon\Carbon();
        $toDate = $data['ToDate'] ?? (new \Carbon\Carbon())->addSeconds((60 * 60 * 24) - 1 /* Add 23:59:59 */);

        $builder->where('rawPositionJson->fixGps', '>=', $fromDate);
        $builder->where('rawPositionJson->fixGps', '<=', $toDate);

        return $builder->get()->each(function ($item) {
            // Controllo se l'area è stata eliminata o meno
            if ($item->trigger)
                $item->trigger->setAppends([]); // Rimuovo il geo_json che sarebbe inutile e ridondante
        });
    }

    public function storicoTriggerEvento(StoricoTriggerEventoRequest $request, string $trigger, ?int $idTrigger = null) {
        $data = $request->validated();

        $builder = TT_StoricoEventoModel::with([
            'servizio',
            'evento',
            'trigger',
        ]);

        $fromDate = (new \Carbon\Carbon($data['FromDate'] ?? now()->startOfDay()))->toISOString();
        $toDate = (new \Carbon\Carbon($data['ToDate'] ?? $fromDate))->endOfDay()->toISOString();

        $builder->where(
            fn ($builder) =>  $builder
                ->where('posizione->fixGps', '>=', $fromDate)
                ->where('posizione->fixGps', '<=', $toDate)
        );

        if ($data['servizi'] ?? false)
            $builder->whereIn('idServizio', collect($data['servizi'])->pluck('id')->toArray());

        if ($data['tipologia']['id'] ?? false)
            $builder->where('idTipologiaEvento', $data['tipologia']['id']);

        if ($idTrigger ?? false)
            $builder->where('trigger_id', $idTrigger);

        switch ($trigger) {
            case 'target':
                $builder->where('trigger_type', 'TT_Area');
                break;
            case 'soglia':
                $builder->where('trigger_type', 'TT_Soglia');
                break;
        }

        // dd($trigger, $data, $idTrigger);
        // $builder->dd();
        return $builder->get();
    }

    public function parziale(ParzialeRequest $request, int $idServizio = null) //! TU SEI PARZIALE
    {
        return $this->real_parziale($request->validated(), $idServizio);
    }

    function real_parziale(array $req, int $idServizio = null) //! TU SEI PARZIALE
    {
        if (is_null($idServizio)) {
            $idServizio = $req['idServizio'];
            unset($req['idServizio']);
        }


        $req['FromDate']           = isset($req['FromDate']) ? $req['FromDate'] : (new Carbon())->format('Y-m-d 00:00:00');
        $req['ToDate']             = isset($req['ToDate']) ? $req['ToDate'] : (new DateTime('now', new DateTimeZone('Europe/Rome')))->format('Y-m-d H:i:s');
        $req['TimeZoneAdjustment'] = isset($req['TimeZoneAdjustment']) ? $req['TimeZoneAdjustment'] : ((new DateTimeZone('Europe/Rome'))->getOffset(new DateTime()) / 3600);
        $req['ExcludeData']        = (isset($req['ExcludeData']) && $req['ExcludeData'] === true) ? 'true' : 'false';
        $req['StartCondition']     = "eventNormalized=" . (isset($req['StartCondition']) ? $req['StartCondition'] : 1000);
        $req['EndCondition']       = "eventNormalized=" . (isset($req['EndCondition']) ? $req['EndCondition'] : 1001);
        $req['DeviceId']           = $this->get_unitcode($idServizio);


        $return_data = $this->azure('api/v3/query/getRecordsFromEvents', 'GET', (object)['query' => $req]);
        if ((is_object($return_data) && property_exists($return_data, 'ReturnData')) || (is_array($return_data) && array_key_exists('ReturnData', $return_data))) {
            $return_data = $return_data->ReturnData ?? $return_data['ReturnData'];
        }

        // Divisione in giornate
        $storico = [];
        foreach ($return_data as $record_groups) {
            $tmp = (object)['data' => $record_groups['Data'], 'recordGroups' => []];
            foreach ($record_groups['RecordGroups'] as $group) {
                $tmp_group = (object)[];
                if ($req['ExcludeData'] !== 'true') {
                    $tmp_group->tratta       = PosizioneController::cast_bulk($group['Records']['Tratta']);
                    $tmp_group->sleep        = PosizioneController::cast_bulk($group['Records']['Sleep']);
                }
                $tmp_group->giornaliero  = $group['Statistiche'];

                $tmp->recordGroups[] = $tmp_group;
            }
            $tmp->globale = $record_groups['StatisticheGlobali'];
            $storico[] = $tmp;
        }

        // Calcolo fermate
        foreach ($storico ?? [] as $giornata) {
            $fermate = 0;
            foreach ($giornata->recordGroups ?? [] as $tratte) {
                $is_fermo = 0;
                foreach ($tratte->tratta ?? [] as $posizione) {
                    if ($posizione->speed <= 1) {
                        $is_fermo += 1;

                        if ($is_fermo == 2) {
                            $fermate += 1;
                        }
                    } else {
                        $is_fermo = 0;
                    }
                }
            }

            $giornata->globale[] = [
                'Codice'         => 'NUMERO_FERMATE',
                'Valore'         => $fermate,
                'FormattedValue' => $fermate,
            ];
        }
        return $storico;
    }

    /** @deprecated */
    public function globale(ParzialeRequest $request, int $idServizio = null) {
        $req = $request->validated();

        if (is_null($idServizio)) {
            $idServizio = $req['idServizio'];
            unset($req['idServizio']);
        }

        $req['FromDate']           = isset($req['FromDate']) ? $req['FromDate'] : (new Carbon())->format('Y-m-d 00:00:00');
        $req['ToDate']             = isset($req['ToDate']) ? $req['ToDate'] : (new DateTime('now', new DateTimeZone('Europe/Rome')))->format('Y-m-d H:i:s');
        $req['TimeZoneAdjustment'] = isset($req['TimeZoneAdjustment']) ?? ((new DateTimeZone('Europe/Rome'))->getOffset(new DateTime()) / 3600);
        $req['ExcludeData']        = 'true';
        $req['StartCondition']     = "eventNormalized=" . (isset($req['StartCondition']) ? $req['StartCondition'] : 1000);
        $req['EndCondition']       = "eventNormalized=" . (isset($req['EndCondition']) ? $req['EndCondition'] : 1001);
        $req['DeviceId']           = $this->get_unitcode($idServizio);

        //? qua potresti controllare se per quella data il servizio esisteva

        $return_data = $this->azure('api/v3/query/getRecordsFromEvents', 'GET', (object)['query' => $req]);
        if ((is_object($return_data) && property_exists($return_data, 'ReturnData')) || (is_array($return_data) && array_key_exists('ReturnData', $return_data))) {
            $return_data = $return_data->ReturnData ?? $return_data['ReturnData'];
            if (count($return_data) >= 1) {
                $return_data = $return_data[(count($return_data) - 1)]['StatisticheGlobali'];
            } else {
                $return_data = null;
            }
        }
        return $return_data;
    }

    public function rigenera_indirizzi(RigeneraIndirizziRequest $request, int $idComponente) {
        $req = $request->validated();

        $componente = TT_ComponenteModel::findOrFail($idComponente);

        $req['UnitCode'] = $componente->unitcode;
        $req['Force'] = true;

        return $this->azure('api/v2/reverse/addFromDateToDate', 'POST', (object)['body' => $req]);
    }

    public function tachigrafo(Request $request) {
        /**
         * @var TT_UtenteModel
         */
        $user = Auth::user();
        if ($user->getRoleLevel() <= 3) {
            $user->actiaUser = 'record';
            $user->actiaPassword = 'Recordmeno20';
        }

        if (empty($user->actiaUser) || empty($user->actiaPassword)) {
            return response()->json("Utente Actia Non trovato", 404);
        }

        try {
            $url = "https://www.actiafleet.com/api/account/authentication";

            $data = json_encode([
                "username" => $user->actiaUser,
                "password" => $user->actiaPassword
            ]);

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Api-Version: 3.0.0',
                'Accept: application/json',
                'Referer: https://web.recorditalia.net'
            ]);

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

            $result = json_decode(curl_exec($curl));
            curl_close($curl);

            $result = base64_encode($result->token);

            $result = "https://sctacho.recorditalia.net/bauth.html?a=" . $result . "&b=fallback_url&c=d";

            $result = ["link" => $result];
            // exit(json_encode($result));
            return $result;
        } catch (\Exception $th) {
            throw $th;
        }
    }

    public function getCurrentPositionAll(int $unitcode = null) {
        if (is_null($unitcode)) {
            return PosizioniManager::getLatests();
        } else {
            return PosizioniManager::getLatestUnitcodes($unitcode);
        }
    }

    public function set_heartbeat(Request $request) {
        $request = $this->request_validator_heartbeat($request);

        $allarm = [
            'status'    => 'error',
            'level'     => $request['level'],
            'title'     => $request['title'],
            'message'   => $request['message'],
        ];

        if (!is_null($request['expire_in'])) {
            $expire = $request['expire_in'] * 60;
        } else {
            $expire = 30;
        }


        return Redis::set('MANUTENZIONE', json_encode($allarm), 'EX', $expire);
    }

    public function get_heartbeat() {
        // if (!is_null(Redis::get('MANUTENZIONE'))) {
        return Redis::get('MANUTENZIONE') ?? ['status' => 'ok'];
        // } else {
        //     return ['status' => 'ok'];
        // }
    }

    public function resolve_real_mezzo(ResolveMezzoRequest $request, int $idServizio) {
        $servizio = TT_ServizioModel::findOrFail($idServizio)->load('mezzo');
        $val_data = $request->validated();

        if (!$servizio->has('mezzo')) return new UnprocessableEntityHttpException('Il servizio non ha alcun mezzo associato.');

        $mezzo = $servizio->mezzo[0];
        $mezzo->km_totali = $val_data['km_totali'];
        $mezzo->ore_totali = $val_data['ore_totali'];

        $mezzo->save();

        // Aggiorno i km della periferica
        return (new TorinoController)->km(iHelpU::mkRequest(['km' => $val_data['km_totali']], Auth::id()), $idServizio);

        return response()->noContent();
    }

    public function tracciato(ParzialeRequest $request, int $idServizio = null) {
        $request_data = $request->validated();
        if (!is_null($idServizio)) $servizio = TT_ServizioModel::findOrFail($idServizio);
        $return = [];
        // Se viene passato un servizio nella rotta usa quello altrimenti usa l'array passato nel body se non presenta cicla un array vuoto
        $request_data['servizi'] = [$idServizio];
        foreach ($request_data['servizi'] as $idServizio) {
            $iter_servizio = TT_ServizioModel::findOrFail($idServizio);
            // ? Prendo il valore parziale del componente principale se presente altrimenti lo prendo dal primo
            $is_parziale = $iter_servizio->gps()->orderBy('principale', 'DESC')->first()->servizioComponente->parziale;

            if ($is_parziale) {
                return  $this->real_parziale($request_data, $iter_servizio->id);
            } else {
                $resp = $this->real_storico($request_data, $iter_servizio->id);

                return $this->split_storico($resp, isset($request_data['TimeZoneAdjustment']) ? $request_data['TimeZoneAdjustment'] : ((new DateTimeZone('Europe/Rome'))->getOffset(new DateTime()) / 3600));
            }
        }
        return $return;
    }

    private function split_storico($resp, $timezone) {
        $rows = [];
        foreach ($resp as $pos) {
            // Sposto la timezone della data a quella passata
            $data = (new Carbon($pos->fixGps))->timezone(new CarbonTimeZone($timezone))->isoFormat('YYYY-MM-DD');
            // $pos->fixGps = (new Carbon($pos->fixGps))->timezone(new CarbonTimeZone($timezone))->toISOString(true);

            if (!array_key_exists($data, $rows)) {
                $rows[$data] = [];
            }
            $rows[$data][] = $pos;
        }
        $return = [];
        foreach ($rows as $data => $tratta) {
            $km = last($tratta)->km - $tratta[0]->km;
            $return[] = (object) [
                'data' => $data,
                'recordGroups' => [
                    (object)[
                        'tratta' => $tratta,
                        'sleep' => [],
                        'giornaliero' => [
                            (object) [
                                'Codice'         => 'KM_PERCORSI',
                                'Valore'         => $km,
                                'FormattedValue' => $km . ' km',
                            ],
                        ],
                    ]
                ],
                'globale' => [],
            ];
        }
        return $return;
    }

    private function real_autisti_per_mezzo(array $req) {
        $req['FromDate']           = isset($req['FromDate']) ? $req['FromDate'] : (new Carbon())->format('Y-m-d 00:00:00');
        $req['ToDate']             = isset($req['ToDate']) ? $req['ToDate'] : (new DateTime('now', new DateTimeZone('Europe/Rome')))->format('Y-m-d H:i:s');
        $req['TimeZoneAdjustment'] = isset($req['TimeZoneAdjustment']) ? $req['TimeZoneAdjustment'] : ((new DateTimeZone('Europe/Rome'))->getOffset(new DateTime()) / 3600);

        $return_data = $this->azure('api/v3/query/unitcodeDrivers', 'GET', (object)['query' => $req]);

        if ((is_object($return_data) && property_exists($return_data, 'ReturnData')) || (is_array($return_data) && array_key_exists('ReturnData', $return_data))) {
            $return_data = $return_data->ReturnData ?? $return_data['ReturnData'];
        }

        $autisti = [];

        foreach ($return_data as $radiocomando => $dates) {
            foreach ($dates as $data) {
                $tmp                 = (object)[];
                $tmp->idRadiocomando = $radiocomando;
                $tmp->start          = $data['Min'];
                $tmp->end            = $data['Max'];

                $autisti[] = $tmp;
            }
        }

        return $autisti;
    }

    private function real_mezzi_per_autista(array $req) {
        $req['FromDate']           = isset($req['FromDate']) ? $req['FromDate'] : (new Carbon())->format('Y-m-d 00:00:00');
        $req['ToDate']             = isset($req['ToDate']) ? $req['ToDate'] : (new DateTime('now', new DateTimeZone('Europe/Rome')))->format('Y-m-d H:i:s');
        $req['TimeZoneAdjustment'] = isset($req['TimeZoneAdjustment']) ? $req['TimeZoneAdjustment'] : ((new DateTimeZone('Europe/Rome'))->getOffset(new DateTime()) / 3600);

        $return_data = $this->azure('api/v3/query/drivers', 'GET', (object)['query' => $req]);

        if ((is_object($return_data) && property_exists($return_data, 'ReturnData')) || (is_array($return_data) && array_key_exists('ReturnData', $return_data))) {
            $return_data = $return_data->ReturnData ?? $return_data['ReturnData'];
        }

        $autisti = [];

        foreach ($return_data as $unitcode => $dates) {
            $idServizio = TT_ComponenteModel::firstWhere('unitcode', $unitcode)->servizio_gps()->attivi()->first()->id;
            foreach ($dates as $data) {
                $tmp             = (object)[];
                $tmp->idServizio = $idServizio;
                $tmp->start      = $data['Min'];
                $tmp->end        = $data['Max'];

                $autisti[] = $tmp;
            }
        }

        return $autisti;
    }

    public function autisti_per_mezzo(StoricoRequest $request, int $idServizio) {
        $servizio = TT_ServizioModel::findOrFail($idServizio);
        $req = $request->validated();
        $req['DeviceId'] = $servizio->get_unitcode();

        return $this->real_autisti_per_mezzo($req);
    }

    public function mezzi_per_autista(StoricoRequest $request, int $idRadiocomando) {
        $componente = TT_ComponenteModel::findOrFail($idRadiocomando);
        $req = $request->validated();
        $req['driverid'] = $componente->unitcode;

        return $this->real_mezzi_per_autista($req);
    }

    public function parzialeGlobale(array $req, int $idServizio) // ? Lo uso in manutenzione
    {
        if (is_null($idServizio)) {
            $idServizio = $req['idServizio'];
            unset($req['idServizio']);
        }

        $req['FromDate']           = isset($req['FromDate']) ? $req['FromDate'] : (new Carbon())->format('Y-m-d 00:00:00');
        $req['ToDate']             = isset($req['ToDate']) ? $req['ToDate'] : (new DateTime('now', new DateTimeZone('Europe/Rome')))->format('Y-m-d H:i:s');
        $req['TimeZoneAdjustment'] = isset($req['TimeZoneAdjustment']) ? $req['TimeZoneAdjustment'] : ((new DateTimeZone('Europe/Rome'))->getOffset(new DateTime()) / 3600);
        $req['ExcludeData']        = 'true';
        $req['StartCondition']     = "eventNormalized=" . (isset($req['StartCondition']) ? $req['StartCondition'] : 1000);
        $req['EndCondition']       = "eventNormalized=" . (isset($req['EndCondition']) ? $req['EndCondition'] : 1001);
        $req['DeviceId']           = $this->get_unitcode($idServizio);
        $bsd_data = $this->azure('api/v3/query/getRecordsFromEvents', 'GET', (object)['query' => $req]);

        $service_data = [];
        // Somma tutte le giornate in 1
        foreach ($bsd_data['ReturnData'] ?? [] as $giorno) {
            foreach ($giorno as $key => $data) {
                if ($key !== 'StatisticheGlobali') continue;
                foreach ($data as $stat) {
                    $service_data[$stat['Codice']] = $service_data[$stat['Codice']] ?? ['Valore' => 0];
                    $service_data[$stat['Codice']]['Valore'] += (int) $stat['Valore'];
                }
            }
        }
        // Casting fatto dopo tutte le somme
        foreach ($service_data as $stat => $stat_value) {
            switch ($stat) {
                case 'KM_PERCORSI':
                    $service_data[$stat]['FormattedValue'] = $stat_value['Valore'] . ' Km';
                    break;
                case 'TEMPO_ON':
                    $service_data[$stat]['FormattedValue'] = date('H:i:s', $stat_value['Valore']);
                    break;
                case 'FOLLE':
                    $service_data[$stat]['FormattedValue'] = date('H:i:s', $stat_value['Valore']);
                    break;
                case 'NUMERO_SOSTE':
                    $service_data[$stat]['FormattedValue'] = $stat_value['Valore'];
                    break;
                case 'MOVIMENTO':
                    $service_data[$stat]['FormattedValue'] = date('H:i:s', $stat_value['Valore']);
                    break;
                case 'TEMPO_OFF':
                    $service_data[$stat]['FormattedValue'] = date('H:i:s', $stat_value['Valore']);
                    break;
                case 'VELOCITA_MEDIA':
                    $vel_media = round($stat_value['Valore'] / count($bsd_data['ReturnData'] ?? 1), 0);
                    $service_data[$stat]['FormattedValue'] = $vel_media . ' Km/h';
                    $service_data[$stat]['Valore'] = $vel_media;
                    break;
            }
        }
        return $service_data;
    }

    public function get_usable_user() {
        /**@var TT_UtenteModel */
        $loggedUser = Auth::user();
        switch ($loggedUser->getRoleLevel()) {
            case 1:
            case 2:
            case 3:
                $users = TT_UtenteModel::has('flotte')->get();
                break;
            case 4:
                $users = TT_UtenteModel::has('flotte')->get();
                break;
            default:
                $users = TT_UtenteModel::where('id', $loggedUser->id)->orWhere('idParent', $loggedUser->id)->has('flotte')->get();
                break;
        }
        return $users;
    }

    public function get_flotte_servizi($idUtente) {
        return TT_UtenteModel::find($idUtente)->flotte()->with('servizi')->get();
    }
}
