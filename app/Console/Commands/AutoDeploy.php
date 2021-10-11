<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AutoDeploy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git:deploy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'pulla';

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
     * @return mixed
     */
    public function handle()
    {
        // Ricorda che devi fare la chiave rsa per l'account di apache! Scemo! nel serverone è www-data
        $return = [];
        exec('cd ../; git pull 2>&1; whoami; ssh-keyscan -t rsa github.com | ssh-keygen -lf -', $return);
        exit( json_encode($return) );
        return $return;
    }
}