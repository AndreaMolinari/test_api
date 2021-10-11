<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriversTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TT_Autista', function (Blueprint $table) {
            $table->id();

            $table->string('autista');

            $table->foreignId('idAnagrafica')->constrained('TT_Anagrafica')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idOperatore')->constrained('TT_Utente');

            $table->timestamps();
            $table->softDeletes();
        });
        Schema::connection('mysql2')->dropIfExists('TT_Autista');
        Schema::connection('mysql2')->create('TT_Autista', function (Blueprint $table) {
            $table->id();


            $table->string('autista');
            $table->foreignId('idAnagrafica');
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
        Schema::connection('mysql2')->dropIfExists('TT_Autista');
        Schema::dropIfExists('TT_Autista');
    }
}
