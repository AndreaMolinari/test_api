<?php

namespace App\Http\Controllers\v4;

use App\Http\Controllers\Controller;
use App\Http\Requests\AutistaRequest;
use App\Http\Requests\IndexRequest;
use App\Models\TC_ServizioComponenteModel;
use App\Models\TT_AutistaModel;
use App\Models\TT_ComponenteModel;
use App\Models\TT_ServizioModel;
use App\Models\TT_UtenteModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class AutistaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(IndexRequest $request)
    {
        return self::index_helper($request, TT_AutistaModel::class, TT_AutistaModel::with([
            'anagrafica:TT_Anagrafica.id,nome,cognome,ragSoc',
            'componenti:TT_Componente.id,unitcode,imei',
        ]));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\AutistaRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AutistaRequest $request)
    {
        return (self::insert_helper($request->validated()));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Autista  $autista
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {
        $autista = TT_AutistaModel::findOrFail($id);
        return ($autista->load([
            'anagrafica:TT_Anagrafica.id,nome,cognome,ragSoc',
            'componenti:TT_Componente.id,unitcode,imei',
        ]));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\AutistaRequest  $request
     * @param  \App\Models\Autista  $autista
     * @return \Illuminate\Http\Response
     */
    public function update(AutistaRequest $request, int $id)
    {
        $autista = TT_AutistaModel::findOrFail($id);
        return (self::update_helper($request->validated(), $autista));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Autista  $autista
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        $autista = TT_AutistaModel::findOrFail($id);
        $autista->delete();
        return ($autista);
    }

    /**
     * Display a listing of the trashed resource.
     *
     * @param  \Illuminate\Http\IndexRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function index_trash(IndexRequest $request)
    {
        return self::index_helper($request, TT_AutistaModel::class, TT_AutistaModel::onlyTrashed());
    }

    /**
     * Restore a resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function restore_trash(int $id)
    {
        $autista = TT_AutistaModel::findOrFail($id);
        $autista->restore();
        return $autista;
    }

    /**
     * Permanently remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy_trash(int $id)
    {
        $autista = TT_AutistaModel::findOrFail($id);
        $autista->forceDelete();
        return $autista;
    }

    public static function index_helper(IndexRequest $request, $model, Builder $query = null)
    {
        if (is_null($query)) $query = $model::query();

        $index_params = $request->validated();

        // Is searching
        if (isset($index_params['operator'])) {
            $query_string = $request->except(array_keys($request->rules()));
            if (count($query_string) > 0) {
                $inject_operator = [];
                foreach ($query_string as $key => $value) {
                    $inject_operator[] = [$key, $index_params['operator'], $value];
                }

                $query = $query->where($inject_operator);
            }
        }

        // Is ordering
        if (isset($index_params['order_field']) && isset($index_params['order_direction'])) {
            $query = $query->orderBy($index_params['order_field'], $index_params['order_direction']);
        }

        // Is paginating
        if (isset($index_params['per_page'])) {
            return $query->paginate($index_params['per_page'] ?? $model::make()->getPerPage());
        }

        return $query->get();
    }

    public static function insert_helper(array $model_resource): TT_AutistaModel
    {
        $autista             = TT_AutistaModel::make($model_resource);
        try {
            if (!$autista->id)
                $autista->idOperatore = Auth::id();

            $autista->save();

            return $autista;
        } catch (\Exception $ex) {
            $autista->forceDelete();
            throw new UnprocessableEntityHttpException($ex->getMessage(), $ex);
        }
    }

    public static function update_helper(array $model_resource, TT_AutistaModel $autista): TT_AutistaModel
    {
        $original = TT_AutistaModel::find($autista->id);   // Prendo l'istanza dal db senza le modifiche applicate
        $autista->fill($model_resource);            // applico le modifiche all'istanza di cui ho modificato le rel.
        if ($original != $autista)                   // e la comparo con quella inserita
            $autista->update($model_resource);
        return $autista;
    }

    /**
     * @deprecated
     */
    private static function manage_componenti(TT_AutistaModel $autista, array $componenti)
    {
        $comp_ids = [];
        foreach ($componenti as $componente) {
            if (isset($componente['id']) && $found = TT_ComponenteModel::find($componente['id']))
                $comp_ids[$found->id] = [
                    'idOperatore'      => Auth::id(),
                ];
        }
        $autista->componenti()->sync($comp_ids);
    }
    private function prepare_data_from_json()
    {
        $radiocomandi = [];

        $finti = ['Autista', 'Da associare'];

        if (is_file(base_path('autisti.json'))) {
            $autisti = file_get_contents(base_path('autisti.json'));
            $autisti = json_decode($autisti);
            foreach ($autisti as $idAnagrafica => $autisti_anag) {
                foreach( $autisti_anag as $au )
                {
                    $tmp = (object)['autista' => ''];
                    $ins = true;
                    if (preg_match('((055D)([a-zA-Z0-9]){4})', strtoupper($au->unitcode)) === 1) {
                        $tmp->unticode = strtoupper(trim($au->unitcode));
                        $tmp->autista = (object)['autista' => null];
                        if (preg_match('([a-zA-Z0-9])', ucfirst(strtolower(trim($au->autista)))) === 1) {
                            $tmp->autista->autista = ucfirst(strtolower(trim($au->autista)));
                        }else{
                            $ins = false;
                        }
                        if (in_array($tmp->autista->autista, $finti)) {
                            $tmp->autista = null;
                            $ins = false;
                        }
                        if ( !array_key_exists($idAnagrafica, $radiocomandi) ){
                            $radiocomandi[$idAnagrafica] = [];
                        }
                        if( $ins )
                        {
                            $radiocomandi[$idAnagrafica][] = $tmp;
                        }
                    }
                }
            }
            return $radiocomandi;
        }
    }

    public function get_autista_from_anagrafica(int $idUser = null)
    {
        /**@var TT_UtenteModel */
        $user = Auth::user();
        if ($user->getRoleLevel() <= 4) {
            $idAnagrafica = TT_UtenteModel::findOrFail($idUser)->idAnagrafica;
        } else {
            $idAnagrafica = $user->idAnagrafica;
        }
        return TT_AutistaModel::where('idAnagrafica', $idAnagrafica)->with('radiocomandi')->get();
    }

    private function make_servizio($idAnagrafica, $radiocomandi)
    {
        $servizio               = TT_ServizioModel::make();
        $servizio->idAnagrafica = $idAnagrafica;
        $servizio->dataInizio   = (new Carbon())->isoFormat('YYYY-MM-DD');
        $servizio->prezzo       = 0;
        $servizio->idPeriodo    = 69;
        $servizio->idCausale    = 33;
        $servizio->idOperatore  = 1;
        $servizio->save();

        foreach( $radiocomandi as $rad )
        {
            $sc = TC_ServizioComponenteModel::make();
            $sc->idServizio = $servizio->id;
            $sc->idRadiocomando = $rad;
            $sc->idOperatore  = 1;
            $sc->save();
        }

        return true;
    }

    public function import_json()
    {
        $servizi = [];
        foreach ($this->prepare_data_from_json() as $idAnagrafica => $radiocomandi) {
            foreach( $radiocomandi as $uno )
            {
                $radiocomando = TT_ComponenteModel::updateOrCreate(['unitcode' => $uno->unticode], ['idModello' => 1242, 'idOperatore' => 1]);
                if (isset($radiocomando->id) && !is_null($uno->autista)) {
                    $autista = TT_AutistaModel::firstOrNew(['autista' => $uno->autista->autista], ['idOperatore' => 1]);
                    $autista->idOperatore = Auth::id();
                    $autista->save();
                    $this->manage_componenti($autista, [$radiocomando]);
                }
                if ( !array_key_exists($idAnagrafica, $servizi) ){
                    $servizi[$idAnagrafica] = [];
                }
                $servizi[$idAnagrafica][] = $radiocomando->id;
            }
        }


        foreach( $servizi as $idAnagrafica => $radiocomandi_list )
        {
            $this->make_servizio($idAnagrafica, $radiocomandi_list);
        }
    }
}
