<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Notifica extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TT_Notifica', function (Blueprint $table) {
            $table->id();

            $table->boolean('usaEmailUtente')->default(0);
            $table->string('messaggioCustom')->nullable();
            $table->foreignId('idUtente')->constrained('TT_Utente')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idOperatore')->constrained('TT_Utente');

            $table->timestamps();
        });
        Schema::connection('mysql2')->dropIfExists('TT_Notifica');
        Schema::connection('mysql2')->create('TT_Notifica', function (Blueprint $table) {
            $table->id();

            $table->boolean('usaEmailUtente')->default(0);
            $table->string('messaggioCustom')->nullable();
            $table->foreignId('idUtente');
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
        Schema::connection('mysql2')->dropIfExists('TT_Notifica');
        Schema::dropIfExists('TT_Notifica');
    }
}
