<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NotificaContatto extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TC_NotificaContatto', function (Blueprint $table) {
            $table->id();

            $table->foreignId('idNotifica')->constrained('TT_Notifica')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idContatto')->constrained('TT_Contatto')->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
        });
        Schema::connection('mysql2')->dropIfExists('TC_NotificaContatto');
        Schema::connection('mysql2')->create('TC_NotificaContatto', function (Blueprint $table) {
            $table->id();

            $table->foreignId('idNotifica');
            $table->foreignId('idContatto');

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
        Schema::connection('mysql2')->dropIfExists('TC_NotificaContatto');
        Schema::dropIfExists('TC_NotificaContatto');
    }
}
