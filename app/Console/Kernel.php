<?php

namespace App\Console;

use App\Jobs\UpdatePosizioniJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\App;

class Kernel extends ConsoleKernel {
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule) {
        // TODO: Ricorda che per abilitare questa cosina carina, devi aggiungere ai cron del server
        // TODO: Il comando che ti dice laravel -> * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
        // ? crontab -e
        // ? * * * * * cd /home/imatto/public_html/API && php artisan schedule:run >> /dev/null 2>&1
        /**
         * ! sudo a2enmod rewrite per abilitare HTACCESS
         * TODO: Permessi di scrittura per i log, senza di questi non va un ca**o
         * ? sudo chmod -R 775 storage
         * ? sudo chmod -R 775 bootstrap/cache
         * ? sudo chown -R $USER:www-data storage
         * ? sudo chown -R $USER:www-data bootstrap/cache
         * * Sarebbe carino farlo prima di php artisan passport:install
         */

        // Pulisce i token revocati e scaduti dalla tabella
        $schedule->command('passport:purge')->daily()->runInBackground();

        // $schedule->command('update:posizioni')->everyMinute()->runInBackground();

        $schedule->command('rss:update')->everyFifteenMinutes()->runInBackground();

        if (App::environment('produzione')  ) {
            $schedule->job(new UpdatePosizioniJob)->everyMinute()->runInBackground();

            $schedule->command('report:mensile')->monthlyOn(1, '02:00')->runInBackground();

            $schedule->command('update:mezzi')->dailyAt('03:00')->runInBackground();
        } else {
            $schedule->job(new UpdatePosizioniJob)->everyMinute()->runInBackground();
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands() {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
