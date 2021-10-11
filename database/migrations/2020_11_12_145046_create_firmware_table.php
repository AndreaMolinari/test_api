<?php

use App\Models\v5\Modello;
use App\Models\v5\Utente;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFirmwareTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('TT_Firmware', function (Blueprint $table) {
            $table->id();

            $table->string('version');
            $table->string('firmware_path')->nullable();

            $table->foreignIdFor(Modello::class, 'idModello')->constrained('TT_Modello')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignIdFor(Utente::class, 'idOperatore')->constrained('TT_Utente')->cascadeOnUpdate()->cascadeOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['idModello', 'version']);
        });
        Schema::connection('mysql2')->dropIfExists('TT_Firmware');
        Schema::connection('mysql2')->create('TT_Firmware', function (Blueprint $table) {
            $table->id();
            
            $table->string('version')->nullable();
            $table->string('firmware_path')->nullable();
            $table->foreignId('idOperatore');
            $table->foreignId('idModello');

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
        Schema::connection('mysql2')->dropIfExists('TT_Firmware');
        Schema::dropIfExists('TT_Firmware');
    }
}
