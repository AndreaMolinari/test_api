<?php
namespace App\Console\Commands;

use App\Http\Controllers\Trax\ProxyBSDController;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Facades\App\Repositories\iHelpU;
use Facades\App\Http\Controllers\v4\MailController;
use Illuminate\Console\Command;

use App\Http\Controllers\v4\FlottaController;

class reportMensile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:mensile';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera report mensile per chi lo chiede';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        foreach( $this->sendReports() as $key => $value )
        {
            MailController::sendReportMensile($this->getReportMensilePerUtente($key), $value);
        }

        return true;
    }

    function sendReports() //qui inserisco la lista degli utenti report
    {
        //? idUtente => mail
        return [4 => 'devh.testmail@gmail.com'];
    }

    function getReportMensilePerUtente($userID)
    {
        $myReq = iHelpU::mkRequest([], 1);
        // ? Ricorda che sono da sistemare from e to date
        if (!isset($myReq->FromDate)) {
            $FromDate = date("Y-m-01 00:00:00");
            $FromDate = (new Carbon(date("Y-m-01 00:00:00")))->subMonth()->format('Y-m-d H:i:s');
        } else {
            $FromDate = $myReq->FromDate;
        }

        if (!isset($myReq->toDate)) {
            $toDate = date("Y-m-01 00:00:00");
            // $toDate = $toDate;
        } else {
            $toDate = $myReq->toDate;
        }
        $Fields = [];
        if (isset($myReq->fields)) {
            $Fields = $myReq->fields;
        }

        $TimeZoneAdjustment = (new CarbonTimeZone('Europe/Rome'))->toOffsetName();
        if (isset($myReq->TimeZoneAdjustment)) {
            $TimeZoneAdjustment = $myReq->TimeZoneAdjustment;
        }

        $TimeZoneAdjustment = (explode(":", $TimeZoneAdjustment))[0];


        $StartCondition = "eventNormalized=1000";
        if (isset($myReq->StartCondition)) {
            $StartCondition = 'eventNormalized=' . $myReq->StartCondition;
        }

        $EndCondition = "eventNormalized=1001";
        if (isset($myReq->EndCondition)) {
            $EndCondition = 'eventNormalized=' . $myReq->EndCondition;
        }

        // $StartCondition = 'event=100';
        // $EndCondition = 'event=101';

        $body = (object) [
            'type' => 'query',
            'data' => [
                'FromDate' => $FromDate,
                'ToDate' => $toDate,
                'DeviceId' => null,
                // 'Fields' => $Fields, //"latitude, longitude, fixGps,event ,message, eventNormalized, rftag_active, address",
                'TimeZoneAdjustment' => $TimeZoneAdjustment,
                'StartCondition' => $StartCondition,
                'EndCondition' => $EndCondition
                ]
            ];

        $resp = [];
        foreach ((new FlottaController())->getFlottaPerUtente($userID) as $flotta) {
            foreach ($flotta->servizi as $servizio) {
                if (isset($servizio->unitcode)) {
                    $body->data['DeviceId'] = $servizio->unitcode;
                    $tmp = (object) (new ProxyBSDController($myReq, 'GET', 'v3/query/getRecordsFromEvents', $body))->call();

                    $tmpResp = (object) [
                        'idServizio' => $servizio->idServizio,
                        'flotta'    => $flotta->nome,
                        'nickname'  => $servizio->nickname,
                        'unitcode'  => $servizio->unitcode,
                        'targa'     => (isset($servizio->targa)) ? $servizio->targa : ((isset($servizio->telaio)) ? $servizio->telaio : null),
                        'brand'     => (isset($servizio->marca)) ? $servizio->marca : null,
                        'modello'   => (isset($servizio->modello)) ? $servizio->modello : null,
                        'ore_attivita'  => null,
                        'ore_inattivita' => null,
                        'ore_movimento' => null,
                        'ore_fermate'   => null,
                        'distanza'      => null,
                        'soste'         => null
                    ];

                    if (isset($tmp->ReturnData) && is_array($tmp->ReturnData) && count($tmp->ReturnData) >= 1) {
                        $KM_TOT = [];
                        $TEMPO_ON_TOT = [];
                        $FOLLE = [];
                        $NUMERO_SOSTE = [];
                        $MOVIMENTO = [];
                        $TEMPO_OFF = [];
                        $VELOCITA_MEDIA = [];

                        foreach ($tmp->ReturnData as $giorno) {
                            foreach ($giorno['StatisticheGlobali'] as $stat) {
                                switch ($stat['Codice']) {
                                    case "KM_PERCORSI":
                                        $KM_TOT[] = (int) $stat['Valore'];
                                        break;
                                    case "TEMPO_ON":
                                        $TEMPO_ON_TOT[] = (int) $stat['Valore'];
                                        break;
                                    case "FOLLE":
                                        $FOLLE[] = (int) $stat['Valore'];
                                        break;
                                    case "NUMERO_SOSTE":
                                        $NUMERO_SOSTE[] = (int) $stat['Valore'];
                                        break;
                                    case "MOVIMENTO":
                                        $MOVIMENTO[] = (int) $stat['Valore'];
                                        break;
                                    case "TEMPO_OFF":
                                        $TEMPO_OFF[] = (int) $stat['Valore'];
                                        break;
                                    case "VELOCITA_MEDIA":
                                        $VELOCITA_MEDIA[] = (int) $stat['Valore'];
                                        break;
                                    default:
                                        sleep(0);
                                        break;
                                }
                            }
                        }

                        $tmpResp->distanza          = round(array_sum($KM_TOT), 2);
                        $tmpResp->ore_attivita      = date('H:i:s', array_sum($TEMPO_ON_TOT));
                        $tmpResp->ore_fermate       = date('H:i:s', array_sum($FOLLE));
                        $tmpResp->soste             = array_sum($NUMERO_SOSTE);
                        $tmpResp->ore_movimento     = date('H:i:s', array_sum($MOVIMENTO));
                        $tmpResp->ore_inattivita    = date('H:i:s', array_sum($TEMPO_OFF));
                    }
                    $resp[] = $tmpResp;
                }
            }
        }
        return $resp;
    }
}


