<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class TestJo implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**@var string */
    protected $old_key;

    /**@var string */
    protected $latest_key;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $old_key, string $latest_key) {
        $this->old_key = $old_key;
        $this->latest_key = $latest_key;
        $this->onQueue('test');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        Log::channel('dev')->debug($this->old_key);
        Log::channel('dev')->debug(Redis::get($this->old_key));
        Log::channel('dev')->debug($this->latest_key);
        Log::channel('dev')->debug(Redis::get($this->latest_key));
    }
}
