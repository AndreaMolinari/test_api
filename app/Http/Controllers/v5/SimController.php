<?php

namespace App\Http\Controllers\v5;

use App\Http\Controllers\Controller;
use App\Models\v5\Brand;
use App\Models\v5\Sim;
use Illuminate\Http\Request;

class SimController extends Controller {
    const WHAT_TO_LOAD = ['modello.brand', 'modello.tipologia'];
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {

        return Sim::with(static::WHAT_TO_LOAD)->paginate();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\v5\Sim  $sim
     * @return \Illuminate\Http\Response
     */
    public function show(Sim $sim) {
        return $sim->load(self::WHAT_TO_LOAD);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\v5\Sim  $sim
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Sim $sim) {
        return response()->noContent();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\v5\Sim  $sim
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sim $sim) {
        return response()->noContent();
    }
}
