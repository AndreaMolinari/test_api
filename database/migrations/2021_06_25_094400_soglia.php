<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Soglia extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TT_Soglia', function (Blueprint $table) {
            $table->id();

            $table->string('inizio')->nullable();
            $table->string('fine')->nullable();

            $table->foreignId('idTipologia')->constrained('TT_Tipologia')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idUtente')->constrained('TT_Utente')->onUpdate('cascade')->onDelete('cascade');

            $table->foreignId('idOperatore');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::connection('mysql2')->dropIfExists('TT_Soglia');
        Schema::connection('mysql2')->create('TT_Soglia', function (Blueprint $table) {
            $table->id();

            $table->string('inizio')->nullable();
            $table->string('fine')->nullable();

            $table->foreignId('idTipologia');
            $table->foreignId('idUtente');

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
    public function down()
    {
        Schema::connection('mysql2')->dropIfExists('TT_Soglia');
        Schema::dropIfExists('TT_Soglia');
    }
}
