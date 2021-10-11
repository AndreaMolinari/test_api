<?php

namespace App\Http\Controllers\v5;

use App\Http\Controllers\Controller;
use App\Http\Resources\v5\Tipologia\TipologiaResource;
use App\Models\v5\Tipologia;

class TipologiaController extends Controller
{
    public function index()
    {
        return TipologiaResource::collection(Tipologia::whereNull('idParent')->with('descendants')->get());
    }

    public function show(Tipologia $tipologia)
    {
        return TipologiaResource::make($tipologia->load('ancestors'));
    }

    public function store()
    {
        return [];
    }

    public function update()
    {
        return [];
    }

    public function destroy()
    {
        return [];
    }
}
