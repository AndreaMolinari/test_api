<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TriggerEvento extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TT_TriggerEvento', function (Blueprint $table) {
            $table->id();

            $table->morphs('trigger');
            // $table->morphs('trackable');
            // $table->foreignId('idServizio')->constrained('TT_Servizio');
            $table->string('action_type')->nullable();
            $table->unsignedBigInteger('action_id')->nullable();
            $table->unsignedInteger('cambiaUscita')->default(0);
            // $table->string('evento');
            $table->foreignId('idTipologiaEvento')->constrained('TT_Tipologia')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idOperatore')->constrained('TT_Utente');

            $table->timestamps();

            $table->index(["action_type", "action_id"]);
        });
        Schema::connection('mysql2')->dropIfExists('TT_TriggerEvento');
        Schema::connection('mysql2')->create('TT_TriggerEvento', function (Blueprint $table) {
            $table->id();

            $table->morphs('trigger');
            // $table->morphs('trackable');
            // $table->foreignId('idServizio')->constrained('TT_Servizio');
            $table->string('action_type')->nullable();
            $table->unsignedBigInteger('action_id')->nullable();
            $table->unsignedInteger('cambiaUscita')->default(0);
            // $table->string('evento');
            $table->foreignId('idTipologiaEvento');
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
        Schema::connection('mysql2')->dropIfExists('TT_TriggerEvento');
        Schema::dropIfExists('TT_TriggerEvento');
    }
}
