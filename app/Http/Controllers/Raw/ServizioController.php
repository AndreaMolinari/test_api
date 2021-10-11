<?php

namespace App\Http\Controllers\Raw;

use App\Http\Controllers\Controller;
use App\Models\v5\Servizio;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ServizioController extends Controller
{

    const WHAT_TO_LOAD = ['gps', 'mezzo'];


    // ? ████████████████████████████████████████████████
    // ? █░░░░░░░░░░░░░░███░░░░░░░░░░░░░░█░░░░░░░░░░░░███
    // ? █░░▄▀▄▀▄▀▄▀▄▀░░███░░▄▀▄▀▄▀▄▀▄▀░░█░░▄▀▄▀▄▀▄▀░░░░█
    // ? █░░▄▀░░░░░░▄▀░░███░░▄▀░░░░░░░░░░█░░▄▀░░░░▄▀▄▀░░█
    // ? █░░▄▀░░██░░▄▀░░███░░▄▀░░█████████░░▄▀░░██░░▄▀░░█
    // ? █░░▄▀░░░░░░▄▀░░░░█░░▄▀░░░░░░░░░░█░░▄▀░░██░░▄▀░░█
    // ? █░░▄▀▄▀▄▀▄▀▄▀▄▀░░█░░▄▀▄▀▄▀▄▀▄▀░░█░░▄▀░░██░░▄▀░░█
    // ? █░░▄▀░░░░░░░░▄▀░░█░░░░░░░░░░▄▀░░█░░▄▀░░██░░▄▀░░█
    // ? █░░▄▀░░████░░▄▀░░█████████░░▄▀░░█░░▄▀░░██░░▄▀░░█
    // ? █░░▄▀░░░░░░░░▄▀░░█░░░░░░░░░░▄▀░░█░░▄▀░░░░▄▀▄▀░░█
    // ? █░░▄▀▄▀▄▀▄▀▄▀▄▀░░█░░▄▀▄▀▄▀▄▀▄▀░░█░░▄▀▄▀▄▀▄▀░░░░█
    // ? █░░░░░░░░░░░░░░░░█░░░░░░░░░░░░░░█░░░░░░░░░░░░███
    // ? ████████████████████████████████████████████████

    public function reverse_with(Request $request)
    {
        $validated = $request->validate([
            'last_check' => 'before_or_equal:' . now(),
        ]);

        $listone = Servizio::attivi()->with(self::WHAT_TO_LOAD);
        
        if ($validated['last_check'] ?? false) {
            $listone->where('updated_at', '>=', $validated['last_check']);
        }

        return $listone->get()->map(fn ($s) => $s->gps->map(fn ($g) => (object)[
            'PartitionKey' => $g->unitcode,
            'ReverseWith' => $s->reverse_with,
        ]))->flatten();
    }


    // TODO ███████╗░██████╗██████╗░██████╗░██╗████████╗
    // TODO ██╔════╝██╔════╝██╔══██╗██╔══██╗██║╚══██╔══╝
    // TODO █████╗░░╚█████╗░██████╔╝██████╔╝██║░░░██║░░░
    // TODO ██╔══╝░░░╚═══██╗██╔═══╝░██╔══██╗██║░░░██║░░░
    // TODO ███████╗██████╔╝██║░░░░░██║░░██║██║░░░██║░░░
    // TODO ╚══════╝╚═════╝░╚═╝░░░░░╚═╝░░╚═╝╚═╝░░░╚═╝░░░

    public function specifics(Request $request)
    {
        $validated = $request->validate([
            'last_check' => 'before_or_equal:' . now(),
        ]);

        // ? Caro Remo, dovresti espormi una tabella con le configurazioni possibili per ogni modello di periferica (le configurazioni verranno create dal tuo configuratore)
        $lista = Servizio::attivi()
            ->whereHas('gps')
            ->with('gps', 'gps.modello');

        if ($validated['last_check'] ?? false) {
            $lista->where('updated_at', '>=', $validated['last_check']);
        }

        return $lista->get()->map( fn ($s) => $s->gps->map( fn ($g) => (object)[
            'unitcode'       => $g->unitcode,
            'modello'        => $g->modello->nome ?? null,
            'targa'          => ($s->mezzo->first()) ? $s->mezzo->first()->targa ?? $s->mezzo->first()->telaio : null,
            'configurazione' => null,
        ]))->flatten();
    }
}
