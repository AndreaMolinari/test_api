<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSimsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TT_Sim', function (Blueprint $table) {
            $table->id();
            $table->string('serial');
            $table->string('apn')->nullable();
            $table->string('dataAttivazione')->nullable();
            $table->string('dataDisattivazione')->nullable();
            $table->boolean('bloccato')->default(false);

            $table->foreignId('idModello')->constrained('TT_Modello')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idOperatore')->constrained('TT_Utente');

            $table->timestamps();
            $table->softDeletes();
        });
        Schema::connection('mysql2')->dropIfExists('TT_Sim');
        Schema::connection('mysql2')->create('TT_Sim', function (Blueprint $table) {
            $table->id();

            $table->string('serial');
            $table->string('apn')->nullable();
            $table->string('dataAttivazione')->nullable();
            $table->string('dataDisattivazione')->nullable();
            $table->boolean('bloccato')->default(false);

            $table->foreignId('idModello');
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
        Schema::connection('mysql2')->dropIfExists('sims');
        Schema::dropIfExists('sims');
    }
}
