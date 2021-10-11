<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MakeController extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '
        make:custom 
        {--name=*}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'personalizzato';
    
    protected $pathModel = null;
    protected $pathController = null;
    protected $pathMigration = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->stato = (object) [];

        $basePath = base_path();

        $this->pathModel = $basePath.'/app';
        $this->pathController = $basePath.'/app/Http/Controllers';

        // $this->templateMigration = file_get_contents(dirname(__FILE__)."/templateMigration.txt");
        // $this->templateModel = file_get_contents(dirname(__FILE__)."/templateModel.txt");
        $this->templateController = file_get_contents(dirname(__FILE__)."/templateController.txt");
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->option('name')[0];

        // $this->stato->migration = $this->createMigration($name);
        // $this->stato->model = $this->createModel($name);
        $this->stato->controller = $this->createController($name);

        return json_encode($this->stato);
    }

    private function createMigration($name){
        $params = [
            'name' => $name,
            '--table' => $name
        ];
        
        Artisan::call('make:migration', $params);

        return True;
    }

    private function createModel($name){
        $file_path = $this->pathModel.'/'.$name.'Model.php';

        $params = [
            'name' => $name.'Model'
        ];

        $this->templateModel = str_replace('{{$modelName}}', $name.'Model', $this->templateModel);
        $this->templateModel = str_replace('{{$tableName}}', $name, $this->templateModel);
        
        Artisan::call('make:model', $params);

        $file_handle = fopen($file_path, 'w');
        fwrite($file_handle, $this->templateModel);
        fclose($file_handle);

        return True;
    }

    private function createController($name){
        $params = [
            'name' => $name.'Model'
        ];

        // Artisan::call('make:model', $params);

        $this->templateController = str_replace('{{$modelName}}', $name.'Model', $this->templateController);
        $this->templateController = str_replace('{{$controllerName}}', $name.'Controller', $this->templateController);
        $this->templateController = str_replace('{{$tabellaName}}', $name, $this->templateController);

        $file_path = $this->pathModel.'/Http/Controllers/crud/'.$name.'Controller.php';

        $file_handle = fopen($file_path, 'w');
        fwrite($file_handle, $this->templateController);
        fclose($file_handle);

        return True;
    }
}