<?php

namespace App\Http\Controllers\v5;

use App\Http\Controllers\Controller;
use App\Http\Requests\v5\RicercaAnagraficaRequest;
use App\Http\Requests\v5\StoreAnagraficaRequest;
use App\Http\Resources\v5\Anagrafica\AnagraficaResource;
use App\Models\v5\Anagrafica;
use Elasticsearch\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\Compound\DisMaxQuery;
use ONGR\ElasticsearchDSL\Query\FullText\MultiMatchQuery;
use ONGR\ElasticsearchDSL\Query\MatchAllQuery;
use ONGR\ElasticsearchDSL\Search;

class AnagraficaController extends Controller {
    const WHAT_TO_LOAD = ['genere', 'tipologie', 'utenti', 'parent', 'fatturazione', 'rubriche', 'indirizzi'];
    const WHAT_TO_COUNT = ['servizi'];
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $resourceClass = $request->attributes->get('AnagraficaResource') ?? AnagraficaResource::class;
        return $resourceClass::collection(Anagrafica::with(static::WHAT_TO_LOAD)->withCount(static::WHAT_TO_COUNT)->orderBy('updated_at', 'desc')->paginate($request->input('per_page') ?? 15));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\StoreAnagraficaRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAnagraficaRequest $request) {
        $anagrafica = Anagrafica::make($request->validated());
        $anagrafica->genere()->associate($request->input('genere.id'));
        $anagrafica->save();
        $anagrafica->wasRecentlyCreated = true;
        return $this->show($anagrafica);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\v5\Anagrafica  $anagrafica
     * @return \Illuminate\Http\Response
     */
    public function show(Anagrafica $anagrafica) {
        return $anagrafica->load(array_merge(static::WHAT_TO_LOAD, ['servizi.periodo', 'servizi.mezzo', 'fatture']));
        return AnagraficaResource::make($anagrafica->load(self::WHAT_TO_LOAD));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\StoreAnagraficaRequest  $request
     * @param  \App\Models\v5\Anagrafica  $anagrafica
     * @return \Illuminate\Http\Response
     */
    public function update(StoreAnagraficaRequest $request, Anagrafica $anagrafica) {
        $anagrafica->genere()->associate($request->input('genere.id'));
        $anagrafica->update($request->validated());
        return response()->noContent();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\v5\Anagrafica  $anagrafica
     * @return \Illuminate\Http\Response
     */
    public function destroy(Anagrafica $anagrafica) {
        return response()->noContent();
    }

    public function search(RicercaAnagraficaRequest $request) {
        $data = $request->validated();
        return AnagraficaResource::collection(Anagrafica::search('', function (Client $client, Search $search) use ($data) {

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
                $search->addQuery($dis_max_query, BoolQuery::MUST);
            } else {
                $search->addQuery(new MatchAllQuery());
            }

            return $client->search(['body' => $search->toArray()]);
        })->query(function (Builder $builder) {
            $builder->with(static::WHAT_TO_LOAD);
        })->paginate($request->input('per_page'), 'page', $request->input('page')));
    }
}
