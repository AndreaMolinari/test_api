<?php

namespace App\Http\Controllers\v4;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Facades\App\Repositories\iHelpU;
use Illuminate\Support\Facades\{Auth, DB, Hash};
use App\Models\{TC_AnagraficaAnagraficaModel, TC_AnagraficaIndirizzoModel, TC_AnagraficaTipologiaModel, TT_AnagraficaFatturazioneModel, TT_AnagraficaModel, TT_AnnotazioneModel, TT_ContattoModel, TT_IndirizzoModel, TT_TipologiaModel, TT_UtenteModel};
use App\Http\Requests\AnagraficaRequest;
use App\Http\Requests\PIvaRequest;
use App\Http\Requests\RicercaAnagraficaRequest;
use Elasticsearch\Client;
use Illuminate\Database\Eloquent\Builder;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\Compound\DisMaxQuery;
use ONGR\ElasticsearchDSL\Query\FullText\MultiMatchQuery;
use ONGR\ElasticsearchDSL\Query\MatchAllQuery;
use ONGR\ElasticsearchDSL\Search;
use SoapClient;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

// use Illuminate\Validation\ValidationException;

class AnagraficaController extends Controller
{
    protected function make_list($raw)
    {
        $servizi_anagrafica = iHelpU::groupBy(DB::table('TT_Servizio')->select('id', 'idAnagrafica', 'prezzo')->get(), 'idAnagrafica');
        $parents = iHelpU::groupBy(DB::table('TC_AnagraficaAnagrafica')->select('TC_AnagraficaAnagrafica.id', 'idParent', 'idChild', 'idTipologia', 'nome', 'cognome', 'ragSoc')->leftJoin('TT_Anagrafica', 'TC_AnagraficaAnagrafica.idParent', '=', 'TT_Anagrafica.id')->get(), 'idChild');
        $tipologie = iHelpU::groupBy(DB::table('TT_Tipologia')->select('id', 'tipologia')->get());

        $lista = [];
        foreach ($raw as $r) {
            $tmp = clone ($r);
            $tmp->genere = array_key_exists($tmp->id, $tipologie) ? $tipologie[$tmp->id][0]->tipologia : null;
            $tmp->servizio = (object)[
                'totale' => (array_key_exists($tmp->id, $servizi_anagrafica)) ? count($servizi_anagrafica[$tmp->id]) : 0,
            ];
            $tmp->anagraficaParent = array_key_exists($tmp->id, $parents) ? $parents[$tmp->id][0] : null;
            $lista[] = $tmp;
        }

        $lista_rivenditore = [];
        /**@var TT_UtenteModel */
        $user = Auth::user();
        if ($user->getRoleLevel() === 4) {
            foreach ($lista as $uno) {
                if (isset($uno->anagraficaParent->idParent) && $uno->anagraficaParent->idParent == $user->idAnagrafica) {
                    $lista_rivenditore[] = $uno;
                }
            }
            $lista = $lista_rivenditore;
        }

        return $lista;
    }

