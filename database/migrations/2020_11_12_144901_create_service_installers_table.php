<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceInstallersTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TC_ServizioInstallatore', function (Blueprint $table) {
            $table->id();
            $table->text('descrizione')->nullable();
            $table->date('dataInstallazione')->nullable();
            $table->boolean('bloccato')->default(false);

            $table->foreignId('idAnagrafica')->nullable()->constrained('TT_Anagrafica')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idServizio')->constrained('TT_Servizio')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idOperatore')->constrained('TT_Utente');

            $table->timestamps();
            $table->softDeletes();
        });
        Schema::connection('mysql2')->dropIfExists('TC_ServizioInstallatore');
        Schema::connection('mysql2')->create('TC_ServizioInstallatore', function (Blueprint $table) {
            $table->id();

            $table->text('descrizione')->nullable();
            $table->date('dataInstallazione')->nullable();
            $table->boolean('bloccato')->default(false);

            $table->foreignId('idAnagrafica')->nullable();
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
        Schema::connection('mysql2')->dropIfExists('TC_ServizioInstallatore');
        Schema::dropIfExists('TC_ServizioInstallatore');
    }
}
