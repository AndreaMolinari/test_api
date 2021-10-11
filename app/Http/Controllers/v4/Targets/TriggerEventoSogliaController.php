<?php

namespace App\Http\Controllers\v4\Targets;

use App\Http\Controllers\Controller;
use App\Http\Requests\Targets\TriggerEventoRequest;
use App\Models\Targets\TT_TriggerEventoModel;
use App\Models\Targets\TT_SogliaModel;
use App\Models\Targets\TT_NotificaModel;
use App\Models\Targets\TT_NotificaTargetModel;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

class TriggerEventoSogliaController extends Controller {

    public function tmpPort() {
        return TT_NotificaTargetModel::with(['tipologia', 'observable', 'trigger'])->get()->groupBy(['trigger_id', 'idTipologia']);
    }

    public function index(int $idSoglia) {
        $query = TT_SogliaModel::findOrFail($idSoglia)->triggers_evento()->with([
            // 'trigger',
            'servizi',
            'action' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    TT_NotificaModel::class => [
                        'contatti',
                    ],
                ]);
            },
            'evento',
        ]);

        return $query->get();
    }

    public function store(TriggerEventoRequest $request, int $idSoglia) {
        $soglia = TT_SogliaModel::findOrFail($idSoglia);
        $data = $request->validated();

        // Creazione o modifica notifica
        if ($data['action']) {
            /** @var TT_NotificaModel */
            $notifica = TT_NotificaModel::firstOrNew(['id' => $data['action']['id'] ?? null], $data['action']);

            if (!($notifica->id ?? false)) {
                $notifica->idOperatore = Auth::id();
            }

            $notifica->utente()->associate($soglia->idUtente);

            $notifica->save();
            $notifica->contatti()->sync($data['action']['contatti']);
        }

        /** @var TT_TriggerEventoModel */
        $triggerEvento = TT_TriggerEventoModel::make([
            'trigger_id' => $soglia->id,
            'trigger_type' => 'TT_Soglia',
            'cambiaUscita' => $data['cambiaUscita'],
            'idTipologiaEvento' => $data['evento']['id'],
        ]);

        if (!($triggerEvento->id ?? false)) {
            $triggerEvento->idOperatore = Auth::id();
        }

        $triggerEvento->evento()->associate($data['evento']['id']);

        $triggerEvento->trigger()->associate($soglia);

        // Associazione notifica
        if ($data['action']) {
            // Se la notifica associata prima è diversa da questa (non ha passato un id) la elimino
            if ($triggerEvento->action && $triggerEvento->action->id !== $notifica->id) {
                $triggerEvento->action->delete();
            }
            $triggerEvento->action()->associate($notifica);
        } else {
            // Se non passata la rimuovo
            if ($triggerEvento->action)
                $triggerEvento->action->delete();
            $triggerEvento->action()->dissociate();
        }

        $triggerEvento->save();

        $triggerEvento->servizi()->sync($data['servizi']);

        return $this->show($soglia->id, $triggerEvento->id);
    }

    public function show(int $idSoglia, int $idTriggerEvento) {
        /** @var TT_SogliaModel */
        $soglia = TT_SogliaModel::findOrFail($idSoglia);
        return $soglia->triggers_evento()->where('id', $idTriggerEvento)->with([
            // 'trigger',
            'servizi',
            'action' => function (MorphTo $morphTo) {
                return $morphTo->morphWith([
                    TT_NotificaModel::class => [
                        'contatti',
                    ],
                ]);
            },
            'evento',
        ])->firstOrFail();
    }

    public function update(TriggerEventoRequest $request, int $idSoglia, int $idTriggerEvento) {
        /** @var TT_SogliaModel */
        $soglia = TT_SogliaModel::findOrFail($idSoglia);

        /** @var TT_TriggerEventoModel */
        $triggerEvento = $soglia->triggers_evento()->where('id', $idTriggerEvento)->with(['action'])->firstOrFail();

        $data = $request->validated();

        // Creazione o modifica notifica
        if ($data['action']) {
            /** @var TT_NotificaModel */
            $notifica = TT_NotificaModel::firstOrNew(['id' => $data['action']['id'] ?? null], $data['action']);

            if (!($notifica->id ?? false)) {
                $notifica->idOperatore = Auth::id();
            }

            $notifica->utente()->associate($soglia->idUtente);

            $notifica->save();
            $notifica->contatti()->sync($data['action']['contatti']);

            // Se la notifica associata prima è diversa da questa (non ha passato un id) la elimino
            if ($triggerEvento->action && $triggerEvento->action->id !== $notifica->id) {
                $triggerEvento->action->delete();
            }
            $triggerEvento->action()->associate($notifica);
        } else {
            // Se non passata la rimuovo
            if ($triggerEvento->action)
                $triggerEvento->action->delete();
            $triggerEvento->action()->dissociate();
        }

        $triggerEvento->evento()->associate($data['evento']['id']);

        $triggerEvento->servizi()->sync($data['servizi']);

        $triggerEvento->update(['cambiaUscita' => $data['cambiaUscita']]);

        return response()->noContent();
    }

    public function delete(int $idSoglia, int $idTriggerEvento) {
        /** @var TT_SogliaModel */
        $soglia = TT_SogliaModel::findOrFail($idSoglia);

        /** @var TT_TriggerEventoModel */
        $triggerEvento = $soglia->triggers_evento()->where('id', $idTriggerEvento)->firstOrFail();

        $triggerEvento->delete();

        return response()->noContent();
    }
}
