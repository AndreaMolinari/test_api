<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;

class CleanRedisWG extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pulisce tutto quello che è legato a wg';

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
        try
        {
            cache()->forget("TC_AnagraficaIndirizzo.ALL");
            cache()->forget("TC_AnagraficaTipologia.ALL");
            cache()->forget("TC_AutistaComponente.ALL");
            cache()->forget("TC_FlottaServizio.ALL");
            cache()->forget("TC_ServizioApplicativo.ALL");
            cache()->forget("TC_ServizioComponente.ALL");
            cache()->forget("TC_ServizioInstallatore.ALL");
            cache()->forget("TC_UtenteFlotta.ALL");
    
            cache()->forget("TT_Anagrafica.ALL");
            cache()->forget("TT_AnagraficaFatturazione.ALL");
            cache()->forget("TT_Brand.ALL");
            cache()->forget("TT_Sim.ALL");
            cache()->forget("TT_Componente.ALL");
            cache()->forget("TT_Mezzo.ALL");
            cache()->forget("TT_Contatto.ALL");
            cache()->forget("TT_Flotta.ALL");
            cache()->forget("TT_Indirizzo.ALL");
            cache()->forget("TT_Modello.ALL");
            cache()->forget("TT_Servizio.ALL");
            cache()->forget("TT_Servizio.ATTIVI");
            cache()->forget("TT_Servizio.SCADUTI");
            cache()->forget("TT_Servizio.FUTURI");
            cache()->forget("TT_Tipologia.ALL");
            cache()->forget("TT_Utente.ALL");
            //
            dd("bella");
        }catch(Exception $e)
        {
            dd($e->getMessage());
        }
    }
}
