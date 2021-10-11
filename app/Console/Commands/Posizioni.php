<?php

namespace App\Console\Commands;

use App\Common\Managers\PosizioniManager;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Posizioni extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:posizioni';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prende tutte le latest position per mantenere il redis pronto per rispondere all\'utente.';

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
        try{
            PosizioniManager::fetchAndUpdateLatests();
        }catch(Exception $e){
            Log::channel('dev')->error('Posizioni - '.$e->getMessage());
        }
    }
}
