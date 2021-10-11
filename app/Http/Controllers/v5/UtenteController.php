<?php

namespace App\Http\Controllers\v5;

use App\Http\Controllers\Controller;
use App\Http\Requests\v5\StoreUtenteRequest;
use App\Http\Resources\v5\Utente\UtenteResource;
use App\Models\v5\{Utente, Anagrafica};
use Illuminate\Support\Facades\Hash;

class UtenteController extends Controller
{
    const WHAT_TO_LOAD = ['anagrafica', 'tipologia'];
    /**
     * Display a listing of the resource.
     *
     * @param  \App\Models\v5\Anagrafica  $anagrafica
     * @return \Illuminate\Http\Response
     */
    public function index(Anagrafica $anagrafica = null)
    {
        if (!$anagrafica) {
            return UtenteResource::collection(Utente::with(self::WHAT_TO_LOAD)->orderBy('updated_at', 'desc')->paginate());
        }
        return UtenteResource::collection($anagrafica->utenti()->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\v5\Anagrafica  $anagrafica
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUtenteRequest $request, Anagrafica $anagrafica = null)
    {
        if( $anagrafica )
        {
            $utente = Utente::make($request->validated());
            $utente->password_dec = $utente->password;
            $utente->password = Hash::make($utente->password_dec);
            $utente->anagrafica()->associate($anagrafica);
            $utente->tipologia()->associate($request->input('tipologia.id'));
            $utente->save();
            $utente->wasRecentlyCreated = true;
            return $this->show(null, $utente);
        }
        return $anagrafica ?? $request->all();
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\v5\Anagrafica  $anagrafica
     * @param  \App\Models\v5\Utente  $utente
     * @return \Illuminate\Http\Response
     */
    public function show(Anagrafica $anagrafica = null, Utente $utente)
    {
        if( !$anagrafica )
        {
            return UtenteResource::make($utente->load(self::WHAT_TO_LOAD));
        }
        return UtenteResource::make($utente);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\v5\Anagrafica  $anagrafica
     * @param  \App\Models\v5\Utente  $utente
     * @return \Illuminate\Http\Response
     */
    public function update(StoreUtenteRequest $request, Anagrafica $anagrafica, Utente $utente)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\v5\Anagrafica  $anagrafica
     * @param  \App\Models\v5\Utente  $utente
     * @return \Illuminate\Http\Response
     */
    public function destroy(Anagrafica $anagrafica, Utente $utente)
    {
        //
    }
}
