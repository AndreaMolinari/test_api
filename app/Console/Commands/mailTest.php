<?php
namespace App\Console\Commands;

use App\Http\Controllers\v4\MailController;
use Illuminate\Console\Command;

class mailTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Invia una mail di test per controllare il cron';

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
        $mail = (object) [
            'object' => "Controllo del cron",
            'body' => "Se questa mail arriva ogni ora, il cron funziona.",
        ];

        MailController::job_info($mail);
    }
}
