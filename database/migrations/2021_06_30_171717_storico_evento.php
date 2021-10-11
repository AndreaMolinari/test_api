<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class StoricoEvento extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TT_StoricoEvento', function (Blueprint $table) {
            $table->id();

            $table->morphs('trigger');

            $table->foreignId('idServizio')->constrained('TT_Servizio')->onUpdate('cascade')->onDelete('cascade');

            $table->foreignId('idTipologiaEvento')->constrained('TT_Tipologia')->onUpdate('cascade')->onDelete('cascade');
            $table->json('posizione')->nullable();

            $table->timestamps();
        });
        Schema::connection('mysql2')->dropIfExists('TT_StoricoEvento');
        Schema::connection('mysql2')->create('TT_StoricoEvento', function (Blueprint $table) {
            $table->id();

            $table->morphs('trigger');

            $table->foreignId('idServizio');

            $table->foreignId('idTipologiaEvento');
            $table->json('posizione')->nullable();

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
        Schema::connection('mysql2')->dropIfExists('TT_StoricoEvento');
        Schema::dropIfExists('TT_StoricoEvento');
    }
}