    public function get_all(string $params = null, int $id = null)
    {
        $visible = ['TT_Anagrafica.id', 'TT_Anagrafica.idGenere', 'TT_Anagrafica.codFisc', 'TT_Anagrafica.nome', 'TT_Anagrafica.cognome', 'TT_Anagrafica.dataNascita', 'TT_Anagrafica.pIva', 'TT_Anagrafica.referenteLegale', 'TT_Anagrafica.ragSoc', 'TT_Anagrafica.bloccato'];
        switch ($params) {
            case "latests":
                $raw = DB::table('TT_Anagrafica')
                    ->select($visible)
                    ->where('created_at', '>=', (new Carbon())->addDay(-7))
                    ->orderByDesc('id')
                    ->get();
                break;
            case "tipologia":
                $tipologia = TT_TipologiaModel::findOrFail($id);
                $raw = DB::table('TT_Anagrafica')
                    ->select($visible)
                    ->leftJoin('TC_AnagraficaTipologia', 'TT_Anagrafica.id', '=', 'TC_AnagraficaTipologia.idAnagrafica')
                    ->where('idTipologia', $tipologia->id)
                    ->orderByDesc('id')
                    ->get();
                break;
            case "short":
                if (!is_null($id)) {
                    $tipologia = TT_TipologiaModel::findOrFail($id);
                    $raw = DB::table('TT_Anagrafica')
                        ->select(['TT_Anagrafica.id', 'nome', 'cognome', 'ragSoc', 'codFisc', 'pIva'])
                        ->leftJoin('TC_AnagraficaTipologia', 'TT_Anagrafica.id', '=', 'TC_AnagraficaTipologia.idAnagrafica')
                        ->where('idTipologia', $tipologia->id)
                        ->orderByDesc('id')
                        ->get();
                } else {
                    $raw = DB::table('TT_Anagrafica')
                        ->select(['TT_Anagrafica.id', 'nome', 'cognome', 'ragSoc', 'codFisc', 'pIva'])
                        ->orderByDesc('id')
                        ->get();
                }
                return $raw;
                break;
            default:
                $raw = DB::table('TT_Anagrafica')->select($visible)->orderByDesc('id')->get();
                break;
        }

        return $this->make_list($raw);
    }

    public function get_id(int $id)
    {
        $anagrafica = TT_AnagraficaModel::findOrFail($id);

        $tipologie      = DB::table("TC_AnagraficaTipologia")->select('idTipologia')->where('idAnagrafica', $id)->get();
        $fatturazione   = DB::table("TT_AnagraficaFatturazione")
            ->select(['id', 'idModalita', 'idPeriodo', 'sdi', 'splitPA', 'esenteIVA', 'speseIncasso', 'speseSpedizione', 'banca', 'filiale', 'iban', 'iban_abi', 'iban_cab', 'iban_cin', 'pec', 'mail'])
            ->where('idAnagrafica', $id)
            ->get();
        $indirizzo      = DB::table("TC_AnagraficaIndirizzo")
            ->select(['TC_AnagraficaIndirizzo.id as idAnagraficaIndirizzo', 'idIndirizzo', 'idTipologia', 'descrizione', 'predefinito', 'istat', 'provincia', 'nazione', 'comune', 'cap', 'via', 'civico', 'bloccato'])
            ->leftJoin('TT_Indirizzo', 'TC_AnagraficaIndirizzo.idIndirizzo', '=', 'TT_Indirizzo.id')
            ->where('idAnagrafica', $id)
            ->get();
        $utente         = DB::table("TT_Utente")->select(['id', 'idTipologia', 'email', 'username', 'password_dec', 'actiaMail', 'actiaUser', 'actiaPassword', 'bloccato'])->where('idAnagrafica', $id)->get();
        $nota           = DB::table("TT_Annotazione")->where('tabella', 'TT_Anagrafica')->where('idRiferimento', $id)->get();
        // $parents        = DB::table('TT_Servizio');
        $contatti = [];
        $rubrica = DB::table("TT_Contatto")->select(['id', 'descrizione', 'nome'])->where('idAnagrafica', $id)->whereNull('idParent')->get();
        foreach ($rubrica as $rub) {
            $tmp = clone ($rub);
            $tmp->recapito = DB::table("TT_Contatto")
                ->select(['id', 'idTipologia', 'nome', 'contatto', 'predefinito'])
                ->where('idParent', $tmp->id)
                ->get();
            $contatti[] = $tmp;
        }

        $children = DB::table("TC_AnagraficaAnagrafica")
            ->select(['TT_Anagrafica.id', 'codFisc', 'nome', 'cognome', 'ragSoc', 'pIva', 'codFisc', 'idTipologia', 'tipologia'])
            ->leftJoin('TT_Anagrafica', 'TT_Anagrafica.id', '=', 'idChild')
            ->leftJoin('TT_Tipologia', 'TC_AnagraficaAnagrafica.idTipologia', '=', 'TT_Tipologia.id')
            ->where('TC_AnagraficaAnagrafica.idParent', $id)
            ->orderByDesc('id')
            ->get();
        if (count($children) >= 1) {
            $children_ids = [];
            foreach ($children as $c) {
                $children_ids[] = $c->id;
            }
            $servizi_children = iHelpU::groupBy(DB::table("TT_Servizio")->selectRaw('count(id) as conto, idAnagrafica')->whereIn('idAnagrafica', $children_ids)->groupBy('idAnagrafica')->get(), 'idAnagrafica');

            $childs = [];
            foreach ($children as $child) {
                $tmp = clone ($child);
                $tmp->servizio = (object)[
                    'totale' => (array_key_exists($tmp->id, $servizi_children)) ? $servizi_children[$tmp->id][0]->conto : 0,
                ];
                $childs[] = $tmp;
            }
            $children = $childs;
        }

        $parents = DB::table("TC_AnagraficaAnagrafica")
            ->select(['id', 'idParent', 'idTipologia'])
            ->where('idChild', $id)
            ->get();

        $anagrafica->tipologia          = $tipologie;
        $anagrafica->fatturazione       = $fatturazione;
        $anagrafica->indirizzo          = $indirizzo;
        $anagrafica->utente             = $utente;
        $anagrafica->rubrica            = $contatti;
        $anagrafica->nota               = $nota;
        $anagrafica->parents            = $parents;
        $anagrafica->children           = $children;

        return $anagrafica;
    }

