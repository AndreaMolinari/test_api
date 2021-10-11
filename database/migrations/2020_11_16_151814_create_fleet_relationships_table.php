<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFleetRelationshipsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        return;
        Schema::create('TC_FlottaFlotta', function (Blueprint $table) {
            $table->id();

            $table->foreignId('idParent')->constrained('TT_Flotta')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idChild')->constrained('TT_Flotta')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idOperatore')->constrained('TT_Utente');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['idParent', 'idChild']);
        });
        Schema::connection('mysql2')->dropIfExists('TC_FlottaFlotta');
        Schema::connection('mysql2')->create('TC_FlottaFlotta', function (Blueprint $table) {
            $table->id();


            $table->foreignId('idParent');
            $table->foreignId('idChild');
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
        return;
        Schema::connection('mysql2')->dropIfExists('TC_FlottaFlotta');
        Schema::dropIfExists('TC_FlottaFlotta');
    }
}
