<?php

namespace App\Http\Controllers\v4;

use App\Http\Controllers\Controller;
use Facades\App\Repositories\iHelpU;
use App\Models\{TC_FlottaServizioModel, TC_UtenteFlottaModel, TT_FlottaModel, TT_ServizioModel, TT_UtenteModel};
use App\Http\Requests\FlottaRequest;
use App\Http\Requests\Trax\CustomFlottaRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class FlottaController extends Controller {
    protected function make_lists($raw_data) {
        if (is_null($raw_data)) {
            return [];
        }

        $list = [];

        $utenti = iHelpU::groupBy(DB::table((new TC_UtenteFlottaModel)->table)
            ->select((new TC_UtenteFlottaModel)->table . '.idRiferimento', 'username', 'idUtente')
            ->leftJoin((new TT_UtenteModel)->table, (new TC_UtenteFlottaModel)->table . '.idUtente', (new TT_UtenteModel)->table . '.id')
            ->get(), 'idRiferimento');

        $servizi = iHelpU::groupBy(DB::table((new TC_FlottaServizioModel)->table)
            ->select('idFlotta', 'idServizio')
            ->leftJoin((new TT_ServizioModel)->table, (new TC_FlottaServizioModel)->table . '.idServizio', (new TT_ServizioModel)->table . '.id')
            ->where('dataInizio', '<=', now())
            ->whereRaw("(`dataFine` IS NULL OR `dataFine` >= now())")
            ->get(), 'idFlotta');

        foreach ($raw_data as $raw) {
            $tmp = clone ($raw);
            $tmp->nservizio = (isset($servizi[$raw->id])) ? count($servizi[$raw->id]) : 0;
            $tmp->utente = (isset($utenti[$raw->id])) ? $utenti[$raw->id] : [];

            $list[] = $tmp;
        }

        $lista_rivenditore = [];
        /**@var TT_UtenteModel */
        $user = Auth::user();
        if ($user->getRoleLevel() === 4) {
            $allowed_user = array_keys(iHelpU::groupBy(DB::table('TT_Utente')
                ->select('TT_Utente.id')
                ->leftJoin('TC_AnagraficaAnagrafica', 'idChild', '=', 'idAnagrafica')
                ->where('TC_AnagraficaAnagrafica.idParent', $user->idAnagrafica)
                ->get(), 'id'));

            foreach ($list as $uno) {
                $per_rivenditore = false;
                foreach ($uno->utente as $u) {
                    if (in_array($u->idUtente, $allowed_user))  //Forte
                    {
                        $per_rivenditore = true;
                    }
                }
                if ($per_rivenditore) {
                    $lista_rivenditore[] = $uno;
                }
            }
            $list = $lista_rivenditore;
        }

        return array_reverse($list);
    }

    public function get_id(int $id) {
        $flotta_completa = (object) clone (TT_FlottaModel::findOrFail($id)->load('utenti', 'servizi'));
        $servizi = [];
        $utenti = [];
        if (count($flotta_completa->utenti) >= 0) {
            foreach ($flotta_completa->utenti as $utente) {
                $utente = $utente;
                $utente->username = TT_UtenteModel::findOrFail($utente->idUtente)->username;
                $utenti[] = $utente;
            }
        }
        if (count($flotta_completa->servizi) >= 0) {
            foreach ($flotta_completa->servizi as $servizio) {
                $gps = null;
                foreach ($servizio->gps as $k => $g) {
                    if ($k == 0 || $g->servizioComponente->principale) {
                        $gps = $g;
                    }
                }
                $tmp = (object)[
                    'idServizio' => $servizio->id,
                    'dataInizio' => $servizio->dataInizio,
                    'dataFine' => $servizio->dataFine,
                    'nickname' => $servizio->pivot->nickname,
                    'icona' => $servizio->pivot->icona,
                    'unitcode' => (!is_null($gps->unitcode)) ? $gps->unitcode : null,
                    'targa' => (isset($servizio->mezzo[0]->targa)) ? $servizio->mezzo[0]->targa : null,
                    'telaio' => (isset($servizio->mezzo[0]->telaio)) ? $servizio->mezzo[0]->telaio : null,
                    'brand' => (isset($servizio->mezzo[0]->modello->brand->marca)) ? $servizio->mezzo[0]->modello->brand->marca : null,
                    'modello' => (isset($servizio->mezzo[0]->modello->modello)) ? $servizio->mezzo[0]->modello->modello : null,
                ];
                $servizi[] = $tmp;
            }
        }
        $flotta_completa->utente = $utenti;
        $flotta_completa->servizio = $servizi;
        unset($flotta_completa->servizi, $flotta_completa->utenti);
        return [$flotta_completa];
    }

    function get_all() {
        $raw_data = TT_FlottaModel::all();

        return $this->make_lists($raw_data);
    }

    function get_by_params(string $params, int $id = null) {
        switch ($params) {
            case 'servizio':
                $raw_data = TT_ServizioModel::findOrFail($id)->flotte;
                break;
            case 'utente':
                $raw_data = TT_UtenteModel::findOrFail($id)->flotte;
                break;
            default:
                $raw_data = TT_FlottaModel::all();
                break;
        }
        return $this->make_lists($raw_data);
    }

    public function sanitize(int $id) {
        $flotta = TT_FlottaModel::findOrFail($id);
        $result = (object)[];

        $tc_flotta_utente = TC_UtenteFlottaModel::where('idRiferimento', $id)->get();
        $flotta_utente = iHelpU::groupBy($tc_flotta_utente, 'idUtente');
        $tc_flotta_servizio = TC_FlottaServizioModel::where('idFlotta', $id)->get();
        $flotta_servizio = iHelpU::groupBy($tc_flotta_servizio, 'idServizio');
        $result->deleted_users = [];
        foreach ($flotta_utente as $idUtente => $tc) {
            if (count($tc) > 1) {
                foreach ($tc as $i => $u) {
                    if ($i > 0) {
                        $u->delete();
                    }
                }
                $result->deleted_users[] = $idUtente;
            }
        }

        $result->deleted_services = [];
        foreach ($flotta_servizio as $idServizio => $tc) {
            if (count($tc) > 1) {
                foreach ($tc as $i => $u) {
                    if ($i > 0) {
                        $u->delete();
                    }
                }
                $result->deleted_services[] = $idServizio;
            }
        }

        return (array) $result;
    }

    public function delete(int $id) {
        $flotta = TT_FlottaModel::findOrFail($id);
        $flotta_servizio = TC_FlottaServizioModel::where('idFlotta', $id)->get();
        foreach ($flotta_servizio as $servizio) {
            $servizio->delete();
        }
        $flotta_utente = TC_UtenteFlottaModel::where('idRiferimento', $id)->get();
        foreach ($flotta_utente as $utente) {
            $utente->delete();
        }
        return $flotta->delete();
    }

    public function create(FlottaRequest $request, int $idFlotta = null) {
        $new_insert = $request->validated();

        $utente   = $new_insert['utente'];
        $servizio = $new_insert['servizio'];

        $flotta = $request->except('utente', 'servizio');
        $flotta['idOperatore'] = Auth::user()->id;

        if (is_null($idFlotta)) {
            $stored_flotta = TT_FlottaModel::create($flotta);
        } else {
            $stored_flotta = TT_FlottaModel::updateOrCreate(['id' => $idFlotta], $flotta);
        }

        if (isset($stored_flotta->id)) {
            $this->sync_utente($stored_flotta->id, $utente);
            $this->sync_servizio($stored_flotta->id, $servizio);

            return $this->get_id($stored_flotta->id);
        }

        throw new UnprocessableEntityHttpException();
    }

    protected function sync_utente(int $idFlotta, $specific_request) {
        $stored = TC_UtenteFlottaModel::where('idRiferimento', $idFlotta)->get();
        if (count($stored) >= 0) {
            foreach ($stored as $old) {
                $find = false;
                if (!is_null($specific_request)) {
                    foreach ($specific_request as $req) {
                        if (isset($req['id']) && $old->id == $req['id']) $find = true;
                    }
                }
                if (!$find) $old->delete();
            }
        }

        $utente = [];

        if (is_null($specific_request)) return $utente;

        foreach ($specific_request as $req) {
            $req['idRiferimento'] = $idFlotta;
            $new_req = [];
            $new_req['idRiferimento'] = $idFlotta;
            $new_req['idUtente'] = $req['idUtente'];
            $new_req['nickname'] = $req['nickname'];
            $new_req['principale'] = ($req['principale'] == true) ? true : false;
            $new_req['idOperatore'] = Auth::user()->id;
            if ($new_req['principale']) {
                // ? Devo eliminare il principale da tutte le altre flotte!
                foreach (TC_UtenteFlottaModel::where('idUtente', $req['idUtente'])->where('principale', 1)->where('idRiferimento', '!=', $idFlotta)->get() as $old_princ) {
                    $old_princ->principale = 0;
                    $old_princ->save();
                }
            }
            if (isset($req['id']) && !is_null($req['id'])) {
                $find = ['id' => $req['id']];
                unset($req['id']);
                $utente[] = TC_UtenteFlottaModel::updateOrCreate($find, $new_req);
            } else {
                $utente[] = TC_UtenteFlottaModel::updateOrCreate($new_req);
            }
        }
        return $utente;
    }

    protected function sync_servizio(int $idFlotta, $specific_request) {
        $stored = TC_FlottaServizioModel::where('idFlotta', $idFlotta)->get();
        if (count($stored) >= 0) {
            foreach ($stored as $old) {
                $find = false;
                if (!is_null($specific_request)) {
                    foreach ($specific_request as $req) {
                        if (isset($req['idServizio']) && $old->idServizio == $req['idServizio']) $find = true;
                    }
                }
                if (!$find) $old->delete();
            }
        }
        $servizio = [];

        if (is_null($specific_request)) return $servizio;

        foreach ($specific_request as $req) {
            $req['idOperatore'] = Auth::user()->id;
            $new_req       = [];
            $find          = [];
            $find['idFlotta']   = $idFlotta;
            $find['idServizio'] = $req['idServizio'];
            $new_req['nickname']    = $req['nickname'];
            $new_req['icona']       = $req['icona'];
            $new_req['idOperatore'] = Auth::user()->id;
            
            if (isset($req['id']) && !is_null($req['id'])) {
                $find['id'] = $req['id'];
                $servizio[] = TC_FlottaServizioModel::updateOrCreate($find, $new_req);
            } else {
                $servizio[] = TC_FlottaServizioModel::updateOrCreate($find, $req);
            }
        }
        return $servizio;
    }

    public function delete_servizio_from_flotta(int $idFlotta, string $params, int $id) {
        $flotta = TT_FlottaModel::findOrFail($idFlotta);

        switch ($params) {
            case 'servizio':
                $servizio = $flotta->servizi()->where('idServizio', $id)->first();
                if (!is_null($servizio)) {
                    $pivot = TC_FlottaServizioModel::where(['idServizio' => $id, 'idFlotta' => $idFlotta])->first();
                } else {
                    throw new UnprocessableEntityHttpException("Il servizio $id non è contenuto nella flotta $idFlotta!");
                }
                break;
            case 'utente':
                $utente = $flotta->utenti()->where('idRiferimento', $idFlotta)->first();
                if (!is_null($utente)) {
                    $pivot = TC_UtenteFlottaModel::where(['idRiferimento' => $idFlotta, 'idUtente' => $id])->first();
                } else {
                    throw new UnprocessableEntityHttpException("L'utente $id non è contenuto nella flotta $idFlotta!");
                }
                break;
            default:
                throw new UnprocessableEntityHttpException('Non so cosa vuoi fare!');
                break;
        }
        return $pivot->delete();
    }

    public function custom_flotta(CustomFlottaRequest $request, int $idFlotta) {
        $flotta = TT_FlottaModel::findOrFail($idFlotta);
        $req = $request->validated();

        $flotta->servizi()->updateExistingPivot($req['idServizio'], $req);

        return $flotta;
    }

    public function set_principale_flotta(int $idUtente, int $idFlotta) {
        $utente = TT_UtenteModel::findOrFail($idUtente);
        $flotta = TT_FlottaModel::findOrFail($idFlotta);

        foreach ($utente->flotte as $tmp)
            $utente->flotte()->updateExistingPivot($idFlotta, ['principale' => $tmp->id === $flotta->id]);

        return response()->noContent();
    }
}
