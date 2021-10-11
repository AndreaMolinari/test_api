<?php

namespace App\Jobs;

use App\Common\Managers\PosizioniManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class UpdatePosizioniJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, SerializesModels, Queueable;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct() {
        $this->onQueue('posizioni');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        try {
            $latestPos = PosizioniManager::fetchAndUpdateLatests();
            if (!$latestPos) {
                return;
            }

            $latestKey = time() . 'latestPos';

            $positionsToSave = [];
            foreach($latestPos as $posizione){
                $positionsToSave[$posizione->PartitionKey] = json_encode($posizione);
            }
            Redis::hmset($latestKey, $positionsToSave);
            Redis::expire($latestKey, 60 * 10 /* 10 Minutes in seconds */);
            
            dispatch(new CheckTargetJobNew($latestKey));
            dispatch(new CheckSoglieTemperaturaJobNew($latestKey));
        } catch (\Exception $th) {
            Log::error($th->getMessage());
        }
        return;
    }
}