    public function delete(int $id)
    {
        $anagrafica = TT_AnagraficaModel::findOrFail($id);

        $tipologie = TC_AnagraficaTipologiaModel::where('idAnagrafica', $id)->get();
        $nota = TT_AnnotazioneModel::where('tabella', 'TT_Anagrafica')->where('idRiferimento', $id)->get();
        $fatturazione = TT_AnagraficaFatturazioneModel::where('idAnagrafica', $id)->get();
        $indirizzo = TC_AnagraficaIndirizzoModel::where('idAnagrafica', $id)->get();
        $utente = TT_UtenteModel::where('idAnagrafica', $id)->get();
        $relazioni = TC_AnagraficaAnagraficaModel::where('idParent', $id)->orWhere('idParent', $id)->get();

        $eliminotutto = [$tipologie, $nota, $fatturazione, $indirizzo, $utente, $relazioni];

        foreach ($eliminotutto as $eliminoparte) {
            foreach ($eliminoparte as $elimino) {
                $elimino->delete();
            }
        }

        return $anagrafica->delete();
    }

    public function create(AnagraficaRequest $request, int $idAnagrafica = null)
    {
        $new_insert           = $request->validated();
        $fatturazione         = (!is_null($new_insert['fatturazione'])) ? $new_insert['fatturazione'][0] : null;
        $utenti               = $new_insert['utente'];
        $indirizzi            = $new_insert['indirizzo'];
        $rubrica              = $new_insert['rubrica'];
        $anagrafica_relazioni = $new_insert['relazioni'];
        $note                 = $new_insert['nota'];
        $anagrafica_tipologia = $new_insert['tipologia'];

        $anagrafica = $request->except('fatturazione', 'utente', 'utente', 'indirizzo', 'nota', 'rubrica', 'tipologia', 'documento', 'componente', 'documento', 'relazioni');
        $anagrafica['idOperatore'] = Auth::user()->id;
        // dd($anagrafica, $fatturazione, $utenti, $indirizzi, $rubrica, $anagrafica_tipologia, $anagrafica_relazioni, $note);

        if (is_null($idAnagrafica)) {
            $new_anag = TT_AnagraficaModel::create($anagrafica);
        } else {
            $new_anag = TT_AnagraficaModel::updateOrCreate(['id' => $idAnagrafica], $anagrafica);
        }

        $results = [];
        if (isset($new_anag->id)) {
            $results['relazioni']    = $this->sync_relazioni($new_anag->id, $anagrafica_relazioni);
            $results['fatturazione'] = $this->sync_fatturazione($new_anag->id, $fatturazione);
            $results['utenti']       = $this->sync_utente($new_anag->id, $utenti);
            $results['tipologia']    = $this->sync_tipologia($new_anag->id, $anagrafica_tipologia);
            $results['note']         = AnnotazioneController::sync('TT_Anagrafica', $new_anag->id, $note);
            $results['indirizzi']    = $this->sync_indirizzo($new_anag->id, $indirizzi);
            $results['rubrica']      = $this->sync_rubrica($new_anag->id, $rubrica);
        }
        return $this->get_id($new_anag->id);
        dd($results, $this->get_id($new_anag->id));
    }