/*

    function getReportMensilePerUtente($userID)
    {
        // $userID = 4;

        $myReq = $this->castRequest( $this->mkRequest([]) );

        if (!isset($myReq->FromDate)) {
            $FromDate = date("Y-m-01 00:00:00");
            $FromDate = (new Carbon(date("Y-m-01 00:00:00")))->subMonth()->format('Y-m-d H:i:s');
        } else {
            $FromDate = $myReq->FromDate;
        }

        if (!isset($myReq->toDate)) {
            $toDate = date("Y-m-01 00:00:00");
            // $toDate = $toDate;
        } else {
            $toDate = $myReq->toDate;
        }
        $Fields = [];
        if (isset($myReq->fields)) {
            $Fields = $myReq->fields;
        }

        $TimeZoneAdjustment = (new CarbonTimeZone('Europe/Rome'))->toOffsetName();
        if (isset($myReq->TimeZoneAdjustment)) {
            $TimeZoneAdjustment = $myReq->TimeZoneAdjustment;
        }
        $TimeZoneAdjustment = (explode(":", $TimeZoneAdjustment))[0]; // ? explode su : in modo da dividere ore e minuti... prendo in considerazione solo le ore! [0]

        $StartCondition = "eventNormalized=1000";
        if (isset($myReq->StartCondition)) {
            $StartCondition = 'eventNormalized=' . $myReq->StartCondition;
        }

        $EndCondition = "eventNormalized=1001";
        if (isset($myReq->EndCondition)) {
            $EndCondition = 'eventNormalized=' . $myReq->EndCondition;
        }

        // $StartCondition = 'event=100';
        // $EndCondition = 'event=101';

        $body = (object) [
            'type' => 'query',
            'data' => [
                'FromDate' => $FromDate,
                'ToDate' => $toDate,
                'DeviceId' => null,
                // 'Fields' => $Fields, //"latitude, longitude, fixGps,event ,message, eventNormalized, rftag_active, address",
                'TimeZoneAdjustment' => $TimeZoneAdjustment,
                'StartCondition' => $StartCondition,
                'EndCondition' => $EndCondition
            ]
        ];

        $resp = [];

        foreach (testFlotta::getFlottaPerUtente($userID) as $flotta) {
            foreach ($flotta->servizi as $servizio) {
                if (isset($servizio->unitcode)) {
                    $body->data['DeviceId'] = $servizio->unitcode;
                    $tmp = (object) (new ProxyBSDController($this->mkRequest((array) $myReq), 'GET', 'v3/query/getRecordsFromEvents', $body))->call();

                    $tmpResp = (object) [
                        'idServizio' => $servizio->idServizio,
                        'flotta'    => $flotta->nome,
                        'nickname'  => $servizio->nickname,
                        'unitcode'  => $servizio->unitcode,
                        'targa'     => (isset($servizio->targa)) ? $servizio->targa : ((isset($servizio->telaio)) ? $servizio->telaio : null),
                        'brand'     => (isset($servizio->marca)) ? $servizio->marca : null,
                        'modello'   => (isset($servizio->modello)) ? $servizio->modello : null,
                        'ore_attivita'  => null,
                        'ore_inattivita' => null,
                        'ore_movimento' => null,
                        'ore_fermate'   => null,
                        'distanza'      => null,
                        'soste'         => null
                    ];

                    if (isset($tmp->ReturnData) && is_array($tmp->ReturnData) && count($tmp->ReturnData) >= 1) {
                        // exit( json_encode($tmp->ReturnData) );
                        $KM_TOT = [];
                        $TEMPO_ON_TOT = [];
                        $FOLLE = [];
                        $NUMERO_SOSTE = [];
                        $MOVIMENTO = [];
                        $TEMPO_OFF = [];
                        $VELOCITA_MEDIA = [];

                        foreach ($tmp->ReturnData as $giorno) {
                            foreach ($giorno['StatisticheGlobali'] as $stat) {
                                switch ($stat['Codice']) {
                                    case "KM_PERCORSI":
                                        $KM_TOT[] = (int) $stat['Valore'];
                                        break;
                                    case "TEMPO_ON":
                                        $TEMPO_ON_TOT[] = (int) $stat['Valore'];
                                        break;
                                    case "FOLLE":
                                        $FOLLE[] = (int) $stat['Valore'];
                                        break;
                                    case "NUMERO_SOSTE":
                                        $NUMERO_SOSTE[] = (int) $stat['Valore'];
                                        break;
                                    case "MOVIMENTO":
                                        $MOVIMENTO[] = (int) $stat['Valore'];
                                        break;
                                    case "TEMPO_OFF":
                                        $TEMPO_OFF[] = (int) $stat['Valore'];
                                        break;
                                    case "VELOCITA_MEDIA":
                                        $VELOCITA_MEDIA[] = (int) $stat['Valore'];
                                        break;
                                    default:
                                        sleep(0);
                                        break;
                                }
                            }
                        }

                        $tmpResp->distanza          = round(array_sum($KM_TOT), 2);
                        $tmpResp->ore_attivita      = date('H:i:s', array_sum($TEMPO_ON_TOT));
                        $tmpResp->ore_fermate       = date('H:i:s', array_sum($FOLLE));
                        $tmpResp->soste             = array_sum($NUMERO_SOSTE);
                        $tmpResp->ore_movimento     = date('H:i:s', array_sum($MOVIMENTO));
                        $tmpResp->ore_inattivita    = date('H:i:s', array_sum($TEMPO_OFF));
                    }
                    return $tmpResp; //! ATTENZIONE QUA, HO MESSO UN MEZZO SOLO PER TEST!!!!!!!!!!!!!!!!!!!!
                    $resp[] = $tmpResp;
                }
            }
        }

        return $resp;
    }

*/
