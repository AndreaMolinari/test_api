<?php

namespace App\Http\Controllers\v5;

use App\Http\Controllers\Controller;
use App\Models\v5\Mezzo;
use Illuminate\Http\Request;

class MezzoController extends Controller {
    const WHAT_TO_LOAD = ['modello.brand', 'modello.tipologia'];
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return Mezzo::with(static::WHAT_TO_LOAD)->orderBy('updated_at', 'desc')->paginate($request->input('per_page') ?? 15);
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
     * @param  \App\Models\v5\Mezzo  $mezzo
     * @return \Illuminate\Http\Response
     */
    public function show(Mezzo $mezzo) {
        return $mezzo->load(self::WHAT_TO_LOAD);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\v5\Mezzo  $mezzo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Mezzo $mezzo) {
        return response()->noContent();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\v5\Mezzo  $mezzo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mezzo $mezzo) {
        return response()->noContent();
    }
}