    private function sync_fatturazione(int $idAnagrafica, $mini_request)
    {
        $stored = TT_AnagraficaFatturazioneModel::where('idAnagrafica', $idAnagrafica)->get();
        if (count($stored) >= 0) {
            foreach ($stored as $old) {
                if (!isset($mini_request['id']) || $old->id != $mini_request['id'])
                    $old->delete();
            }
        }
        if (is_null($mini_request)) return null;
        $mini_request['idAnagrafica'] = $idAnagrafica;
        $mini_request['idOperatore'] = Auth::user()->id;
        if (isset($mini_request['id'])) {
            return TT_AnagraficaFatturazioneModel::updateOrCreate(['id' => $mini_request['id']], $mini_request);
        } else {
            return TT_AnagraficaFatturazioneModel::updateOrCreate($mini_request);
        }
    }

    private function sync_utente(int $idAnagrafica, $mini_request)
    {
        $stored = TT_UtenteModel::where('idAnagrafica', $idAnagrafica)->get();
        if (count($stored) >= 0) {
            foreach ($stored as $old) {
                $found = false;
                if (!is_null($mini_request)) {
                    foreach ($mini_request as $req) {
                        if (isset($req['id']) && $old->id == $req['id']) $found = true;
                    }
                }
                if (!$found) {
                    $old->delete();
                }
            }
        }
        $utente = [];
        if (is_null($mini_request)) return $utente;
        foreach ($mini_request as $req) {
            $req['idAnagrafica'] = $idAnagrafica;
            $req['password']     = Hash::make($req['password']);
            $req['password_dec'] = $req['password_confirmation'];
            $req['idOperatore'] = Auth::user()->id;
            unset($req['password_confirmation'], $req['hasActia']);
            $find = [];
            if (isset($req['id'])) {
                $find['id'] = $req['id'];
            }
            $find['username'] = $req['username'];
            $utente[]           = TT_UtenteModel::updateOrCreate($find, $req);
            // $utente[] = TT_UtenteModel::firstOrCreate($req);
        }
        return $utente;
    }
    private function sync_relazioni(int $idAnagrafica, $mini_request)
    {
        // SE SEI MLS TI AGGIUNGO IL PADRE!
        /**@var TT_UtenteModel */
        $user = Auth::user();
        if ($user->getRoleLevel() === 4) {
            if (is_null($mini_request) || count($mini_request) == 0 || !in_array($user->idAnagrafica, collect($mini_request)->pluck('idParent')->toArray())) {
                if (!is_array($mini_request)) $mini_request = [];

                $mini_request[] = [
                    'idParent' => $user->idAnagrafica,
                    'idTipologia' => 60
                ];
            }
        }

        $stored = TC_AnagraficaAnagraficaModel::where('idChild', $idAnagrafica)->get();
        if (count($stored) >= 0) {
            foreach ($stored as $old) {
                $found = false;
                if (!is_null($mini_request)) {
                    foreach ($mini_request as $req) {
                        if (isset($req['id']) && $old->id == $req['id']) $found = true;
                    }
                }
                if (!$found) {
                    $old->delete();
                }
            }
        }

        $relazione = [];
        if (is_null($mini_request)) return $relazione;

        foreach ($mini_request as $req) {
            $req['idChild'] = $idAnagrafica;
            $req['idOperatore'] = $user->id;
            if (isset($req['id'])) {
                $relazione[] = TC_AnagraficaAnagraficaModel::updateOrCreate(['id' => $req['id']], $req);
            } else {
                $relazione[] = TC_AnagraficaAnagraficaModel::updateOrCreate($req);
            }
        }
        return $relazione;
    }

