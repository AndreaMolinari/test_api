<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegistriesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TT_Anagrafica', function (Blueprint $table) {
            $table->id();

            $table->string('nome')->nullable();
            $table->string('cognome')->nullable();
            $table->date('dataNascita')->nullable();
            $table->string('codFisc')->nullable();
            $table->string('pIva')->nullable();
            $table->string('referenteLegale')->nullable();
            $table->string('ragSoc')->nullable();

            $table->boolean('bloccato')->default(false);

            $table->foreignId('idGenere')->constrained('TT_Tipologia')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idAgente')->nullable()->constrained('TT_Anagrafica')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idCommerciale')->nullable()->constrained('TT_Anagrafica')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idOperatore')->constrained('TT_Utente');

            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('TT_Utente', function (Blueprint $table) {
            $table->foreign('idAnagrafica')->on('TT_Anagrafica')->references('id')->onUpdate('cascade')->onDelete('cascade');
        });
        Schema::connection('mysql2')->dropIfExists('TT_Anagrafica');
        Schema::connection('mysql2')->create('TT_Anagrafica', function (Blueprint $table) {
            $table->id();

            $table->string('nome')->nullable();
            $table->string('cognome')->nullable();
            $table->date('dataNascita')->nullable();
            $table->string('codFisc')->nullable();
            $table->string('pIva')->nullable();
            $table->string('referenteLegale')->nullable();
            $table->string('ragSoc')->nullable();

            $table->boolean('bloccato')->default(false);

            $table->foreignId('idGenere');
            $table->foreignId('idAgente')->nullable();
            $table->foreignId('idCommerciale')->nullable();
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
        Schema::connection('mysql2')->dropIfExists('TT_Anagrafica');
        Schema::dropIfExists('TT_Anagrafica');
    }
}
