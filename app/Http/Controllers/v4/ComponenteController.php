<?php

namespace App\Http\Controllers\v4;

use App\Http\Controllers\Controller;
use Facades\App\Repositories\iHelpU;
use App\Models\{TC_ServizioComponenteModel, TT_AutistaModel, TT_ComponenteModel, TT_ServizioModel, TT_SimModel, TT_UtenteModel};
use App\Http\Requests\{ComponenteBulkRequest, ComponenteDeleteRequest, ComponenteRequest, RadiocomandoRequest, TachoRequest};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ComponenteController extends Controller {
    private static function make_list($rows) {
        $list = [];
        $modellis = iHelpU::groupBy(DB::select('SELECT id, idBrand, idTipologia, modello, batteria FROM `TT_Modello` WHERE 1'), 'id');
        $brands   = iHelpU::groupBy(DB::select('SELECT id, marca, idFornitore FROM `TT_Brand` WHERE 1'), 'id');
        $modelli = [];
        foreach ($modellis as $mod) {
            $tmp = (object) clone ($mod[0]);
            $tmp->brand = (array_key_exists($tmp->idBrand, $brands)) ? $brands[$tmp->idBrand][0] : null;
            $modelli[$tmp->id] = $tmp;
        }
        $simList = iHelpU::groupBy(DB::select('SELECT id, idModello, serial, apn, dataAttivazione, dataDisattivazione FROM `TT_Sim` WHERE 1'), 'id');

        foreach ($rows as $row) {
            $tmp = (object) clone ($row);
            $tmp->modello = (array_key_exists($tmp->idModello, $modelli)) ? $modelli[$tmp->idModello] : null;
            $tmp->sim = (array_key_exists($tmp->idSim, $simList)) ? $simList[$tmp->idSim][0] : null;
            $list[] = $tmp;
        }

        return $list;
    }

    public static function get_id(int $id) {
        return TT_ComponenteModel::findOrFail($id)->makeHidden('idSim')->load('sim', 'modello', 'note');
    }

    public function get_all(int $idTipologia) {
        $rows = DB::table('TT_Componente')
            ->select('TT_Componente.*')
            ->leftJoin('TT_Modello', 'TT_Componente.idModello', '=', 'TT_Modello.id')
            ->where('idTipologia', $idTipologia)
            ->orderByDesc('updated_at')
            ->get();

        return $this->make_list($rows);
    }

    public function get_all_radiocomando() {
        return $this->get_all(93);
    }

    public function get_all_tacho() {
        return $this->get_all(92);
    }

    public function get_all_gps() {
        return $this->get_all(10);
    }

    public function non_associato(int $idTipologia, int $id = null) {
        $rows = DB::table('TT_Componente')
            ->select('TT_Componente.*')
            ->leftJoin('TT_Modello', 'TT_Componente.idModello', '=', 'TT_Modello.id');

        if (!is_null($id)) {
            $coda = " AND TT_Servizio.id != " . $id;
        } else {
            $coda = '';
        }

        switch ($idTipologia) {
            case 93:
                $rows->whereRaw('TT_Componente.id NOT IN (SELECT idRadiocomando
                    FROM TT_Servizio INNER JOIN TC_ServizioComponente ON TC_ServizioComponente.idServizio = TT_Servizio.id
                    WHERE idRadiocomando IS NOT NULL
                    AND (dataFine >= now() OR dataFine IS NULL) ' . $coda . ' )');
                break;
            case 92:
                $rows->whereRaw('TT_Componente.id NOT IN (SELECT idTacho
                    FROM TT_Servizio INNER JOIN TC_ServizioComponente ON TC_ServizioComponente.idServizio = TT_Servizio.id
                    WHERE idTacho IS NOT NULL
                    AND (dataFine >= now() OR dataFine IS NULL) ' . $coda . ' )');
                break;
            case 10:
                $rows->whereRaw('TT_Componente.id NOT IN (SELECT idComponente
                FROM TT_Servizio INNER JOIN TC_ServizioComponente ON TC_ServizioComponente.idServizio = TT_Servizio.id
                WHERE idComponente IS NOT NULL
                AND (dataFine >= now() OR dataFine IS NULL) ' . $coda . ' )');
                break;
        }
        $rows->where('idTipologia', $idTipologia);
        return $this->make_list($rows->get());
    }

    public function non_associato_radiocomando(int $id = null) {
        return $this->non_associato(93, $id);
    }

    public function non_associato_tacho(int $id = null) {
        return $this->non_associato(92, $id);
    }

    public function non_associato_gps(int $id = null) {
        return $this->non_associato(10, $id);
    }

    private function from_csv_to_models($file) {
        $handle = (fopen($file, "r") !== FALSE) ? fopen($file, "r") : null;
        if (!is_null($handle)) {
            $array_from_csv = [];
            $row = 1;
            $file_titles = fgetcsv($handle, 1000000, ";");
            while (($data = fgetcsv($handle, 1000000, ";")) !== FALSE) {
                $tmp = (object) [];
                foreach ($data as $key => $val) {
                    $val = trim($val);
                    switch ($key) {
                        case 0:
                            $tmp->{$file_titles[$key]} = $val;
                            break;
                        case 1:
                            $tmp->{$file_titles[$key]} = $val;
                            break;
                        case 2:
                            if (!property_exists($tmp, 'sim')) $tmp->sim = (object) [];
                            $tmp->sim->{$file_titles[$key]} = $val;
                            break;
                        case 3:
                            if (property_exists($tmp, 'sim'))
                                $tmp->sim->{$file_titles[$key]} = $val;
                            break;
                    }
                }
                $array_from_csv[] = $tmp;
            }
            return $array_from_csv;
        } else {
            throw new UnprocessableEntityHttpException('File non conforme ');
        }
    }

    public function create_bulk(ComponenteBulkRequest $request) {
        if ($request->hasFile('packet')) {
            $list_insert = $this->from_csv_to_models($request->file('packet'));
        } else {
            $list_insert = $request->validated();
        }
        $inserted = [];
        foreach ($list_insert as $row) {
            $tmp_row = (object) $row;
            $tmp_row->sim = (object) $row['sim'];
            $find = ['unitcode' => $tmp_row->unitcode];
            $data = ['idModello' => $tmp_row->idModello];
            $data = ['idOperatore' => Auth::user()->id];
            $cmp = TT_ComponenteModel::updateOrCreate($find, $data);
            if (isset($tmp_row->sim)) {
                $sim = TT_SimModel::updateOrCreate(['serial' => $tmp_row->sim->serial], ['idModello' => $tmp_row->sim->idModello, 'idOperatore' => Auth::user()->id]);
            }
            if (isset($sim)) {
                $cmp->idSim = $sim->id;
                $cmp->save();
            }
            $inserted[] = $cmp;
        }
        return $inserted;
    }

    // ?in realtà non uso l'id per la modifica, mi serve solo per validare la request
    public function create(ComponenteRequest $request, int $id = null) {
        $reqData = collect($request->validated());
        $componente = $reqData->except(['sim', 'nota'])->toArray();

        // Non serve controllare l'array o count perche è validato come array
        $nota = $reqData['nota'] ?? [];

        $componente['idOperatore'] = Auth::user()->id;

        if (isset($reqData['sim']['id'])) {
            // Assegna sim
            $componente['idSim'] = $reqData['sim']['id'];
        } elseif (isset($reqData['sim']['serial'])) {
            // Crea sim e associa
            $sim = $reqData['sim'];
            $sim['idOperatore'] = Auth::user()->id;

            $new_sim = TT_SimModel::updateOrCreate(['serial' => $sim['serial']], $sim);
            $componente['idSim'] = $new_sim->id;
        } else {
            // Dissocia sim
            $componente['idSim'] = null;
        }

        if (!is_null($id)) {
            $new_componente = TT_ComponenteModel::find($id);
            $new_componente->update($componente);
        } else {
            $new_componente = TT_ComponenteModel::updateOrCreate(['unitcode' => $componente['unitcode']], $componente);
        }

        AnnotazioneController::sync('TT_Componente', $new_componente->id, $nota);

        return $this->get_id($new_componente->id);
    }

    public function create_tacho(TachoRequest $request, int $id = null) {
        $request->validated();
        $componente = $request->except(['sim', 'nota']);

        $nota = (!is_array($request->only('nota')['nota']) || count($request->only('nota')['nota']) < 1) ? [] : $request->only('nota')['nota'];

        $componente['idOperatore'] = Auth::user()->id;

        if (!is_null($request->only('sim')) && isset($request->sim['id'])) {
            $componente['idSim'] = $request->sim['id'];
        } elseif (!is_null($request->only('sim')) && isset($request->sim['serial'])) {
            $sim = $request->only('sim')['sim'];
            $sim['idOperatore'] = Auth::user()->id;

            $new_sim = TT_SimModel::updateOrCreate(['serial' => $sim['serial']], $sim);
            $componente['idSim'] = $new_sim->id;
        } else {
            $componente['idSim'] = null;
        }

        $new_componente = TT_ComponenteModel::updateOrCreate(['unitcode' => $componente['unitcode']], $componente);

        AnnotazioneController::sync('TT_Componente', $new_componente->id, $nota);

        return $this->get_id($new_componente->id);
    }

    public function create_radiocomando(RadiocomandoRequest $request, int $id = null) {
        $request->validated();
        $componente = $request->except(['sim', 'nota']);

        $nota = (!is_array($request->only('nota')['nota']) || count($request->only('nota')['nota']) < 1) ? [] : $request->only('nota')['nota'];

        $componente['idOperatore'] = Auth::user()->id;

        if (!is_null($request->only('sim')) && isset($request->sim['id'])) {
            $componente['idSim'] = $request->sim['id'];
        } elseif (!is_null($request->only('sim')) && isset($request->sim['serial'])) {
            $sim = $request->only('sim')['sim'];
            $sim['idOperatore'] = Auth::user()->id;

            $new_sim = TT_SimModel::updateOrCreate(['serial' => $sim['serial']], $sim);
            $componente['idSim'] = $new_sim->id;
        } else {
            $componente['idSim'] = null;
        }

        $new_componente = TT_ComponenteModel::updateOrCreate(['unitcode' => $componente['unitcode']], $componente);

        AnnotazioneController::sync('TT_Componente', $new_componente->id, $nota);

        return $this->get_id($new_componente->id);
    }

    public function delete(ComponenteDeleteRequest $request, int $id) {
        $request->validated();

        $componente = TT_ComponenteModel::findOrFail($id)->countServizi();

        if ($componente->countServizi > 0 && !$request->force) {
            throw new UnprocessableEntityHttpException("Questo componente è associato ad un servizio. Per eliminarlo setta 'force': true ");
        } else {
            if ($componente->countServizi >= 1) {
                foreach ($componente->servizi()->servizi as $serv) {
                    $serv = (object) $serv;
                    $servizio = (object) (new ServizioController)->get_id($serv->id);

                    foreach ($servizio->componente ?? [] as $gps) {
                        $sc = TC_ServizioComponenteModel::find($gps->idServizioComponente);
                        $sc->delete();
                    }

                    foreach ($servizio->tacho ?? [] as $gps) {
                        $sc = TC_ServizioComponenteModel::find($gps->idServizioComponente);
                        $sc->delete();
                    }

                    foreach ($servizio->radiocomando ?? [] as $gps) {
                        $sc = TC_ServizioComponenteModel::find($gps->idServizioComponente);
                        $sc->delete();
                    }
                }
            }
            return $componente->delete();
        }
    }

    public static function show_battery($unitcode) {
        if (preg_match("/([0-9]{2})(29)([0-9]{6})/", $unitcode)) { // ? MLS SEMPRE BATTERIE
            return true;
        }

        $modello = TT_ComponenteModel::where('unitcode', $unitcode)->first()->modello;
        if ($modello->batteria == true) {
            return true;
        }
        return false;
    }

    public function get_radiocomandi_per_anagrafica(int $id = null) {
        /**@var TT_UtenteModel */
        $user = Auth::user();
        if ($user->getRoleLevel() <= 4) {
            $idAnagrafica = TT_UtenteModel::findOrFail($id)->idAnagrafica;
        } else {
            $idAnagrafica = $user->idAnagrafica;
        }
        $servizi_radiocomandi = TT_ServizioModel::has('radiocomandi')->where('idAnagrafica', $idAnagrafica)->with('radiocomandi.autisti')->get();

        return $servizi_radiocomandi->pluck('radiocomandi')->flatten(); // come le tette di matteo
    }

    public function associa_autista(Request $request, int $idRadiocomando) {
        $request->validate([
            'idAutista' => 'required_without:autista|exists:TT_Autista,id',
            'idUtente' => 'required|exists:TT_Utente,id',
            'autista' => 'required_without:idAutista|string'
        ]);

        $request['idAnagrafica'] = TT_UtenteModel::findOrFail($request['idUtente'])->idAnagrafica;

        unset($request['idUtente']);

        if (isset($request['idAutista'])) {
            $autista = TT_AutistaModel::findOrFail($request['idAutista']);
        } else {
            $autista = TT_AutistaModel::firstOrNew(['autista' => $request['autista'], 'idAnagrafica' => $request['idAnagrafica']]);
            $autista->idOperatore = Auth::id();
            if (isset($request['idAnagrafica'])) {
                $autista->anagrafica()->associate($request['idAnagrafica']);
            } else {
                $autista->anagrafica()->dissociate($request['idAnagrafica']);
            }

            $autista->save();
        }

        $componente = TT_ComponenteModel::findOrFail($idRadiocomando)->autisti()->sync([$autista->id => [
            'idOperatore' => Auth::id(),
        ]]);

        return $componente;
    }
}