    private function sync_tipologia(int $idAnagrafica, $mini_request)
    {
        $stored = TC_AnagraficaTipologiaModel::where('idAnagrafica', $idAnagrafica)->get();

        if (count($stored) >= 0) {
            foreach ($stored as $old) {
                $find = false;
                if (!is_null($mini_request)) {
                    foreach ($mini_request as $key => $req) {
                        if ($old->idTipologia == $req['idTipologia']) {
                            // $mini_request[$key]['id'] = $old->id;
                            $find = true;
                        }
                    }
                }
                if (!$find) $old->delete();
            }
        }
        $tipologie = [];
        if (is_null($mini_request)) throw new UnprocessableEntityHttpException();
        foreach ($mini_request as $req) {
            $tipologie[] = TC_AnagraficaTipologiaModel::firstOrCreate(['idAnagrafica' => $idAnagrafica, 'idTipologia' => $req['idTipologia']], ['idOperatore' => Auth::id()]);
        }
        return $tipologie;
    }

    private function sync_indirizzo(int $idAnagrafica, $mini_request)
    {
        $stored = TC_AnagraficaIndirizzoModel::where('idAnagrafica', $idAnagrafica)->get();
        foreach ($stored as $old) {
            $found = false;
            if (!is_null($mini_request)) {
                foreach ($mini_request as $req) {
                    if (isset($req['id']) && $old->id == $req['id']) $found = true;
                }
            }
            if (!$found) {
                $old->delete();
            }
        }
        $new_inds = [];
        if (is_null($mini_request)) return $new_inds;
        foreach ($mini_request as $req) {
            $indirizzo = [
                'istat'     => $req['istat'],
                'nazione'   => $req['nazione'],
                'cap'       => $req['cap'],
                'comune'    => $req['comune'],
                'provincia' => $req['provincia'],
                'via'       => $req['via'],
                'civico'    => $req['civico'],
            ];
            $indirizzo['idOperatore'] = Auth::user()->id;
            if (isset($req['idIndirizzo'])) {
                $ind = TT_IndirizzoModel::updateOrCreate(['id' => $req['idIndirizzo']], $indirizzo);
            } else {
                $ind = TT_IndirizzoModel::updateOrCreate($indirizzo);
            }
            if (isset($ind->id)) {
                $anag_ind = [
                    "idAnagrafica" => $idAnagrafica,
                    "idIndirizzo"  => $ind->id,
                    "idTipologia"  => $req['idTipologia'],
                    "descrizione"  => $req['descrizione'],
                    "predefinito"  => ($req['predefinito'] == 1) ? 1 : 0,
                ];
                $anag_ind['idOperatore'] = Auth::user()->id;
                // return $anag_ind;
                if (isset($req['id'])) {
                    $anag_ind['id'] = $req['id'];
                    $new_inds[] = TC_AnagraficaIndirizzoModel::updateOrCreate(['id' => $anag_ind['id']], $anag_ind);
                } else {
                    $new_inds[] = TC_AnagraficaIndirizzoModel::updateOrCreate($anag_ind);
                }
            }
        }
        return $new_inds;
    }

