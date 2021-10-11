<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TT_Servizio', function (Blueprint $table) {
            $table->id();
            $table->date('dataInizio');
            $table->date('dataFine')->nullable();
            $table->date('dataSospInizio')->nullable();
            $table->date('dataSospFine')->nullable();
            $table->double('prezzo')->nullable();
            $table->enum('reverse_with', ['ptv', 'osm'])->nullable();
            $table->boolean('bloccato')->default(false);

            $table->foreignId('idAnagrafica')->constrained('TT_Anagrafica')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idPeriodo')->constrained('TT_Tipologia')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idCausale')->constrained('TT_Tipologia')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idOperatore')->constrained('TT_Utente');

            $table->timestamps();
            $table->softDeletes();
        });
        Schema::connection('mysql2')->dropIfExists('TT_Servizio');
        Schema::connection('mysql2')->create('TT_Servizio', function (Blueprint $table) {
            $table->id();

            $table->date('dataInizio');
            $table->date('dataFine')->nullable();
            $table->date('dataSospInizio')->nullable();
            $table->date('dataSospFine')->nullable();
            $table->double('prezzo')->nullable();
            $table->string('reverse_with')->nullable();
            $table->boolean('bloccato')->default(false);

            $table->foreignId('idAnagrafica');
            $table->foreignId('idPeriodo');
            $table->foreignId('idCausale');
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
        Schema::connection('mysql2')->dropIfExists('TT_Servizio');
        Schema::dropIfExists('TT_Servizio');
    }
}
