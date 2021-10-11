<?php

namespace App\Console\Commands;

// use App\Http\Controllers\v3\ManutenzioneController;

use App\Common\Managers\PosizioniManager;
use App\Http\Controllers\v4\ManutenzioneController;
use App\Http\Controllers\v4\TraxController;
use App\Models\TT_ServizioModel;
use Illuminate\Console\Command;
use App\Repositories\Posizione;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class updateMezzi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:mezzi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Esegue aggiornamento di km e ore dei mezzi';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        // ini_set('memory_limit', '2048M');
        // set_time_limit(-1);
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //
        // (new ManutenzioneController)->updateMezzi();
        $start = time();
        try {
            $this->update_stat_mezzi();
        } catch (\Throwable $th) {
            Log::channel('dev')->info('updateMezzi - ' . $th->getMessage());
        }
        $end = time();

        Log::channel('dev')->info('updateMezzi - Metrics ' . ($end - $start));

        (new ManutenzioneController)->update_mezzi();
        dd('Fine');
    }

    public function update_stat_mezzi()
    {

        try {
            $servizi = TT_ServizioModel::attivi()
                ->whereHas('gps')
                ->whereHas('mezzo')
                ->with([
                    'mezzo',
                    'gps' => function ($load) {
                        return $load->orderBy('principale', 'desc');
                    }
                ])
                ->chunk(1000, function (Collection $coll) {
                    // Mo qui eseguo 500 alla volta per spezzare la query
                    // altrimenti facendola tutta in una volta max_dioporco_lenght rompeva il cazzo
                    foreach ($coll as $servizio) {
                        $pos = PosizioniManager::getLatestUnitcode($servizio->gps[0]->unitcode);
                        if ($pos) {
                            $servizio->mezzo[0]->km_totali = $pos->km;
                            if ($servizio->gps[0]->servizioComponente->parziale) {
                                $req = [
                                    'idServizio' => [$servizio->id],
                                    'FromDate' => (new Carbon())->subHours(24)->isoFormat('YYYY-MM-DD 00:00:00'),
                                    'ToDate' => (new Carbon())->isoFormat('YYYY-MM-DD 00:00:00')
                                ];
                                // dump($servizio->id . ' -.-.-.-.-.-.-.-.- ' . $servizio->mezzo[0]->ore_totali);
                                $time = (new TraxController())->parzialeGlobale($req, $servizio->id);
                                if (isset($time['TEMPO_ON'])) {
                                    $servizio->mezzo[0]->ore_totali += ($time['TEMPO_ON']['Valore'] / 3600);
                                    $servizio->mezzo[0]->ore_totali = round($servizio->mezzo[0]->ore_totali, 3);
                                }
                            }
                            $servizio->mezzo[0]->save();
                        }
                    }
                });
        } catch (\Throwable $th) {
            Log::channel('dev')->info($th->getMessage());
        }
    }
}
