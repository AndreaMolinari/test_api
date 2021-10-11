<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegistryInvoicesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TT_AnagraficaFatturazione', function (Blueprint $table) {
            $table->id();

            $table->string('sdi', 20)->nullable();
            $table->boolean('splitPA')->default(False);
            $table->boolean('esenteIVA')->default(False);
            $table->boolean('speseIncasso')->default(False); // spese di incasso
            $table->boolean('speseSpedizione')->default(False);
            $table->string('banca', 160)->nullable();
            $table->string('filiale', 160)->nullable();
            $table->string('iban', 160)->nullable();
            $table->string('iban_abi', 160)->nullable();
            $table->string('iban_cab', 160)->nullable();
            $table->string('iban_cin', 160)->nullable();
            $table->string('pec', 160)->nullable();
            $table->string('mail', 160)->nullable();
            $table->boolean('bloccato')->default(False);

            $table->foreignId('idAnagrafica')->constrained('TT_Anagrafica')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idModalita')->constrained('TT_Tipologia')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idPeriodo')->constrained('TT_Tipologia')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idOperatore')->constrained('TT_Utente');

            $table->timestamps();
            $table->softDeletes();
        });
        Schema::connection('mysql2')->dropIfExists('TT_AnagraficaFatturazione');
        Schema::connection('mysql2')->create('TT_AnagraficaFatturazione', function (Blueprint $table) {
            $table->id();


            $table->string('sdi', 20)->nullable();
            $table->boolean('splitPA')->default(False);
            $table->boolean('esenteIVA')->default(False);
            $table->boolean('speseIncasso')->default(False); // spese di incasso
            $table->boolean('speseSpedizione')->default(False);
            $table->string('banca', 160)->nullable();
            $table->string('filiale', 160)->nullable();
            $table->string('iban', 160)->nullable();
            $table->string('iban_abi', 160)->nullable();
            $table->string('iban_cab', 160)->nullable();
            $table->string('iban_cin', 160)->nullable();
            $table->string('pec', 160)->nullable();
            $table->string('mail', 160)->nullable();
            $table->boolean('bloccato')->default(False);

            $table->foreignId('idAnagrafica');
            $table->foreignId('idModalita');
            $table->foreignId('idPeriodo');
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
        Schema::connection('mysql2')->dropIfExists('TT_AnagraficaFatturazione');
        Schema::dropIfExists('TT_AnagraficaFatturazione');
    }
}
