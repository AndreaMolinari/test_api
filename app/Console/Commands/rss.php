<?php

namespace App\Console\Commands;

use App\Http\Controllers\v4\RssController;
use Illuminate\Console\Command;

class rss extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rss:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aggiorna gli rss';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        return RssController::set_all();
    }
}
