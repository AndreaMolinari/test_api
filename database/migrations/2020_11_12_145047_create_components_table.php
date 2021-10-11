<?php

use App\Models\v5\Firmware;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComponentsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TT_Componente', function (Blueprint $table) {
            $table->id();
            $table->string('unitcode', 20)->unique();
            $table->string('imei')->nullable();
            $table->boolean('bloccato')->default(false);

            $table->foreignIdFor(Firmware::class, 'idFirmware')->nullable()->constrained('TT_Firmware')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('idSim')->nullable()->constrained('TT_Sim')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idModello')->constrained('TT_Modello')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idOperatore')->constrained('TT_Utente');

            $table->timestamps();
            $table->softDeletes();
        });
        Schema::connection('mysql2')->dropIfExists('TT_Componente');
        Schema::connection('mysql2')->create('TT_Componente', function (Blueprint $table) {
            $table->id();

            $table->string('unitcode', 20);
            $table->string('imei')->nullable();
            $table->boolean('bloccato')->default(false);

            $table->foreignId('idFirmware')->nullable();
            $table->foreignId('idSim')->nullable();
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
        Schema::connection('mysql2')->dropIfExists('TT_Componente');
        Schema::dropIfExists('TT_Componente');
    }
}
