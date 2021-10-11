<?php

namespace App\Providers;

use App\Http\Middleware\Applicativi\{AcciseMiddleware, AnalyzerMiddleware, AnasMiddleware, WebTraxMiddleware};
use App\Http\Middleware\Impersonification;
use App\Models\TT_DDTModel;
use App\Models\TT_TipologiaModel;
use App\Models\v5\Anagrafica;
use App\Models\v5\Fattura;
use App\Models\v5\Servizio;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider {
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot() {
        parent::boot();

        Route::bind('applicativo', function (string $value) {
            return TT_TipologiaModel::where('idParent', 83)->where('tipologia', 'LIKE', $value)->firstOrFail();
        });

        Route::bind('ddt', function (int $value) {
            return TT_DDTModel::findOrFail($value);
        });

        // ! Siamo in V5 V5 V5 V5 V5 V5 V5 V5 V5 V5 V5 V5 V5 V5 V5 V5
        Route::bind('anagrafica', function (int $value) {
            return Anagrafica::findOrFail($value);
        });

        Route::bind('servizio', function (int $value) {
            return Servizio::findOrFail($value);
        });
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map() {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes() {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes() {
        Route::prefix('owner')
            ->middleware('cors')
            ->middleware('apiUser')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.owner.php'));

        Route::group([
            'prefix' => 'api'
        ], function () {
            Route::middleware(['api', 'cors'])
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            // Route::prefix('v1')
            //     ->middleware(['api', 'cors'])
            //     ->namespace($this->namespace)
            //     ->group(base_path('routes/api.v1.php'));

            // Route::prefix('v2')
            //     ->middleware(['api', 'cors'])
            //     ->namespace($this->namespace)
            //     ->group(base_path('routes/api.v2.php'));

            //     Route::prefix('v3')
            //     ->middleware(['api', 'cors', 'auth:api'])
            //     ->namespace($this->namespace)
            //     ->group(base_path('routes/api.v3.php'));

            Route::prefix('v4')
                ->middleware(['api', 'cors', 'auth:api'])
                ->namespace($this->namespace)
                ->group(base_path('routes/api.v4.php'));

            Route::prefix('v5')
                ->middleware(['api', 'cors'])
                ->namespace($this->namespace)
                ->group(base_path('routes/api.v5.php'));

            
            Route::prefix('accise')
                ->middleware(['cors', AcciseMiddleware::class, 'api', 'auth:v5', Impersonification::class])
                ->namespace($this->namespace)
                ->group(base_path('routes/api.accise.php'));

            Route::prefix('anas')
                ->middleware(['cors', AnasMiddleware::class, 'api', 'auth:v5', Impersonification::class])
                ->namespace($this->namespace)
                ->group(base_path('routes/api.anas.php'));

            Route::prefix('analyzer')
                ->middleware(['cors', AnalyzerMiddleware::class, 'api', 'auth:v5', Impersonification::class])
                ->namespace($this->namespace)
                ->group(base_path('routes/api.analyzer.php'));

            Route::prefix('webtrax')
                ->middleware(['cors', WebTraxMiddleware::class, 'api', 'auth:v5', Impersonification::class])
                ->namespace($this->namespace)
                ->group(base_path('routes/api.webtrax.php'));

            Route::prefix('raw')
                ->middleware(['api', 'cors'])
                ->namespace($this->namespace)
                ->group(base_path('routes/api.raw.php'));

            // Route::prefix('crud')
            //     ->middleware(['api', 'cors'])
            //     ->namespace($this->namespace)
            //     ->group(base_path('routes/crud.php'));

            // Route::prefix('ticket')
            //     ->middleware(['api', 'cors'])
            //     ->namespace($this->namespace)
            //     ->group(base_path('routes/api.ticket.php'));
        });
    }
}
