<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceComponentsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TC_ServizioComponente', function (Blueprint $table) {
            $table->id();
            $table->double('prezzo')->nullable();
            $table->boolean('principale')->default(false);
            $table->boolean('parziale')->default(false);
            $table->date('dataRestituzione')->nullable();

            $table->foreignId('idServizio')->constrained('TT_Servizio')->onUpdate('cascade')->onDelete('cascade');

            $table->foreignId('idComponente')->nullable()->constrained('TT_Componente')->onUpdate('cascade')->onDelete('cascade'); //? mutuamente esclusivo
            $table->foreignId('idTacho')->nullable()->constrained('TT_Componente')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idRadiocomando')->nullable()->constrained('TT_Componente')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idSim')->nullable()->constrained('TT_Sim')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idMezzo')->nullable()->constrained('TT_Mezzo')->onUpdate('cascade')->onDelete('cascade');

            $table->foreignId('idOperatore')->constrained('TT_Utente');

            $table->timestamps();
            $table->softDeletes();
        });
        Schema::connection('mysql2')->dropIfExists('TC_ServizioComponente');
        Schema::connection('mysql2')->create('TC_ServizioComponente', function (Blueprint $table) {
            $table->id();

            $table->double('prezzo')->nullable();
            $table->boolean('principale')->default(false);
            $table->boolean('parziale')->default(false);
            $table->date('dataRestituzione')->nullable();

            $table->foreignId('idServizio');

            $table->foreignId('idComponente')->nullable(); //? mutuamente esclusivo
            $table->foreignId('idTacho')->nullable();
            $table->foreignId('idRadiocomando')->nullable();
            $table->foreignId('idSim')->nullable();
            $table->foreignId('idMezzo')->nullable();

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
        Schema::connection('mysql2')->dropIfExists('TC_ServizioComponente');
        Schema::dropIfExists('TC_ServizioComponente');
    }
}
