<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class StoricoTarget extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TT_StoricoTarget', function (Blueprint $table) {
            $table->id();

            $table->json('rawPositionJson')->nullable();
            // Area o soglia interessata
            $table->morphs('trigger');

            $table->foreignId('idServizio')->constrained('TT_Servizio')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idTipologia')->constrained('TT_Tipologia')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
        Schema::connection('mysql2')->dropIfExists('TT_StoricoTarget');
        Schema::connection('mysql2')->create('TT_StoricoTarget', function (Blueprint $table) {
            $table->id();

            $table->json('rawPositionJson')->nullable();
            // Area o soglia interessata
            $table->morphs('trigger');

            $table->foreignId('idServizio');
            $table->foreignId('idTipologia');
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
        Schema::connection('mysql2')->dropIfExists('TT_StoricoTarget');
        Schema::dropIfExists('TT_StoricoTarget');
    }
}
