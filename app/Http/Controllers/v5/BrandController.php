<?php

namespace App\Http\Controllers\v5;

use App\Http\Controllers\Controller;
use App\Models\v5\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller {
    const WHAT_TO_LOAD = ['modelli.tipologia'];
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {

        return Brand::with(self::WHAT_TO_LOAD)->orderBy('updated_at', 'desc')->paginate($request->input('per_page') ?? 15);
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
     * @param  \App\Models\v5\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function show(Brand $brand) {
        return $brand->load(self::WHAT_TO_LOAD);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\v5\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Brand $brand) {
        return response()->noContent();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\v5\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function destroy(Brand $brand) {
        return response()->noContent();
    }
}
