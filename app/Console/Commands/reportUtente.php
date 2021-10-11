<?php

namespace App\Console\Commands;

use App\Http\Controllers\v3\TraxController;
use Illuminate\Console\Command;

class reportUtente extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:utente';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera un report mensile per tutte flotte di un determinato utente';

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
        //idUtente 4 -> anthea

    }
}
