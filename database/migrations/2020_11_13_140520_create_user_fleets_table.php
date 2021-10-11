<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserFleetsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TC_UtenteFlotta', function (Blueprint $table) {
            $table->id();
            $table->string('nickname')->nullable();
            $table->boolean('principale')->default(false);
            $table->boolean('bloccato')->default(false);
            // $table->string('icona')->nullable();

            $table->foreignId('idUtente')->constrained('TT_Utente')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idRiferimento')->constrained('TT_Flotta')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idOperatore')->constrained('TT_Utente');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['idUtente', 'idRiferimento']);
        });
        Schema::connection('mysql2')->dropIfExists('TC_UtenteFlotta');
        Schema::connection('mysql2')->create('TC_UtenteFlotta', function (Blueprint $table) {
            $table->id();

            $table->string('nickname')->nullable();
            $table->boolean('principale')->default(false);
            $table->boolean('bloccato')->default(false);
            // $table->string('icona')->nullable();

            $table->foreignId('idUtente');
            $table->foreignId('idRiferimento');
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
        Schema::connection('mysql2')->dropIfExists('TC_UtenteFlotta');
        Schema::dropIfExists('TC_UtenteFlotta');
    }
}
