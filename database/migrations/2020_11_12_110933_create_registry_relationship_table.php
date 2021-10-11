<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegistryRelationshipTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TC_AnagraficaAnagrafica', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idParent')->constrained('TT_Anagrafica')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idChild')->constrained('TT_Anagrafica')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idTipologia')->constrained('TT_Tipologia')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idOperatore')->constrained('TT_Utente');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::connection('mysql2')->dropIfExists('TC_AnagraficaAnagrafica');
        Schema::connection('mysql2')->create('TC_AnagraficaAnagrafica', function (Blueprint $table) {
            $table->id();

            $table->foreignId('idParent');
            $table->foreignId('idChild');
            $table->foreignId('idTipologia');
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
        Schema::connection('mysql2')->dropIfExists('TC_AnagraficaAnagrafica');
        Schema::dropIfExists('TC_AnagraficaAnagrafica');
    }
}