    private function sync_rubrica(int $idAnagrafica, $mini_request)
    {
        $stored_rubrica = TT_ContattoModel::where('idAnagrafica', $idAnagrafica)->get();
        if (count($stored_rubrica) >= 0) {
            foreach ($stored_rubrica as $old_rubrica) {
                $found_rubrica = false;
                if (!is_null($mini_request)) {
                    foreach ($mini_request as $req_rubrica) {
                        if (isset($req_rubrica['id']) && $req_rubrica['id'] == $old_rubrica->id) {
                            $stored_contatti = TT_ContattoModel::where('idParent', $old_rubrica->id)->get();
                            foreach ($stored_contatti as $old_contatto) {
                                $found_contatto = false;
                                if (!is_null($req_rubrica['recapito']) && count($req_rubrica['recapito']) >= 0) {
                                    foreach ($req_rubrica['recapito'] as $req_recapito) {
                                        if (isset($req_recapito['id']) && $req_recapito['id'] == $old_contatto->id) $found_contatto = true;
                                    }
                                }
                                if (!$found_contatto) $old_contatto->delete();
                            }
                            $found_rubrica = true;
                        }
                    }
                }
                if (!$found_rubrica) $old_rubrica->delete();
            }
        }
        $new_rubrica = [];
        if (is_null($mini_request)) return $new_rubrica;
        foreach ($mini_request as $req) {
            $rubrica = [
                'nome'         => $req['nome'],
                'descrizione'  => $req['descrizione'],
                'idAnagrafica' => $idAnagrafica,
            ];
            if (isset($req['id'])) {
                $rubrica['id'] = $req['id'];
                $rubrica['idOperatore'] = Auth::user()->id;
                $find = ['id' => $req['id']];
                $tmp_rubrica = TT_ContattoModel::updateOrCreate($find, $rubrica);
            } else {
                $tmp_rubrica = TT_ContattoModel::updateOrCreate($rubrica);
            }

            if (isset($tmp_rubrica->id)) {
                if (!is_null($req['recapito'])) {
                    foreach ($req['recapito'] as $reca) {
                        $contatto = [
                            'idTipologia' => $reca['idTipologia'],
                            'contatto'    => $reca['contatto'],
                            'nome'        => $reca['nome'],
                            'predefinito' => (is_null($reca['predefinito'])) ? 0 : $reca['predefinito'],
                            'idParent'    => $tmp_rubrica->id,
                        ];
                        if (isset($reca['id'])) {
                            $contatto['id'] = $reca['id'];
                            $contatto['idOperatore'] = Auth::user()->id;
                            $find = ['id' => $reca['id']];
                            TT_ContattoModel::updateOrCreate($find, $contatto);
                        } else {
                            TT_ContattoModel::updateOrCreate($contatto);
                        }
                    }
                }
            }
            $new_rubrica[] = $tmp_rubrica;
        }

        return $new_rubrica;
    }


    public function get_piva_info(PIvaRequest $request) //? Aspetta pIva come parametro, torna ragSoc e sede legale.
    {
        try {
            if (isset($request->pIva)) {
                $pIva = $request->pIva;
            }
            if (isset($request->nazione)) {
                $nazione = $request->nazione;
            } else {
                $nazione = "IT";
            }
            $client = new SoapClient("https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl");
            $response = $client->checkVat(['countryCode' => $nazione, 'vatNumber' => $pIva]);

            if ($response->valid) {
                $exploded = explode("\n", $response->address);
                $indirizzo = [];
                $indirizzo["via"] = $exploded[0];
                if (is_numeric(substr($exploded[1], 0, 5))) {
                    $indirizzo["cap"] = trim(substr($exploded[1], 0, 5));
                    $indirizzo["comune"] = trim(substr($exploded[1], 5, -2));
                    $indirizzo["provincia"] = trim(substr($exploded[1], -2));
                }
                $return = [
                    'ragSoc' => $response->name,
                    'indirizzo' => $indirizzo
                ];
            } else {
                throw new UnprocessableEntityHttpException('Partita Iva non trovata');
            }

            return $return;
        } catch (\Exception $b) {
            print $b;
        }
    }

