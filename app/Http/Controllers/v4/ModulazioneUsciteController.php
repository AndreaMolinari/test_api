<?php

namespace App\Http\Controllers\v4;

use App\Http\Controllers\Controller;
use App\Http\Proxies\JrinMLSProxy;
use App\Http\Proxies\JrinRecordProxy;
use App\Http\Proxies\TeltonikaProxy;
use App\Http\Requests\Trax\InOutRequest;
use App\Models\{TT_ServizioModel};
use Illuminate\Http\Request;

class ModulazioneUsciteController extends Controller
{
    public static function dispatchWithProxy(int $idServizio, string $metodo, ...$params)
    {
        $gps = TT_ServizioModel::findOrFail($idServizio)->get_principale()->with('modello')->first();

        switch ($gps) {
            case ($gps->modello->brand->id == 4 && preg_match('/^\d{2}29\d{6}$/', $gps->unitcode)): // MLS Torino Xtrax
                $proxy = JrinMLSProxy::class;
                break;
            case ($gps->modello->brand->id == 4 && !preg_match('/^\d{2}29\d{6}$/', $gps->unitcode)): // Se diverso da 29 -> Record Torino Xtrax
                $proxy = JrinRecordProxy::class;
                break;
            case ($gps->modello->brand->id == 2):
                $proxy = TeltonikaProxy::class;
                break;
            default:
                throw new \Exception('Non so a chi tu sia connesso');
                break;
        }
        return $proxy::{$metodo}($gps->unitcode, ...$params);
    }

    public function setStatus(InOutRequest $request, int $idServizio)
    {
        return static::dispatchWithProxy($idServizio, 'setStatus', $request->validated());
    }

    public function getStatus(int $idServizio)
    {
        return static::dispatchWithProxy($idServizio, 'getStatus');
    }

    public function allineaKM(Request $request, int $idServizio)
    {
        $data = $request->validate([
            'km' => 'required|numeric|max:9999999'
        ]);

        return static::dispatchWithProxy($idServizio, 'allineaKM', $data['km']);
    }

    public function allineaMezzo(Request $request, int $idServizio)
    {
        return static::dispatchWithProxy($idServizio, 'allineaMezzo', $request->all());
    }

    public function richiediPosizione(int $idServizio)
    {
        return static::dispatchWithProxy($idServizio, 'richiediPosizione');
    }

    public function isConnected(int $idServizio)
    {
        return static::dispatchWithProxy($idServizio, 'isConnected');
    }
}
