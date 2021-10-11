<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFleetsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TT_Flotta', function (Blueprint $table) {
            $table->id();
            $table->boolean('gruppo')->default(false);
            $table->string('nome');
            $table->text('descrizione')->nullable();
            $table->string('defaultIcon')->nullable();

            $table->foreignId('idOperatore')->constrained('TT_Utente');

            $table->timestamps();
            $table->softDeletes();
        });
        Schema::connection('mysql2')->dropIfExists('TT_Flotta');
        Schema::connection('mysql2')->create('TT_Flotta', function (Blueprint $table) {
            $table->id();

            $table->boolean('gruppo')->default(false);
            $table->string('nome');
            $table->text('descrizione')->nullable();
            $table->string('defaultIcon')->nullable();

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
        Schema::connection('mysql2')->dropIfExists('TT_Flotta');
        Schema::dropIfExists('TT_Flotta');
    }
}