    public function valid_iban(string $iban)
    {
        try {
            $datiBancari = [];
            if (strlen($iban) == 27) {
                $datiBancari["nazione"] = substr($iban, 0, 2);
                $datiBancari["nctrl"]   = substr($iban, 2, 2);
                $datiBancari["cin"]     = substr($iban, 4, 1);
                $datiBancari["abi"]     = substr($iban, 5, 5);
                $datiBancari["cab"]     = substr($iban, 10, 5);
                $datiBancari["conto"]   = substr($iban, 15);
            } elseif (strlen($iban) == 32) {
                $iban = explode(" ", $iban);

                $datiBancari["nazione"] = $iban[0];
                $datiBancari["nctrl"]   = $iban[1];
                $datiBancari["cin"]     = $iban[2];
                $datiBancari["abi"]     = $iban[3];
                $datiBancari["cab"]     = $iban[4];
                $datiBancari["conto"]   = $iban[5];
            } else {
                return "Iban deve contenere 27 caratteri";
            }

            return $datiBancari;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function get_per_applicativo(TT_TipologiaModel $applicativo)
    {
        return $applicativo->servizi()->with('anagrafica', 'anagrafica.utenti')->groupBy('idAnagrafica')->get()->pluck('anagrafica');
    }

    public function _____ricerca(RicercaAnagraficaRequest $request)
    {
        // return Anagrafica::updateAttributesForFaceting(['tipologie']);
        return TT_AnagraficaModel::search('', function (Client $client, Search $search) use ($request) {

            // $id = new IdsQuery([$request->id]);
            $multi = new MultiMatchQuery(
                [
                    "nome",
                    "cognome",
                    "ragSoc",
                ],
                $request->nome,
                [
                    "type" => "best_fields",
                    "operator" => "or",
                    "fuzziness" => "AUTO"
                ]
            );

            // ? Direttametne su insomnia
            // {
            //     "query": {
            //       "bool": {
            //               "should": [{
            //          "term": {"nome": "andrea"}
            //               },{
            //          "term": {"cognome": "pellegrini"}				
            //               }],
            //               "must": {
            //          "match": {"tipologia": "operatore"}
            //               }
            //       }
            //     }
            //   }
            $search = new Search();
            // $search->addQuery($id, BoolQuery::SHOULD);
            $search->addQuery($multi, BoolQuery::SHOULD);
            return $client->search(['body' => $search->toArray()]);
        })->query(function (Builder $builder) {
            $builder->with('tipologia', 'utenti');
        })->paginate();
    }

    public function ricerca(RicercaAnagraficaRequest $request)
    {
        $data = $request->validated();
        return TT_AnagraficaModel::search('', function (Client $client, Search $search) use ($data) {

            $default_conf = [
                "type" => "best_fields",
                "operator" => "OR",
                "fuzziness" => "AUTO"
            ];

            $search = new Search();
            // $pIvaQuery = new TermQuery('pIva', $data['pIva']);
            // $codFiscQuery = new TermQuery('codFisc', $data['pIva']);
            $dis_max_query = new DisMaxQuery();

            if (isset($data['nome'])) {
                $dis_max_query->addQuery(new MultiMatchQuery(
                    [
                        "ragSoc",
                        "nome",
                        "cognome",
                    ],
                    $data['nome'],
                    $default_conf
                ));
            }
            if (isset($data['pIva'])) {
                $dis_max_query->addQuery(new MultiMatchQuery(
                    [
                        "pIva",
                        "codFisc",
                    ],
                    $data['pIva'],
                    $default_conf
                ));
            }

            if (isset($data['utente'])) {
                $dis_max_query->addQuery(new MultiMatchQuery(
                    ["utenti_username", "utenti_mail"],
                    $data['utente'],
                    $default_conf
                ));
            }

            if (isset($data['contatto'])) {
                $dis_max_query->addQuery(new MultiMatchQuery(
                    ["contatti"],
                    $data['contatto'],
                    $default_conf
                ));
            }

            if (isset($data['iban'])) {
                $dis_max_query->addQuery(new MultiMatchQuery(
                    ["fatturazione_iban"],
                    $data['iban'],
                    $default_conf
                ));
            }

            if (isset($data['rivenditore'])) {
                $dis_max_query->addQuery(new MultiMatchQuery(
                    ['parent_nome', 'parent_cognome', 'parent_codFisc', 'parent_pIva', 'parent_ragSoc'],
                    $data['rivenditore'],
                    $default_conf
                ));
            }

            if (isset($data['tipologia'])) {
                $dis_max_query->addQuery(new MultiMatchQuery(
                    ['tipologia'],
                    $data['tipologia'],
                    $default_conf
                ));
            }

            // Se sono tutti vuoti i campi allora ritorna tutti
            if (!empty($dis_max_query->toArray()['dis_max']['queries'])) {
                $search->addQuery($dis_max_query, BoolQuery::SHOULD);
            } else {
                $search->addQuery(new MatchAllQuery());
            }

            return $client->search(['body' => $search->toArray()]);
        })->query(function (Builder $builder) {
            $builder->with('tipologia', 'utenti');
        })->paginate();
    }
}
