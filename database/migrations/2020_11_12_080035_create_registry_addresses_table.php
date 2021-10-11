<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegistryAddressesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TC_AnagraficaIndirizzo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idAnagrafica')->constrained('TT_Anagrafica')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idIndirizzo')->constrained('TT_Indirizzo')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idTipologia')->constrained('TT_Tipologia')->onUpdate('cascade')->onDelete('cascade');
            $table->text('descrizione')->nullable();
            $table->boolean('predefinito')->default(false);
            $table->foreignId('idOperatore')->constrained('TT_Utente');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::connection('mysql2')->dropIfExists('TC_AnagraficaIndirizzo');
        Schema::connection('mysql2')->create('TC_AnagraficaIndirizzo', function (Blueprint $table) {
                        $table->id();

            $table->foreignId('idAnagrafica');
            $table->foreignId('idIndirizzo');
            $table->foreignId('idTipologia');
            $table->text('descrizione')->nullable();
            $table->boolean('predefinito')->default(false);
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
        Schema::connection('mysql2')->dropIfExists('TC_AnagraficaIndirizzo');
        Schema::dropIfExists('TC_AnagraficaIndirizzo');
    }
}
