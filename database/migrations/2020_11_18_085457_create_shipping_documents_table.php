<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShippingDocumentsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TT_DDT', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('numero')->unsigned();
            $table->year('anno');
            $table->date('dataSpedizione');
            $table->integer('colli');
            $table->datetime('dataOraRitiro')->nullable();
            $table->double('pesoTotale');
            $table->double('costoSpedizione');

            $table->foreignId('idCliente')->constrained('TT_Anagrafica')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idIndirizzoDestinazione')->constrained('TT_Indirizzo')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idTrasportatore')->constrained('TT_Anagrafica')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idTrasporto')->constrained('TT_Tipologia')->onUpdate('cascade')->onDelete('cascade'); // Mittente, Destinatario, Vettore
            $table->foreignId('idCausale')->constrained('TT_Tipologia')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idAspetto')->constrained('TT_Tipologia')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idOperatore')->constrained('TT_Utente');

            $table->timestamps();
            $table->softDeletes();
        });
        Schema::connection('mysql2')->dropIfExists('TT_DDT');
        Schema::connection('mysql2')->create('TT_DDT', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('numero')->unsigned();
            $table->year('anno');
            $table->date('dataSpedizione');
            $table->integer('colli');
            $table->datetime('dataOraRitiro')->nullable();
            $table->double('pesoTotale');
            $table->double('costoSpedizione');

            $table->foreignId('idCliente');
            $table->foreignId('idIndirizzoDestinazione');
            $table->foreignId('idTrasportatore');
            $table->foreignId('idTrasporto'); // Mittente, Destinatario, Vettore
            $table->foreignId('idCausale');
            $table->foreignId('idAspetto');
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
        Schema::connection('mysql2')->dropIfExists('TT_DDT');
        Schema::dropIfExists('TT_DDT');
    }
}
