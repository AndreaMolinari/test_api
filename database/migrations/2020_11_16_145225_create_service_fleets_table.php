<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceFleetsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TC_FlottaServizio', function (Blueprint $table) {
            $table->id();
            $table->string('nickname')->nullable();
            $table->string('icona')->nullable();
            $table->boolean('bloccato')->default(false);

            $table->foreignId('idGruppo')->nullable();
            $table->foreignId('idFlotta')->constrained('TT_Flotta')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idServizio')->constrained('TT_Servizio')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idOperatore')->constrained('TT_Utente');

            $table->timestamps();
            $table->softDeletes();
        });
        Schema::connection('mysql2')->dropIfExists('TC_FlottaServizio');
        Schema::connection('mysql2')->create('TC_FlottaServizio', function (Blueprint $table) {
            $table->id();

            $table->string('nickname')->nullable();
            $table->string('icona')->nullable();
            $table->boolean('bloccato')->default(false);

            $table->foreignId('idGruppo')->nullable();
            $table->foreignId('idFlotta');
            $table->foreignId('idServizio');
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
        Schema::connection('mysql2')->dropIfExists('TC_FlottaServizio');
        Schema::dropIfExists('TC_FlottaServizio');
    }
}
