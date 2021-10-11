<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComponentDriversTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TC_AutistaComponente', function (Blueprint $table) {
            $table->id();

            $table->foreignId('idAutista')->constrained('TT_Autista')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idComponente')->constrained('TT_Componente')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idOperatore')->constrained('TT_Utente');

            $table->timestamps();
            $table->softDeletes();
        });
        Schema::connection('mysql2')->dropIfExists('TC_AutistaComponente');
        Schema::connection('mysql2')->create('TC_AutistaComponente', function (Blueprint $table) {
            $table->id();

            $table->foreignId('idAutista');
            $table->foreignId('idComponente');
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
        Schema::connection('mysql2')->dropIfExists('TC_AutistaComponente');
        Schema::dropIfExists('TC_AutistaComponente');
    }
}
