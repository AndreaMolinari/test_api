<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PersonalizzazioniRivenditore extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TT_PersonalizzazioniRivenditore', function (Blueprint $table) {
            $table->id();

            $table->foreignId('idAnagrafica')->constrained('TT_Anagrafica')->onUpdate('cascade')->onDelete('cascade');
            $table->string('colorGest')->nullable();
            $table->json('mapAvail')->nullable();
            $table->string('logoPath')->nullable();
            $table->binary('logoData')->nullable();
            $table->string('platformUrl')->nullable();
            $table->foreignId('idOperatore');

            $table->timestamps();
        });
        Schema::connection('mysql2')->dropIfExists('TT_PersonalizzazioniRivenditore');
        Schema::connection('mysql2')->create('TT_PersonalizzazioniRivenditore', function (Blueprint $table) {
            $table->id();

            $table->foreignId('idAnagrafica');
            $table->string('colorGest')->nullable();
            $table->json('mapAvail')->nullable();
            $table->string('logoPath')->nullable();
            $table->binary('logoData')->nullable();
            $table->string('platformUrl')->nullable();
            $table->foreignId('idOperatore');

            $table->timestamps();

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
        Schema::connection('mysql2')->dropIfExists('TT_PersonalizzazioniRivenditore');
        Schema::dropIfExists('TT_PersonalizzazioniRivenditore');
    }
}
