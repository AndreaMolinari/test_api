<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class dropAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dropAll';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $variabiliGlobali = [
            'DB_DATABASEHISTORY',
            'DB_DATABASE'
        ];

        foreach($variabiliGlobali as $singolo){
            $this->resetDB(env($singolo));
        }
    }

    public function resetDB($dbName)
    {
        $droplist = [];

        $colname = 'Tables_in_' . $dbName;

        $tables = DB::select('SHOW TABLES');

        foreach($tables as $table) {
            $droplist[] = $table->$colname;
        }

        $droplist = implode(',', $droplist);

        if(!empty($droplist)){
            DB::beginTransaction();
            DB::statement("DROP TABLE $droplist");
            DB::commit();
        }
    }
}
