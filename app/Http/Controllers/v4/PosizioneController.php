<?php

namespace App\Http\Controllers\v4;

use App\Http\Controllers\Controller;
use App\Common\Managers\PosizioniManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PosizioneController extends Controller {

    /** @var Collection */
    private static $autisti = [];
    /** @var Collection */
    private static $batteries = [];
    
    public $fixGps;
    public $latitude;
    public $longitude;
    public $speed;
    public $heading;
    public $satellite;
    public $altitude;
    public $km;
    public $battery;
    public $inputs = [];
    public $outputs = [];
    public $analogs = [];
    public $message;
    public $gprsstatus;
    public $signaltype;
    public $stato;
    public $evento;
    public $eventNormalized;
    public $address;
    public $autista;

    public static function latest($unitcode) {
        return (new self)->cast(PosizioniManager::getLatestUnitcode($unitcode));
    }

    public function __construct() {
        static::loadDeps();
    }

    public static function loadDeps() {
        //! Cosi quando fai new non muore il mondo
        if (empty(static::$autisti))
            static::$autisti = collect(DB::select(
                DB::raw('
            SELECT a.id, a.autista as nome, c.unitcode
            FROM TT_Autista as a
            JOIN TC_AutistaComponente as ac on ac.idAutista = a.id
            JOIN TT_Componente as c on ac.idComponente = c.id
            ')
            ))->groupBy('unitcode')->flatten();

        //! Cosi quando fai new non muore il mondo
        if (empty(static::$batteries))
            static::$batteries = collect(DB::select(
                DB::raw('
            SELECT c.unitcode, m.batteria
            FROM TT_Componente as c
            JOIN TT_Modello as m ON c.idModello = m.id
            ')
            ))->groupBy('unitcode');
    }

    public static function cast_bulk($array) {
        static::loadDeps();

        $posizioni = [];
        foreach ($array ?? [] as $p) {
            $posizioni[] = (new self)->cast($p);
        }

        return $posizioni;
    }

    public function cast($evento) {
        try {
            $evento = (array) $evento;
            foreach ($evento as $key => $val) {
                $asd[] = $key;
                if (property_exists($this, $key) && !in_array($key, ['event', 'address', 'inputsLedLabels', 'inputsLed', 'outputsLedLabels', 'outputsLed', 'inputs', 'outputs', 'battery', 'stato'])) {
                    $this->{$key} = $val;
                }
                if (strpos($key, 'analog') !== false) {
                    $this->analogs[$key] = $val;
                }
            }
            if (isset($evento['rftag_active']) && (!empty($evento['rftag_active']))) {
                $this->autista = $this->cast_autista($evento['rftag_active'], $evento['rftag_battery']);
            }
            if (isset($evento['event']) && !empty($evento['event'])) {
                $this->evento = $evento['event'];
            }

            if (isset($evento['stato'])) {
                $this->stato = $this->cast_stato($evento['stato']);
            }

            if (isset($evento['battery'])) {
                $this->battery = $this->cast_battery($evento['battery'], $evento['PartitionKey']);
            }
            if (isset($evento['address']) && !empty($evento['address'])) {
                $this->address = $this->cast_address($evento['address']);
            }
            if (isset($evento['inputsLedLabels']) && isset($evento['inputsLed'])) {
                $this->inputs = $this->cast_IO($evento['inputsLedLabels'], $evento['inputsLed']);
            }
            if (isset($evento['outputsLedLabels']) && isset($evento['outputsLed'])) {
                $this->outputs = $this->cast_IO($evento['outputsLedLabels'], $evento['outputsLed']);
            }
            return $this;
        } catch (\Throwable $th) {
            exit(json_encode($th->getMessage()));
        }
    }

    protected function cast_stato($stato) {
        switch ($stato) {
            case "1":
            case "10":
            case "01":
            case 1:
                return 1;
            case "2":
            case "20":
            case "02":
            case 2:
                return 2;
            case "0":
            case "00":
            case 0:
                return 0;
            default:
                return "Error: " . $stato;
        }
    }

    public function cast_address($address) {
        try {
            $tmp = json_decode($address, true);
        } catch (\Throwable $th) {
            print json_encode($th);
        } finally {
            if (isset($tmp['A'])) {
                return $tmp['A'][0];
            } else {
                return $tmp;
            }
        }
    }

    public function cast_IO($labels, $values) {
        $IO = [];

        $tmp = (strpos($labels, '~') != -1) ? explode('~', $labels) : null;

        if (count($tmp) >= 1) {
            foreach ($tmp as $key => $val) {
                if (!empty($val)) {
                    $IO[$val] = substr($values, $key, 1);
                }
            }
        }
        return (object) $IO;
    }

    public function cast_battery($volt, $unitcode) {
        /* MLS SEMPRE BATTERIE */
        if (preg_match("/([0-9]{2})(29)([0-9]{6})/", $unitcode)){
            return $this->calc_battery($volt);
        } else if( static::$batteries[$unitcode]->first() && static::$batteries[$unitcode]->first()->batteria === 1 ){
            return $this->calc_battery($volt);
        }        
        return null;
    }

    // Calcolo la percentuale della batteria dai volt
    protected function calc_battery($volt){
        $max = 4.15;
        $min = 3.50;

        $delta = $max - $min;

        $scarto = $volt - $min;

        $percentuale = round(($scarto / $delta) * 100, 0);
        $percentuale = ($percentuale > 100) ? 100 : $percentuale;
        $percentuale = ($percentuale < 0) ? 0 : $percentuale;
        return $percentuale;
    }

    public function cast_autista($rfTagID, $rfTagBattery = null) {
        try {
            $not_valid = ['', 'NA', '0.0'];
            if (!in_array($rfTagID, $not_valid)) {
                $autista = static::$autisti->firstWhere('unitcode', 'ilike', $rfTagID);
                // $autista->unitcode = $rfTagID;
                $autista->battery = $rfTagBattery;
                return $autista;
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
        return null;
    }

    public function __toString() {
        return json_encode($this);
    }
}
