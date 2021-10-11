<?php

use App\Models\v5\Fattura;
use App\Models\v5\Anagrafica;
use App\Models\v5\Utente;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFatturasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('TT_Fattura', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('numero')->unsigned();
            $table->year('anno');
            $table->boolean('manuale')->default(false);

            $table->foreignIdFor(Anagrafica::class, 'idAnagrafica')->constrained('TT_Anagrafica')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignIdFor(Fattura::class, 'idNotaCredito')->nullable()->constrained('TT_Fattura')->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreignIdFor(Utente::class, 'idOperatore')->constrained('TT_Utente')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::connection('mysql2')->dropIfExists('TT_Fattura');
        Schema::connection('mysql2')->create('TT_Fattura', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('numero')->unsigned();
            $table->year('anno');
            $table->boolean('manuale')->nullable();

            $table->foreignId('idAnagrafica');
            $table->foreignId('idNotaCredito')->nullable();
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
        Schema::connection('mysql2')->dropIfExists('TT_Fattura');
        Schema::dropIfExists('TT_Fattura');
    }
}
