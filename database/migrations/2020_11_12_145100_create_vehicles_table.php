<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehiclesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TT_Mezzo', function (Blueprint $table) {
            $table->id();
            $table->string('targa')->unique()->nullable(); //? mutuamente esclusivo
            $table->string('telaio')->unique()->nullable();
            $table->string('colore')->nullable();
            $table->year('anno')->nullable();
            $table->text('info')->nullable();
            $table->boolean('bloccato')->default(false);
            $table->integer('km_totali')->nullable();
            $table->integer('ore_totali')->nullable();

            $table->foreignId('idModello')->constrained('TT_Modello')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idOperatore')->constrained('TT_Utente');

            $table->timestamps();
            $table->softDeletes();
        });
        Schema::connection('mysql2')->dropIfExists('TT_Mezzo');
        Schema::connection('mysql2')->create('TT_Mezzo', function (Blueprint $table) {
            $table->id();

            $table->string('targa')->nullable(); //? mutuamente esclusivo
            $table->string('telaio')->nullable();
            $table->string('colore')->nullable();
            $table->year('anno')->nullable();
            $table->text('info')->nullable();
            $table->boolean('bloccato')->default(false);
            $table->integer('km_totali')->nullable();
            $table->integer('ore_totali')->nullable();

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
        Schema::connection('mysql2')->dropIfExists('TT_Mezzo');
        Schema::dropIfExists('TT_Mezzo');
    }
}
