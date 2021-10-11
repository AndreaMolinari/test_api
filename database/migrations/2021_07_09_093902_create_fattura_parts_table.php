<?php

use App\Models\v5\Fattura;
use App\Models\v5\Utente;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFatturaPartsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TC_FatturaPart', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Fattura::class, 'idFattura')->constrained('TT_Fattura')->cascadeOnUpdate()->cascadeOnDelete();
            $table->morphs('billable');

            $table->double('prezzoUnitario')->default(0);
            $table->integer('sconto')->default(0);
            $table->integer('iva')->default(22);

            $table->foreignIdFor(Utente::class, 'idOperatore')->constrained('TT_Utente')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::connection('mysql2')->dropIfExists('TC_FatturaPart');
        Schema::connection('mysql2')->create('TC_FatturaPart', function (Blueprint $table) {
            $table->id();

            $table->foreignId('idFattura');
            $table->morphs('billable');

            $table->double('prezzoUnitario')->default(0);
            $table->integer('sconto')->default(0);
            $table->integer('iva')->default(22);

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
    public function down() {
        Schema::connection('mysql2')->dropIfExists('TC_FatturaPart');
        Schema::dropIfExists('TC_FatturaPart');
    }
}
