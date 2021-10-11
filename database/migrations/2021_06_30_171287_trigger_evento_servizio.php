<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TriggerEventoServizio extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TC_TriggerEventoServizio', function (Blueprint $table) {
            $table->id();

            $table->foreignId('idTriggerEvento')->constrained('TT_TriggerEvento')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idServizio')->constrained('TT_Servizio')->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
        });
        Schema::connection('mysql2')->dropIfExists('TC_TriggerEventoServizio');
        Schema::connection('mysql2')->create('TC_TriggerEventoServizio', function (Blueprint $table) {
            $table->id();

            $table->foreignId('idTriggerEvento');
            $table->foreignId('idServizio');

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
        Schema::connection('mysql2')->dropIfExists('TC_TriggerEventoServizio');
        Schema::dropIfExists('TC_TriggerEventoServizio');
    }
}
