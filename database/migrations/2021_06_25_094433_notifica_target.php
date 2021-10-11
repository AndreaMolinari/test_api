<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NotificaTarget extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('TT_NotificaTarget', function(Blueprint $table) {
            $table->id();

            $table->boolean('usaEmailUtente')->default(0);
            $table->string('messaggioCustom')->nullable();

            // Servizio o Flotta da controllare
            $table->morphs('observable');
            // Area o soglia temperatura da controllare
            $table->morphs('trigger');

            $table->foreignId('idCampoAnagrafica')->nullable()->constrained('TT_CampoAnagrafica')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idTipologia')->constrained('TT_Tipologia')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idOperatore');
            $table->timestamps();
        });
        Schema::connection('mysql2')->dropIfExists('TT_NotificaTarget');
        Schema::connection('mysql2')->create('TT_NotificaTarget', function (Blueprint $table) {
            $table->id();

            $table->boolean('usaEmailUtente')->default(0);
            $table->string('messaggioCustom')->nullable();

            // Servizio o Flotta da controllare
            $table->morphs('observable');
            // Area o soglia temperatura da controllare
            $table->morphs('trigger');

            $table->foreignId('idCampoAnagrafica')->nullable();
            $table->foreignId('idTipologia');
            $table->foreignId('idOperatore');
            $table->timestamps();

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
    public function down()
    {
        Schema::connection('mysql2')->dropIfExists('TT_NotificaTarget');
        Schema::dropIfExists('TT_NotificaTarget');
    }
}
