<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaintenancesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TT_Manutenzione', function (Blueprint $table) {
            $table->id();
            $table->date('data_ritiro')->nullable();
            $table->date('giorno_start')->nullable();
            $table->date('giorno_end')->nullable();
            $table->integer('giorni_intervallo')->nullable();
            $table->integer('km_start')->nullable();
            $table->integer('km_fine')->nullable();
            $table->integer('km_intervallo')->nullable();
            $table->double('ore_start')->nullable();
            $table->double('ore_fine')->nullable();
            $table->double('ore_intervallo')->nullable();
            $table->double('prezzo')->nullable();
            $table->boolean('ripeti')->default(false);
            $table->integer('giorni_preavviso')->nullable();
            $table->integer('km_preavviso')->nullable();
            $table->integer('ore_preavviso')->nullable();
            $table->string('email_notifica')->nullable();
            $table->integer('ore_lavoro')->nullable();
            $table->boolean('sent_preavviso')->nullable()->default(false);
            $table->boolean('sent_scaduta')->nullable()->default(false);

            $table->foreignId('idServizio')->constrained('TT_Servizio')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idTipologia')->constrained('TT_Tipologia')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idCampoAnagrafica')->constrained('TT_CampoAnagrafica')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idOfficina')->nullable()->constrained('TT_CampoAnagrafica')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('custom_email_id')->nullable()->constrained('TT_CampoAnagrafica')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idOperatore')->constrained('TT_Utente');

            $table->timestamps();
            $table->softDeletes();
        });
        Schema::connection('mysql2')->dropIfExists('TT_Manutenzione');
        Schema::connection('mysql2')->create('TT_Manutenzione', function (Blueprint $table) {
            $table->id();

            $table->date('data_ritiro')->nullable();
            $table->date('giorno_start')->nullable();
            $table->date('giorno_end')->nullable();
            $table->integer('giorni_intervallo')->nullable();
            $table->integer('km_start')->nullable();
            $table->integer('km_fine')->nullable();
            $table->integer('km_intervallo')->nullable();
            $table->double('ore_start')->nullable();
            $table->double('ore_fine')->nullable();
            $table->double('ore_intervallo')->nullable();
            $table->double('prezzo')->nullable();
            $table->boolean('ripeti')->default(false);
            $table->integer('giorni_preavviso')->nullable();
            $table->integer('km_preavviso')->nullable();
            $table->integer('ore_preavviso')->nullable();
            $table->string('email_notifica')->nullable();
            $table->integer('ore_lavoro')->nullable();
            $table->boolean('sent_preavviso')->nullable()->default(false);
            $table->boolean('sent_scaduta')->nullable()->default(false);

            $table->foreignId('idServizio')->nullable();
            $table->foreignId('idTipologia')->nullable();
            $table->foreignId('idCampoAnagrafica')->nullable();
            $table->foreignId('idOfficina')->nullable();
            $table->foreignId('custom_email_id')->nullable();
            $table->foreignId('idOperatore');

            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('triggered_id');
            $table->timestamp('triggered_at')->useCurrent();
            $table->string('triggered_action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::connection('mysql2')->dropIfExists('TT_Manutenzione');
        Schema::dropIfExists('TT_Manutenzione');
    }
}
