<?php

namespace App\Http\Controllers\v5;

use App\Http\Controllers\Controller;
use App\Models\v5\Brand;
use App\Models\v5\Modello;
use Illuminate\Http\Request;

class ModelloController extends Controller {
    const WHAT_TO_LOAD = ['brand', 'tipologia'];
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Brand $brand) {

        return $brand->modelli()->with('tipologia')->orderBy('updated_at', 'desc')->paginate($request->input('per_page') ?? 15);
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
     * @param  \App\Models\v5\Modello  $modelli
     * @return \Illuminate\Http\Response
     */
    public function show(Modello $modelli) {
        return $modelli->load(self::WHAT_TO_LOAD);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\v5\Modello  $modelli
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Modello $modelli) {
        return response()->noContent();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\v5\Modello  $modelli
     * @return \Illuminate\Http\Response
     */
    public function destroy(Modello $modelli) {
        return response()->noContent();
    }
}
