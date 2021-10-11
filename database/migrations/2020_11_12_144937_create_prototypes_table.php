<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrototypesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TT_Modello', function (Blueprint $table) {
            $table->id();
            $table->string('modello');
            $table->boolean('batteria')->default(false)->nullable();

            $table->foreignId('idBrand')->constrained('TT_Brand')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idTipologia')->constrained('TT_Tipologia')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idOperatore')->constrained('TT_Utente');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['modello', 'idBrand', 'idTipologia']);
        });
        Schema::connection('mysql2')->dropIfExists('TT_Modello');
        Schema::connection('mysql2')->create('TT_Modello', function (Blueprint $table) {
            $table->id();

            $table->string('modello');
            $table->boolean('batteria')->default(false)->nullable();

            $table->foreignId('idBrand');
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
        Schema::connection('mysql2')->dropIfExists('TT_Modello');
        Schema::dropIfExists('TT_Modello');
    }
}
