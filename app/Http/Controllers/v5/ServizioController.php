<?php

namespace App\Http\Controllers\v5;

use App\Http\Controllers\Controller;
use App\Http\Resources\v5\Servizio\ServizioResource;
use App\Models\v5\Modello;
use App\Models\v5\Servizio;
use App\Models\v5\Tipologia;
use Illuminate\Http\Request;

class ServizioController extends Controller
{

    const WHAT_TO_LOAD = [
        'periodo',
        'causale',
        'applicativi',
        'cliente.parent',
        'installatori',
        'mezzo.modello.brand',
        'gps.modello.brand',
        'tacho.modello.brand',
        'radiocomandi.modello.brand',
    ];

    public function index(Request $request)
    {
        // return Tipologia::where('idParent', 50)->get()->pluck('descrizione')->map( fn ($data) => dd(json_decode($data)));
        // $serv = [];
        // $serv['totale'] = 0;
        // foreach( Tipologia::where('idParent', 50)->get() as $fatt ){
        //     $count = Servizio::attivi()->fatturabili()->periodicitaFatturazione($fatt)->count();
        //     $serv[$fatt->nome] = $count;
        //     $serv['totale']+= $count;
        // }
        // return $serv;
        $listaServizi = Servizio::with(self::WHAT_TO_LOAD)->attivi()->accessibili()->orderBy('updated_at', 'DESC')->orderBy('id', 'DESC');
        // ->orderBy('updated_at', 'DESC');

        $listaServizi = $listaServizi->paginate($request->input('per_page') ?? (new Servizio)->perPage);

        $resourceClass = $request->attributes->get('ServizioResource') ?? ServizioResource::class;
        return $resourceClass::collection($listaServizi);
    }

    public function show(Request $request, Servizio $servizio)
    {
        $resourceClass = $request->attributes->get('ServizioResource') ?? ServizioResource::class;
        return $resourceClass::make($servizio->load(self::WHAT_TO_LOAD));
    }

    public function update(Request $request, Servizio $servizio)
    {
        return Modello::whereIn('idTipologia', Tipologia::where('idParent', 64)->get())->get()->random();
        return $servizio->applicativi()->syncWithPivotValues(Tipologia::where('idParent', 83)->get()->random()->id, ['idOperatore' => 1]);
    }
}
