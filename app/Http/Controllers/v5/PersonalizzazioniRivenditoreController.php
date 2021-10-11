<?php

namespace App\Http\Controllers\v5;

use App\Http\Controllers\Controller;
use App\Http\Requests\v5\PersonalizzazioniRivenditoreRequest;
use App\Models\v5\Anagrafica;
use App\Models\v5\PersonalizzazioniRivenditore;
use App\Models\v5\Utente;
use Illuminate\Support\Facades\Auth;

class PersonalizzazioniRivenditoreController extends Controller {
    public function show(Anagrafica $anagrafica = null) {
        /** @var Utente */
        $authUser = Auth::user();
        $anagrafica = $authUser->getRoleLevel() > 1 ? Auth::user()->anagrafica : $anagrafica ?? Auth::user()->anagrafica;

        return PersonalizzazioniRivenditore::byAnagrafica($anagrafica)->firstOrFail();
    }

    public function store(PersonalizzazioniRivenditoreRequest $request, Anagrafica $anagrafica = null) {
        /** @var Utente */
        $authUser = Auth::user();
        $anagrafica = $authUser->getRoleLevel() > 1 ? Auth::user()->anagrafica : $anagrafica ?? Auth::user()->anagrafica;

        return PersonalizzazioniRivenditore::firstOrCreate(['idAnagrafica' => $anagrafica->id], $request->validated());
    }
}
