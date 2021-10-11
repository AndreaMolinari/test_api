<?php

namespace App\Repositories;

use Facades\App\Http\Controllers\crud\TT_BrandController;
use Facades\App\Http\Controllers\crud\TT_ModelloController;
use Facades\App\Http\Controllers\crud\TT_SimController;
use Facades\App\Http\Controllers\crud\TT_TipologiaController;
use Facades\App\Http\Controllers\crud\TT_ComponenteController;
use Carbon\Carbon;
use Facades\App\Repositories\iHelpU;

class Componente
{
    CONST CACHE_KEY="COMPONENTE";
    CONST CACHE_TIME=1;

    function getAll()
    {
        // cache()->forget($this->getCacheKey("ALL"));
        return cache()->remember($this->getCacheKey("ALL"), Carbon::now()->addMinutes(self::CACHE_TIME), function(){
            $tmp = TT_ComponenteController::getAll();

            $sim = iHelpU::listBy(TT_SimController::getAll());

            $tipologia = iHelpU::listBy(TT_TipologiaController::getAll());

            $brand = iHelpU::listBy(TT_BrandController::getAll());
            $modello = iHelpU::listBy(TT_ModelloController::getAll());

            $result = [];

            foreach ($tmp as $singolo) {
                $idSim = $singolo->idSim;
                $idModello = $singolo->idModello;

                $singolo->modello = null;
                $singolo->sim = null;

                if (property_exists($modello, $idModello)) {
                    $singolo->modello = $modello->$idModello;
                    $idBrand = $singolo->modello->idBrand;
                    $idTipologia = $singolo->modello->idTipologia;
                    $singolo->modello->brand = (property_exists($brand, $idBrand) ? $brand->$idBrand : null);
                    $singolo->modello->tipologia = (property_exists($tipologia, $idTipologia) ? $tipologia->$idTipologia : null);
                }

                if (property_exists($sim, $idSim)) {
                    $tmp = $sim->$idSim;

                    $idModello = $tmp->idModello;

                    $singolo->sim = (property_exists($sim, $idSim) ? $sim->$idSim : null);

                    $singolo->sim->modello = (property_exists($modello, $idModello) ? $modello->$idModello : null);

                    if (!empty($singolo->sim->modello)) {
                        $idBrand = $singolo->sim->modello->idBrand;
                        $idTipologia = $singolo->sim->modello->idTipologia;
                        $singolo->sim->modello->brand = (property_exists($brand, $idBrand) ? $brand->$idBrand : null);
                        $singolo->sim->modello->tipologia = (property_exists($tipologia, $idTipologia) ? $tipologia->$idTipologia : null);
                    }
                }

                $result[] = $singolo;
            }
            return $result;
        });
    }

    function tracciante()
    {
        return cache()->remember($this->getCacheKey("ALL.UNITCODES"), Carbon::now()->addMinutes(self::CACHE_TIME), function(){
            $lista = [];
            foreach(self::getAll() as $componente)
            {
                try {
                    //TODO per Andrea, qui ci ho messo una toppa, funziona ma penso sia un rammendo non troppo "elegante"
                    //se fai un exit (esce perciò al primo giro) funziona, ma se lanci la chiamata si rompe.
                    //credo che il problema quindi sia che ALMENO UNO dei valori, a volte non ritorni come oggetto.
                    //nelle righe seguenti converto tutto in oggetto (è sotto try perché alcuni componenti non presentano i campi,
                    //ad esempio è arrivato un componente senza tipologia. Nel caso, continuo il ciclo.)

                    $objComp = (object) $componente;
                    $objModel = (object) $objComp->modello;
                    $objTipo = $objModel->tipologia;
                } catch (\Throwable $th) {
                    continue;
                }

                if($objTipo->id == 10)
                {
                    $lista[] = $componente;
                }
            }
            return $lista;
        });
    }

    function getCacheKey($key)
    {
        $key = strtoupper($key);

        return self::CACHE_KEY .".$key";
    }
}
