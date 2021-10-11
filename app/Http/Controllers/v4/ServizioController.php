<?php
namespace App\Http\Controllers\v4;

use App\Common\Managers\PosizioniManager;
use App\Http\Controllers\Controller;
use App\Models\{TT_TipologiaModel, TC_AnagraficaAnagraficaModel, TC_FlottaServizioModel, TT_AnagraficaModel, TT_ComponenteModel, TT_MezzoModel, TC_ServizioApplicativoModel, TC_ServizioComponenteModel, TC_ServizioInstallatoreModel, TT_AnnotazioneModel, TT_ModelloModel, TT_ServizioModel, TT_SimModel};
use App\Http\Requests\ServizioRequest;
use Carbon\{Carbon};

use Facades\App\Repositories\iHelpU;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\Break_;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ServizioController extends Controller {
    public function create(ServizioRequest $request, int $idServizio = null) {
        $new_insert = $request->validated();

        $applicativo          = $new_insert['applicativo'];
        $mezzo                = $new_insert['mezzo'];
        $componente           = $new_insert['componente'];
        $tacho                = $new_insert['tacho'];
        $sim                  = $new_insert['sim'];
        $radiocomando         = $new_insert['radiocomando'];
        $servizioInstallatore = $new_insert['servizioInstallatore'];
        $nota                 = $new_insert['nota'];

        $servizio = $request->except('applicativo', 'mezzo', 'componente', 'tacho', 'radiocomando', 'servizioInstallatore', 'nota', 'sim');
        $servizio['idOperatore'] = Auth::user()->id;

        if (is_null($idServizio)) {
            $stored_servizio = TT_ServizioModel::create($servizio);
        } else {
            $stored_servizio = TT_ServizioModel::updateOrCreate(['id' => $idServizio], $servizio);
        }

        if (isset($stored_servizio->id)) {
            $this->sync_applicativo($stored_servizio->id, $applicativo);
            $this->sync_mezzo($stored_servizio->id, $mezzo);
            $this->sync_componente($stored_servizio->id, $componente);
            $this->sync_tacho($stored_servizio->id, $tacho);
            $this->sync_sim($stored_servizio->id, $sim);
            $this->sync_radiocomando($stored_servizio->id, $radiocomando);

            $this->sync_servizioInstallatore($stored_servizio->id, $servizioInstallatore);
            AnnotazioneController::sync('TT_Servizio', $stored_servizio->id, $nota);

            return $this->get_id($stored_servizio->id);
        }

        throw new UnprocessableEntityHttpException();
    }

    private function sync_applicativo(int $idServizio, $specific_request) {
        $stored = TC_ServizioApplicativoModel::where('idServizio', $idServizio)->get();
        if (count($stored) >= 0) {
            foreach ($stored as $old) {
                $find = false;
                if (!is_null($specific_request)) {
                    foreach ($specific_request as $req) {
                        if ($old->idTipologia == $req['idApplicativo']) $find = true;
                    }
                }
                if (!$find) $old->delete();
            }
        }
        $tipologie = [];
        if (is_null($specific_request)) throw new UnprocessableEntityHttpException();
        foreach ($specific_request as $req) {
            $req['idServizio'] = $idServizio;
            $req['idTipologia'] = $req['idApplicativo'];
            unset($req['idApplicativo']);
            $tipologie[] = TC_ServizioApplicativoModel::firstOrCreate($req, ['idOperatore' => Auth::user()->id]);
        }
        return $tipologie;
    }
    private function sync_componente(int $idServizio, $specific_request) {
        $stored = TC_ServizioComponenteModel::where('idServizio', $idServizio)->whereNotNull('idComponente')->get();
        if (count($stored) >= 0) {
            foreach ($stored as $old) {
                $find = false;
                if (!is_null($specific_request)) {
                    foreach ($specific_request as $req) {
                        if (isset($req['idServizioComponente']) && $old->id == $req['idServizioComponente']) $find = true;
                    }
                }
                if (!$find) $old->delete();
            }
        }
        $componente = [];
        if (is_null($specific_request)) return $componente;
        foreach ($specific_request as $req) {
            unset($req['autocomlete']);
            $req['idServizio'] = $idServizio;
            $req['idOperatore'] = Auth::user()->id;
            if (isset($req['idServizioComponente']) && !is_null($req['idServizioComponente'])) {
                $find = ['id' => $req['idServizioComponente']];
                unset($req['idServizioComponente']);
                $componente[] = TC_ServizioComponenteModel::updateOrCreate($find, $req);
            } else {
                unset($req['idServizioComponente']);
                $componente[] = TC_ServizioComponenteModel::updateOrCreate($req);
            }
        }
        return $componente;
    }
    private function sync_tacho(int $idServizio, $specific_request) {
        $stored = TC_ServizioComponenteModel::where('idServizio', $idServizio)->whereNotNull('idTacho')->get();
        if (count($stored) >= 0) {
            foreach ($stored as $old) {
                $find = false;
                if (!is_null($specific_request)) {
                    foreach ($specific_request as $req) {
                        if (isset($req['idServizioComponente']) && $old->id == $req['idServizioComponente']) $find = true;
                    }
                }
                if (!$find) $old->delete();
            }
        }
        $tacho = [];
        if (is_null($specific_request)) return $tacho;
        foreach ($specific_request as $req) {
            $req['idServizio'] = $idServizio;
            $req['idTacho'] = $req['idComponente'];
            $req['idOperatore'] = Auth::user()->id;
            unset($req['idComponente'], $req['autocomlete']);
            if (isset($req['idServizioComponente']) && !is_null($req['idServizioComponente'])) {
                $find = ['id' => $req['idServizioComponente']];
                unset($req['idServizioComponente']);
                $tacho[] = TC_ServizioComponenteModel::updateOrCreate($find, $req);
            } else {
                unset($req['idServizioComponente']);
                $tacho[] = TC_ServizioComponenteModel::updateOrCreate($req);
            }
        }
        return $tacho;
    }
    private function sync_radiocomando(int $idServizio, $specific_request) {
        $stored = TC_ServizioComponenteModel::where('idServizio', $idServizio)->whereNotNull('idRadiocomando')->get();
        if (count($stored) >= 0) {
            foreach ($stored as $old) {
                $find = false;
                if (!is_null($specific_request)) {
                    foreach ($specific_request as $req) {
                        if (isset($req['idServizioComponente']) && $old->id == $req['idServizioComponente']) $find = true;
                    }
                }
                if (!$find) $old->delete();
            }
        }
        $radiocomando = [];
        if (is_null($specific_request)) return $radiocomando;
        foreach ($specific_request as $req) {
            $req['idServizio'] = $idServizio;
            $req['idOperatore'] = Auth::user()->id;
            if (isset($req['idServizioComponente']) && !is_null($req['idServizioComponente'])) {
                $find = ['id' => $req['idServizioComponente']];
                unset($req['idServizioComponente']);
                $radiocomando[] = TC_ServizioComponenteModel::updateOrCreate($find, $req);
            } else {
                unset($req['idServizioComponente']);
                $radiocomando[] = TC_ServizioComponenteModel::updateOrCreate($req);
            }
        }
        return $radiocomando;
    }
    private function sync_sim(int $idServizio, $specific_request) {
        $stored = TC_ServizioComponenteModel::where('idServizio', $idServizio)->whereNotNull('idSim')->get();
        if (count($stored) >= 0) {
            foreach ($stored as $old) {
                $find = false;
                if (!is_null($specific_request)) {
                    foreach ($specific_request as $req) {
                        if (isset($req['idServizioComponente']) && $old->id == $req['idServizioComponente']) $find = true;
                    }
                }
                if (!$find) $old->delete();
            }
        }
        $sim = [];
        if (is_null($specific_request)) return $sim;
        foreach ($specific_request as $req) {
            unset($req['autocomlete']);
            $req['idServizio'] = $idServizio;
            $req['idOperatore'] = Auth::user()->id;
            if (isset($req['idServizioComponente']) && !is_null($req['idServizioComponente'])) {
                $find = ['id' => $req['idServizioComponente']];
                unset($req['idServizioComponente']);
                $sim[] = TC_ServizioComponenteModel::updateOrCreate($find, $req);
            } else {
                unset($req['idServizioComponente']);
                $sim[] = TC_ServizioComponenteModel::updateOrCreate($req);
            }
        }
        return $sim;
    }
    private function sync_mezzo(int $idServizio, $specific_request) {
        $stored = TC_ServizioComponenteModel::where('idServizio', $idServizio)->whereNotNull('idMezzo')->get();
        if (count($stored) >= 0) {
            foreach ($stored as $old) {
                $find = false;
                if (!is_null($specific_request)) {
                    foreach ($specific_request as $req) {
                        if (isset($req['idServizioComponente']) && $old->id == $req['idServizioComponente']) $find = true;
                    }
                }
                if (!$find) $old->delete();
            }
        }
        $mezzo = [];
        if (is_null($specific_request)) return $mezzo;
        foreach ($specific_request as $req) {
            unset($req['autocomlete']);
            $req['idServizio'] = $idServizio;
            $req['idOperatore'] = Auth::user()->id;
            if (isset($req['idServizioComponente']) && !is_null($req['idServizioComponente'])) {
                $find = ['id' => $req['idServizioComponente']];
                unset($req['idServizioComponente']);
                $mezzo[] = TC_ServizioComponenteModel::updateOrCreate($find, $req);
            } else {
                unset($req['idServizioComponente']);
                $mezzo[] = TC_ServizioComponenteModel::updateOrCreate($req);
            }
        }
        return $mezzo;
    }
    private function sync_servizioInstallatore(int $idServizio, $specific_request) {
        $stored = TC_ServizioInstallatoreModel::where('idServizio', $idServizio)->get();
        if (count($stored) >= 0) {
            foreach ($stored as $old) {
                $find = false;
                if (!is_null($specific_request)) {
                    foreach ($specific_request as $req) {
                        if (isset($req['idServizioInstallatore']) && $old->id == $req['idServizioInstallatore']) $find = true;
                    }
                }
                if (!$find) $old->delete();
            }
        }
        $installatore = [];
        if (is_null($specific_request)) return $installatore;
        foreach ($specific_request as $req) {
            $req['idServizio'] = $idServizio;
            $req['idOperatore'] = Auth::user()->id;
            if (isset($req['idServizioInstallatore']) && !is_null($req['idServizioInstallatore'])) {
                $find = ['id' => $req['idServizioInstallatore']];
                unset($req['idServizioInstallatore']);
                $installatore[] = TC_ServizioInstallatoreModel::updateOrCreate($find, $req);
            } else {
                unset($req['id'], $req['idServizioInstallatore']);
                $installatore[] = TC_ServizioInstallatoreModel::updateOrCreate($req);
            }
        }
        return $installatore;
    }

    public function get_id(int $idServizio) {
        $servizio = DB::table('TT_Servizio')->where('id', $idServizio)->limit(1)->get();
        if (count($servizio) == 1) {
            $servizio = (array) $servizio[0];
            $servizio['anagrafica'] = null;
            $servizio['applicativo'] = [];
            $servizio['componente'] = [];
            $servizio['mezzo'] = [];
            $servizio['sim'] = [];
            $servizio['tacho'] = [];
            $servizio['radiocomando'] = [];
            $servizio['installatore'] = [];
            $servizio['nota'] = [];
            $servizio['anagraficaParent'] = null;
            unset($servizio['idOperatore'], $servizio['created_at'], $servizio['updated_at'], $servizio['bloccato']);
            $servizio['applicativo'] = DB::table("TC_ServizioApplicativo")->select('idTipologia as idApplicativo')->where('idServizio', $servizio['id'])->get();


            $anagrafica = DB::table("TT_Anagrafica")
                ->select('TT_Anagrafica.id', 'TT_Anagrafica.nome', 'TT_Anagrafica.cognome', 'TT_Anagrafica.ragSoc', 'Parent.id as p_id', 'Parent.nome as p_nome', 'Parent.cognome as p_cognome', 'Parent.ragSoc as p_ragSoc')
                ->leftJoin('TC_AnagraficaAnagrafica', 'TC_AnagraficaAnagrafica.idChild', '=', 'TT_Anagrafica.id')
                ->leftJoin('TT_Anagrafica as Parent', 'TC_AnagraficaAnagrafica.idParent', '=', 'Parent.id')
                ->where('TT_Anagrafica.id', $servizio['idAnagrafica'])
                ->limit(1)
                ->get()[0];

            $servizio['anagrafica'] = (is_null($anagrafica->ragSoc)) ? $anagrafica->nome . ' ' . $anagrafica->cognome : $anagrafica->ragSoc;
            if (!is_null($anagrafica->p_id)) {
                $servizio['anagraficaParent'] = (object) [
                    'id' => $anagrafica->p_id,
                    'nome' => $anagrafica->p_nome,
                    'cognome' => $anagrafica->p_cognome,
                    'ragSoc' => $anagrafica->p_ragSoc,
                ];
            }

            $installatore = DB::table("TC_ServizioInstallatore")
                ->select('TT_Anagrafica.nome', 'TT_Anagrafica.cognome', 'TT_Anagrafica.ragSoc', 'TC_ServizioInstallatore.*')
                ->leftJoin('TT_Anagrafica', 'TC_ServizioInstallatore.idAnagrafica', '=', 'TT_Anagrafica.id')
                ->where('TC_ServizioInstallatore.idServizio', $servizio['id'])
                ->limit(1)
                ->get();

            if (count($installatore) == 1) {
                $installatore = $installatore[0];
                $servizio['installatore'] = [
                    (object) [
                        "idServizioInstallatore" => $installatore->id,
                        "idAnagrafica" => $installatore->idAnagrafica,
                        "dataInstallazione" => $installatore->dataInstallazione,
                        "descrizione" => $installatore->descrizione,
                    ]
                ];
            }


            $componenti = DB::table("TC_ServizioComponente")
                ->where('idServizio', $servizio['id'])
                ->get();

            foreach ($componenti as $componente) {
                if (!is_null($componente->idComponente)) {
                    $servizio['componente'][] = (object)[
                        'idServizioComponente'  => $componente->id,
                        'idComponente'          => $componente->idComponente,
                        'parziale'              => $componente->parziale,
                        'principale'            => $componente->principale,
                        'prezzo'                => $componente->prezzo,
                        'dataRestituzione'      => $componente->dataRestituzione,
                    ];
                }
                if (!is_null($componente->idTacho)) {
                    $servizio['tacho'][] = (object)[
                        'idServizioComponente'  => $componente->id,
                        'idComponente'          => $componente->idTacho,
                        'parziale'              => $componente->parziale,
                        'principale'            => $componente->principale,
                        'prezzo'                => $componente->prezzo,
                        'dataRestituzione'      => $componente->dataRestituzione,
                    ];
                }
                if (!is_null($componente->idSim)) {
                    $servizio['sim'][] = (object)[
                        'idServizioComponente'  => $componente->id,
                        'idComponente'          => $componente->idSim,
                        'parziale'              => $componente->parziale,
                        'principale'            => $componente->principale,
                        'prezzo'                => $componente->prezzo,
                        'dataRestituzione'      => $componente->dataRestituzione,
                    ];
                }
                if (!is_null($componente->idRadiocomando)) {
                    $servizio['radiocomando'][] = (object)[
                        'idServizioComponente'  => $componente->id,
                        'idComponente'          => $componente->idRadiocomando,
                        'parziale'              => $componente->parziale,
                        'principale'            => $componente->principale,
                        'prezzo'                => $componente->prezzo,
                        'dataRestituzione'      => $componente->dataRestituzione,
                    ];
                }
                if (!is_null($componente->idMezzo)) {
                    $servizio['mezzo'][] = (object)[
                        'idServizioComponente'  => $componente->id,
                        'idMezzo'               => $componente->idMezzo,
                        'parziale'              => $componente->parziale,
                        'principale'            => $componente->principale,
                        'prezzo'                => $componente->prezzo,
                        'dataRestituzione'      => $componente->dataRestituzione,
                    ];
                }
            }

            $servizio['nota'] = DB::table("TT_Annotazione")->select('id', 'testo')->where('tabella', 'TT_Servizio')->where('idRiferimento', $servizio['id'])->get();
        } else {
            return [];
        }

        return $servizio;
    }

    protected function make_list($servizi) {
        $anagrafica = iHelpU::groupBy(
            DB::table((new TT_AnagraficaModel)->table)
                ->select((new TT_AnagraficaModel)->table . '.id', 'nome', 'cognome', 'ragSoc', 'idParent')
                ->leftJoin((new TC_AnagraficaAnagraficaModel)->table, (new TC_AnagraficaAnagraficaModel)->table . '.idChild', '=', (new TT_AnagraficaModel)->table . '.id')
                ->get(),
            'id'
        );
        $applicativo = iHelpU::groupBy(
            DB::table((new TC_ServizioApplicativoModel)->table)
                ->select((new TC_ServizioApplicativoModel)->table . '.id', 'idTipologia', 'idServizio', 'tipologia')
                ->join((new TT_TipologiaModel)->table, (new TT_TipologiaModel)->table . '.id', '=', 'idTipologia')
                ->get(),
            'idServizio'
        );
        $servizio_componente = iHelpU::groupBy(
            DB::select("SELECT " . (new TC_ServizioComponenteModel)->table . ".`id`, `idServizio`, `idComponente`, `idTacho`, " . (new TC_ServizioComponenteModel)->table . ".`idSim`, `idMezzo`, `idRadiocomando`, `prezzo`, `principale`, `parziale`, `dataRestituzione`, `gps`.`unitcode` as `unitcode_gps`, `serial`, `tacho`.`unitcode` as `unitcode_tacho`, `targa`, `telaio`
                FROM " . (new TC_ServizioComponenteModel)->table . "
                LEFT OUTER JOIN " . (new TT_ComponenteModel)->table . " as `gps` ON " . (new TC_ServizioComponenteModel)->table . ".`idComponente` = `gps`.`id`
                LEFT OUTER JOIN " . (new TT_ComponenteModel)->table . " as `tacho` ON " . (new TC_ServizioComponenteModel)->table . ".`idTacho` = `tacho`.`id`
                LEFT OUTER JOIN " . (new TT_SimModel)->table . " as `sim` ON " . (new TC_ServizioComponenteModel)->table . ".`idSim` = `sim`.`id`
                LEFT OUTER JOIN " . (new TT_MezzoModel)->table . " ON " . (new TC_ServizioComponenteModel)->table . ".`idMezzo` = " . (new TT_MezzoModel)->table . ".`id`
                WHERE 1;"),
            'idServizio'
        );

        $lista = [];

        foreach ($servizi as $s) {
            if ($s instanceof TT_ServizioModel) {
                $tmp = (object) $s->getAttributes();
            } else {
                $tmp = clone ($s);
            }
            $tmp->anagrafica = null;
            $tmp->anagraficaParent = null;
            $tmp->applicativo = [];
            $tmp->componente = [];
            $tmp->tacho = [];
            $tmp->sim = [];
            $tmp->radiocomando = [];
            $tmp->mezzo = [];

            if ($anagrafica[$s->idAnagrafica] ?? false) {
                // if (array_key_exists($s->idAnagrafica, $anagrafica)) {
                $tmp->anagrafica = (!is_null($anagrafica[$s->idAnagrafica][0]->ragSoc)) ? $anagrafica[$s->idAnagrafica][0]->ragSoc : $anagrafica[$s->idAnagrafica][0]->nome . ' ' . $anagrafica[$s->idAnagrafica][0]->cognome;
                if (!is_null($anagrafica[$s->idAnagrafica][0]->idParent)) {
                    $tmp->anagraficaParent = (object) [
                        'id' => $anagrafica[$anagrafica[$s->idAnagrafica][0]->idParent][0]->id,
                        'nome' => $anagrafica[$anagrafica[$s->idAnagrafica][0]->idParent][0]->nome,
                        'cognome' => $anagrafica[$anagrafica[$s->idAnagrafica][0]->idParent][0]->cognome,
                        'ragSoc' => $anagrafica[$anagrafica[$s->idAnagrafica][0]->idParent][0]->ragSoc,
                    ];
                }
            }

            // if (array_key_exists($s->id, $servizio_componente)) { // pulito la cache?
            foreach ($servizio_componente[$s->id] ?? [] as $sc) {
                if (!is_null($sc->idComponente)) {
                    $tmp->componente[] = (object)[
                        'idComponente' => $sc->idComponente,
                        'unitcode' => $sc->unitcode_gps,
                    ];
                }
                if (!is_null($sc->idTacho)) {
                    $tmp->tacho[] = (object)[
                        'idTacho' => $sc->idTacho,
                        'unitcode' => $sc->unitcode_tacho
                    ];
                }
                if (!is_null($sc->idSim)) {
                    $tmp->sim[] = (object)[
                        'idSim' => $sc->idSim,
                        'serial' => $sc->serial,
                    ];
                }
                if (!is_null($sc->idRadiocomando)) {
                    $tmp->radiocomando[] = (object)[
                        'idRadiocomando' => $sc->idRadiocomando
                    ];
                }
                if (!is_null($sc->idMezzo)) {
                    $tmp->mezzo = [(object)[
                        'idMezzo' => $sc->idMezzo,
                        'targa' => $sc->targa,
                        'telaio' => $sc->telaio,
                    ]];
                }
            }
            // }

            // if (array_key_exists($s->id, $applicativo)) {
            foreach ($applicativo[$s->id] ?? [] as $sa) {
                $tmp->applicativo[] = (object)[
                    'idWebService' => $sa->idTipologia,
                    'applicativo' => $sa->tipologia,
                ];
            }
            // }

            $lista[] = $tmp;
        }

        $lista_rivenditore = [];
        /**@var TT_UtenteModel */
        $user = Auth::user();
        if ($user->getRoleLevel() === 4) {
            foreach ($lista as $uno) {
                if (isset($uno->anagraficaParent->id) && $uno->anagraficaParent->id == $user->idAnagrafica)
                    $lista_rivenditore[] = $uno;
            }
            $lista = $lista_rivenditore;
        }

        return $lista;
    }

    public function get_all(string $status = null) {
        switch ($status) {
            case 'latests':
                $servizi =  DB::table((new TT_ServizioModel)->table)
                    ->select('id', 'idAnagrafica', 'idPeriodo', 'dataInizio', 'dataFine', 'prezzo', 'idCausale', 'dataSospInizio', 'dataSospFine')
                    ->where('created_at', '>=', (new Carbon(now()))->add('-7', 'days'))
                    ->orderBy('id', 'DESC')
                    ->get();
                break;
            case 'attivo':
                $servizi =  DB::table((new TT_ServizioModel)->table)
                    ->select('id', 'idAnagrafica', 'idPeriodo', 'dataInizio', 'dataFine', 'prezzo', 'idCausale', 'dataSospInizio', 'dataSospFine')
                    ->where('dataInizio', '<=', now())
                    ->where('dataFine', '>=', now())->orWhereNull('dataFine')
                    ->orderBy('id', 'DESC')
                    ->get();
                break;
            case 'scaduto':
                $servizi =  DB::table((new TT_ServizioModel)->table)
                    ->select('id', 'idAnagrafica', 'idPeriodo', 'dataInizio', 'dataFine', 'prezzo', 'idCausale', 'dataSospInizio', 'dataSospFine')
                    ->where('dataFine', '<=', now())
                    ->orderBy('id', 'DESC')
                    ->get();
                break;
            case 'futuro':
                $servizi =  DB::table((new TT_ServizioModel)->table)
                    ->select('id', 'idAnagrafica', 'idPeriodo', 'dataInizio', 'dataFine', 'prezzo', 'idCausale', 'dataSospInizio', 'dataSospFine')
                    ->where('dataInizio', '>=', now())
                    ->orderBy('id', 'DESC')
                    ->get();
                break;
            default:
                $servizi =  DB::table((new TT_ServizioModel)->table)
                    ->select('id', 'idAnagrafica', 'idPeriodo', 'dataInizio', 'dataFine', 'prezzo', 'idCausale', 'dataSospInizio', 'dataSospFine')
                    ->orderBy('id', 'DESC')
                    ->get();
                break;
        }
        return $this->make_list($servizi);
    }

    public function get_per(string $params, int $id) {
        switch ($params) {
            case 'anagrafica':
                $servizi =  DB::table('TT_Servizio')
                    ->select('id', 'idAnagrafica', 'idPeriodo', 'dataInizio', 'dataFine', 'prezzo', 'idCausale', 'dataSospInizio', 'dataSospFine')
                    ->where('idAnagrafica', $id)
                    ->orderBy('id', 'DESC')
                    ->get();
                break;
            case 'applicativo':
                $servizi_presenti =  DB::table("TC_ServizioApplicativo")
                    ->select('idServizio')
                    ->where('idTipologia', $id)
                    ->get();
                $exlude = array_keys(iHelpU::groupBy($servizi_presenti, 'idServizio'));

                $servizi =  DB::table((new TT_ServizioModel)->table)
                    ->select('id', 'idAnagrafica', 'idPeriodo', 'dataInizio', 'dataFine', 'prezzo', 'idCausale', 'dataSospInizio', 'dataSospFine')
                    ->where('dataInizio', '<=', now())
                    ->where('dataFine', '>=', now())->orWhereNull('dataFine')
                    ->whereNotIn('id', $exlude)
                    ->orderBy('id', 'DESC')
                    ->get();
                break;
            default:
                return [];
                break;
        }

        return $this->make_list($servizi);
    }

    public function get_per_applicativo(TT_TipologiaModel $applicativo, string $scope = null) {
        $builder = $applicativo->servizi()->attivi();

        // switch ($scope) {
        //     case 'attivi':
        //         $builder->attivi();
        //         break;
        // }

        return $builder->get()->makeHidden('servizio_applicativo');
    }

    public function get_servizi_non_in_flotta(int $idFlotta = null) {
        $visible = ['TT_Servizio.id', 'idAnagrafica', 'idPeriodo', 'dataInizio', 'dataFine', 'TT_Servizio.prezzo', 'idCausale', 'dataSospInizio', 'dataSospFine'];
        $table_servizio = (new TT_ServizioModel)->table;
        $table_servizio_componente = (new TC_ServizioComponenteModel())->table;

        if (!is_null($idFlotta)) {
            $servizi_presenti =  DB::table("TC_FlottaServizio")
                ->select('idServizio')
                ->where('idFlotta', $idFlotta)
                ->get();
            $exlude = array_keys(iHelpU::groupBy($servizi_presenti, 'idServizio'));
        }

        $exlude = (isset($exlude) && count($exlude) >= 1) ? " AND `" . $table_servizio . "`.`id` NOT IN (" . implode(", ", $exlude) . ") " : '';

        $servizi = DB::select('SELECT ' . implode(', ', $visible) . '
            FROM `' . $table_servizio . '`
            INNER JOIN `' . $table_servizio_componente . '` ON `' . $table_servizio_componente . '`.`idServizio` = `' . $table_servizio . '`.`id`
            WHERE `' . $table_servizio_componente . '`.`idComponente` IS NOT NULL
            AND `dataInizio` <= now() AND (`dataFine` IS NULL OR `dataFine` >= now())
            ' . $exlude . '
            ORDER BY `' . $table_servizio . '`.`id` DESC;
        ');

        return $this->make_list($servizi);
    }

    public function giffi() {
        $idAnagrafica = 906;
        $idFlotta = 702;

        if (is_file(base_path('giffi.json'))) {
            $old_data = file_get_contents(base_path('giffi.json'));
            $old_data = iHelpU::groupBy(json_decode($old_data), 'unitcode');
        }

        $db_servizi = $this->get_per_anagrafica($idAnagrafica);

        foreach ($db_servizi as $servizio) {
            if (count($servizio->componente) >= 1) {
                foreach ($servizio->componente as $cmp) {
                    if (array_key_exists($cmp->unitcode, $old_data)) {
                        unset($old_data[$cmp->unitcode]);
                    }
                }
            }
        }
        // $old_data = array_slice($old_data, 0, 1);

        $new = [];
        foreach ($old_data as $data) {
            $missed = $data[0];
            $servizio = TT_ServizioModel::create([
                'idAnagrafica' => $idAnagrafica,
                'idCausale' => 30,
                'dataInizio' => explode('-', $missed->dataInizio)[2] . '-' . explode('-', $missed->dataInizio)[1] . '-' . explode('-', $missed->dataInizio)[0],
                'idPeriodo' => ($missed->periodicita == 'Mensile') ? 51 : null,
                'prezzo' => $missed->prezzo,
                'idOperatore' => 1,
            ]);

            TC_ServizioApplicativoModel::firstOrCreate([
                'idServizio' => $servizio->id,
                'idTipologia' => 86,
                'idOperatore' => 1,
            ]);

            if (isset($missed->dataInstallazione) && !empty($missed->dataInstallazione)) {
                $find = [
                    'dataInstallazione' => explode('-', $missed->dataInstallazione)[2] . '-' . explode('-', $missed->dataInstallazione)[1] . '-' . explode('-', $missed->dataInstallazione)[0],
                    'idServizio' => $servizio->id,
                    'idOperatore' => 1,
                ];
            }

            if (isset($missed->sim) && !empty($missed->sim)) {
                $sim = TT_SimModel::firstWhere('serial', $missed->sim);
                if (is_null($sim)) {
                    $sim = TT_SimModel::firstOrCreate([
                        'serial' => $missed->sim,
                        'idModello' => 5,
                        'idOperatore' => 1,
                    ]);
                }
            }

            if (isset($missed->unitcode) && !empty($missed->unitcode)) {
                $componente = TT_ComponenteModel::firstWhere('unitcode', $missed->unitcode);
                if (is_null($componente)) {
                    $find = [
                        'unitcode' => $missed->unitcode,
                        'idModello' => 40,
                        'idOperatore' => 1,
                    ];
                    if (isset($sim)) {
                        $find['idSim'] = $sim->id;
                    }

                    $componente = TT_ComponenteModel::firstOrCreate($find);
                }

                TC_ServizioComponenteModel::firstOrCreate([
                    'idComponente' => $componente->id,
                    'idServizio' => $servizio->id,
                    'principale' => 1,
                    'idOperatore' => 1,
                ]);
            }

            if (isset($missed->targa) && !empty($missed->targa)) {
                $mezzo = TT_MezzoModel::where('targa', 'LIKE', $missed->targa)->orWhere('telaio', 'LIKE', $missed->targa)->first();

                if (is_null($mezzo)) {
                    $find = [
                        'targa' => $missed->targa,
                        'idModello' => 1,
                        'bloccato' => 0,
                        'idOperatore' => 1,
                    ];

                    $mezzo = TT_MezzoModel::firstOrCreate($find);
                }

                TC_ServizioComponenteModel::firstOrCreate([
                    'idMezzo' => $mezzo->id,
                    'idServizio' => $servizio->id,
                    'parziale' => null,
                    'principale' => 0,
                    'idOperatore' => 1,
                ]);
            }

            if (isset($idFlotta)) {
                TC_FlottaServizioModel::firstOrCreate([
                    'idServizio' => $servizio->id,
                    'idFlotta' => $idFlotta,
                ]);
            }

            $new[] = $servizio;

            unset($find, $sim, $componente, $mezzo, $servizio);
        }
        return "Bravo, la tua importazione è stata eseguita con sulcesso";
        // return $this->make_list($new);
    }

    public function delete(int $id) {
        $servizio = TT_ServizioModel::findOrFail($id);

        $componenti = TC_ServizioComponenteModel::where('idServizio', $id)->get();
        $installatori = TC_ServizioInstallatoreModel::where('idServizio', $id)->get();
        $applicativo = TC_ServizioApplicativoModel::where('idServizio', $id)->get();
        $nota = TT_AnnotazioneModel::where('tabella', 'TT_Servizio')->where('idRiferimento', $id)->get();
        $flotta = TC_FlottaServizioModel::where('idServizio', $id)->get();

        $eliminotutto = [$componenti, $installatori, $applicativo, $nota, $flotta];

        foreach ($eliminotutto ?? [] as $eliminoparte) {
            foreach ($eliminoparte ?? [] as $elimino) {
                $elimino->delete();
            }
        }

        return $servizio->delete();
    }

    public function sanitize() {
        $servizi              = iHelpU::groupBy(DB::select('SELECT `id` FROM `TT_Servizio` WHERE 1', [1]), 'id');
        $servizi_componenti   = DB::select('SELECT `id`, `idServizio` FROM `TC_ServizioComponente` WHERE 1', [1]);
        $servizi_installatori = DB::select('SELECT `id`, `idServizio` FROM `TC_ServizioInstallatore` WHERE 1', [1]);
        $servizi_flotta       = DB::select('SELECT `id`, `idServizio` FROM `TC_FlottaServizio` WHERE 1', [1]);
        $servizi_applicativo  = DB::select('SELECT `id`, `idServizio` FROM `TC_ServizioApplicativo` WHERE 1', [1]);


        $to_check = [
            TC_ServizioComponenteModel::class   => $servizi_componenti,
            TC_ServizioInstallatoreModel::class => $servizi_installatori,
            TC_FlottaServizioModel::class       => $servizi_flotta,
            TC_ServizioApplicativoModel::class  => $servizi_applicativo,
        ];
        $sanitized = 0;
        foreach ($to_check ?? [] as $model => $check) {
            foreach ($check ?? [] as $c) {
                if (!array_key_exists($c->idServizio, $servizi)) {
                    $model::findOrFail($c->id)->delete();
                    $sanitized += 1;
                }
            }
        }

        return $sanitized;
    }

    public function sanitizeApplicativiDups() {
        $query = DB::raw(
            'SELECT DISTINCT
            sa1.id, sa1.idServizio, sa1.idTipologia
        FROM
            TC_ServizioApplicativo AS sa1
                JOIN
            TC_ServizioApplicativo AS sa2 ON sa1.idServizio = sa2.idServizio
                AND sa1.idTipologia = sa2.idTipologia
                AND sa1.id <> sa2.id
        order by sa1.idServizio, sa1.id;'
        );

        $serv_appls = collect(DB::select($query))->groupBy('idServizio');

        $to_delete = [];
        foreach ($serv_appls as $idServizio => $servizio_appl) {
            for ($i = 1; $i < count($servizio_appl); $i++) {
                $to_delete[] = $servizio_appl[$i]->id;
            }
        }

        TC_ServizioApplicativoModel::findMany($to_delete)->each(function ($item) {
            $item->forceDelete();
        });

        // Se non ci sono duplicati dovrebbe tornare []
        return DB::select($query);
    }

    public function checkPerTipo(string $param) {
        // teltonika|xtrax|thingsMobile|tMobile
        set_time_limit(0);
        $attivi = " and `dataInizio` <= now() and ( dataFine >= now() OR dataFine IS NULL )";

        $serviziQuery = TT_ServizioModel::query();
        switch ($param) {
            case 'teltonika':
                $idTipologia = 10; // Tipologia periferica gps
                $idBrand = 2;
                $modello_ids = TT_ModelloModel::where('idTipologia', $idTipologia)
                    ->where('idBrand', $idBrand)
                    ->select('id')->get()
                    ->makeHidden('tipologia', 'brand')
                    ->pluck('id')->toArray();

                $serviziQuery = DB::select("
                    select `id`
                    from `TT_Servizio`
                    where exists (
                        select 1
                        from `TT_Componente`
                        inner join `TC_ServizioComponente` on `TT_Componente`.`id` = `TC_ServizioComponente`.`idComponente`
                        where `TT_Servizio`.`id` = `TC_ServizioComponente`.`idServizio`
                        and `idModello` in ( " . implode(', ', $modello_ids) . " )
                        and `TC_ServizioComponente`.`idComponente` is not null
                    )" . $attivi . "
                ");
                break;
            case 'xtrax':
                $idTipologia = 10; // Tipologia periferica gps
                $idBrand = 4;
                $modello_ids = TT_ModelloModel::where('idTipologia', $idTipologia)
                    ->where('idBrand', $idBrand)
                    ->select('id')->get()
                    ->makeHidden('tipologia', 'brand')
                    ->pluck('id')->toArray();

                $serviziQuery = DB::select("
                    select `id`
                    from `TT_Servizio`
                    where exists (
                        select 1
                        from `TT_Componente`
                        inner join `TC_ServizioComponente` on `TT_Componente`.`id` = `TC_ServizioComponente`.`idComponente`
                        where `TT_Servizio`.`id` = `TC_ServizioComponente`.`idServizio`
                        and `idModello` in ( " . implode(', ', $modello_ids) . " )
                        and `TC_ServizioComponente`.`idComponente` is not null
                    )" . $attivi . "
                ");
                break;
            case 'thingsmobile':
                $idTipologia = 11; // Tipologia periferica gps
                $idBrand = 6;
                $modello_ids = TT_ModelloModel::where('idTipologia', $idTipologia)
                    ->where('idBrand', $idBrand)
                    ->select('id')->get()
                    ->makeHidden('tipologia', 'brand')
                    ->pluck('id')->toArray();
                $serviziQuery = DB::select("
                        select `id`
                        from `TT_Servizio`
                        where exists (
                            select 1
                            from `TT_Componente`
                            inner join `TC_ServizioComponente` on `TT_Componente`.`id` = `TC_ServizioComponente`.`idComponente`
                            where `TT_Servizio`.`id` = `TC_ServizioComponente`.`idServizio`
                            and exists (
                                select 1
                                from `TT_Sim`
                                where `TT_Componente`.`idSim` = `TT_Sim`.`id`
                                and `idModello` in (" . implode(', ', $modello_ids) . ")
                            ) and `TC_ServizioComponente`.`idComponente` is not null
                        )" . $attivi . "
                    ");
                break;
            case 'tmobile':
                $idTipologia = 11; // Tipologia periferica gps
                $idBrand = 5;
                $modello_ids = TT_ModelloModel::where('idTipologia', $idTipologia)
                    ->where('idBrand', $idBrand)
                    ->select('id')->get()
                    ->makeHidden('tipologia', 'brand')
                    ->pluck('id')->toArray();
                $serviziQuery = DB::select("
                        select `id`
                        from `TT_Servizio`
                        where exists (
                            select 1
                            from `TT_Componente`
                            inner join `TC_ServizioComponente` on `TT_Componente`.`id` = `TC_ServizioComponente`.`idComponente`
                            where `TT_Servizio`.`id` = `TC_ServizioComponente`.`idServizio`
                            and exists (
                                select 1
                                from `TT_Sim`
                                where `TT_Componente`.`idSim` = `TT_Sim`.`id`
                                and `idModello` in (" . implode(', ', $modello_ids) . ")
                            ) and `TC_ServizioComponente`.`idComponente` is not null
                        )" . $attivi . "
                    ");
                break;
            default:
                return response('', 404);
                break;
        }
        $servizio_ids = collect($serviziQuery)->pluck('id')->toArray();

        $unitcodes = collect(DB::select("SELECT `unitcode`
            FROM `TT_Componente`
            INNER JOIN `TC_ServizioComponente` ON `TC_ServizioComponente`.`idComponente` = `TT_Componente`.`id`
            WHERE `TC_ServizioComponente`.`idServizio` IN (" . join(', ', $servizio_ids) . ")
        "))->pluck('unitcode');
        return collect(PosizioniManager::getLatestUnitcodes(...$unitcodes))->sortByDesc('fixGps')->splice(0, 10)->pluck('fixGps', 'PartitionKey');
    }

    public function check_posizioni() {
        $test = ['tmobile', ''];
        $results = [];
        foreach ($test as $t) {
            $results[$t] = $this->checkPerTipo($t);
        }
        return $results;
    }
}
