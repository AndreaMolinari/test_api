<?php

namespace App\Providers;

use App\Models\Targets\TT_AreaModel;
use App\Models\Targets\TT_NotificaModel;
use App\Models\Targets\TT_SogliaModel;
use App\Models\TT_AnagraficaModel;
use App\Models\TT_ComponenteModel;
use App\Models\TT_DDTModel;
use App\Models\TT_FlottaModel;
use App\Models\TT_ManutenzioneModel;
use App\Models\TT_ServizioModel;
use App\Models\TT_SimModel;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        // ini_set('memory_limit', '2048M');
        $this->app->register(\L5Swagger\L5SwaggerServiceProvider::class);

        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        Schema::defaultStringLength(191);

        Relation::morphMap([
            'TT_Manutenzione' => TT_ManutenzioneModel::class,
            'TT_Anagrafica' => TT_AnagraficaModel::class,
            'TT_Servizio' => TT_ServizioModel::class,
            'TT_Flotta' => TT_FlottaModel::class,
            'TT_Area' => TT_AreaModel::class,
            'TT_Soglia' => TT_SogliaModel::class,
            'TT_DDT' => TT_DDTModel::class,
            'TT_Componente' => TT_ComponenteModel::class,
            'TT_Sim' => TT_SimModel::class,
            'TT_Notifica' => TT_NotificaModel::class,
        ]);
    }
}
