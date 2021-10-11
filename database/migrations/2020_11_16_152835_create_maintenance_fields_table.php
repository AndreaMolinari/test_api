<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaintenanceFieldsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TT_CampoAnagrafica', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->text('descrizione')->nullable();
            $table->boolean('deleted')->nullable(); //? Became deleted_at is null

            $table->foreignId('idAnagrafica')->constrained('TT_Anagrafica')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idTipologia')->constrained('TT_Tipologia')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idOperatore')->constrained('TT_Utente');

            $table->timestamps();
            $table->softDeletes();
        });
        Schema::connection('mysql2')->dropIfExists('TT_CampoAnagrafica');
        Schema::connection('mysql2')->create('TT_CampoAnagrafica', function (Blueprint $table) {
            $table->id();

            $table->string('nome')->nullable();
            $table->text('descrizione')->nullable();
            $table->boolean('deleted')->nullable(); //? Became deleted_at is null

            $table->foreignId('idAnagrafica');
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
        Schema::connection('mysql2')->dropIfExists('TT_CampoAnagrafica');
        Schema::dropIfExists('TT_CampoAnagrafica');
    }
}
