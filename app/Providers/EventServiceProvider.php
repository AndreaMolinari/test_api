<?php

namespace App\Providers;

use App\Events\OutTargetEvent;
use App\Listeners\InOutTargetSubscriber;
use App\Models\TT_BrandModel;
use App\Models\TT_ContattoModel;
use App\Models\TT_SimModel;
use App\Models\v5\Anagrafica;
use App\Models\v5\DDT;
use App\Models\v5\Fattura;
use App\Models\v5\Flotta;
use App\Models\v5\Servizio;
use App\Observers\OperatoreObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        // Eliminati listener che apparentemente non dovevano essere qui, in caso controllare i vecchi commit su Git
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        InOutTargetSubscriber::class,
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Fattura::observe(OperatoreObserver::class);
        Servizio::observe(OperatoreObserver::class);
        Anagrafica::observe(OperatoreObserver::class);
        Flotta::observe(OperatoreObserver::class);
        DDT::observe(OperatoreObserver::class);
        
        TT_ContattoModel::observe(OperatoreObserver::class);
        TT_SimModel::observe(OperatoreObserver::class);
        TT_BrandModel::observe(OperatoreObserver::class);
    }
}
