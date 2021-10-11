<?php

namespace App\Http\Controllers\v4;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventoRequest;
use App\Models\TT_EventoModel;
use Illuminate\Support\Facades\Auth;

class EventoController extends Controller
{
    public function get_all()
    {
        return TT_EventoModel::all();
    }

    public function get_id(int $id)
    {
        return TT_EventoModel::findOrFail($id);
    }

    public function create(EventoRequest $request, int $id = null)
    {
        $request = $request->validated();
        $request['idOperatore'] = Auth::id();

        if( !is_null($id) ) {
            $evento = TT_EventoModel::updateOrCreate(['id' => $id], $request);
        }else{
            $evento = TT_EventoModel::create($request);
        }

        return $evento;
    }

    public function delete(int $id)
    {
        $evento = TT_EventoModel::findOrFail($id);
        return $evento->delete();
    }
}
