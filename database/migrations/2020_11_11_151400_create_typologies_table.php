<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypologiesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TT_Tipologia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idParent')->nullable()->constrained('TT_Tipologia')->onUpdate('cascade')->onDelete('cascade');

            $table->string('tipologia');
            $table->text('descrizione')->nullable();
            $table->json('data')->nullable();
            $table->boolean('bloccato')->default(false);
            $table->boolean('deleted')->default(false);
            // $table->boolean('livello')->nullable();

            $table->foreignId('idOperatore')->nullable()->constrained('TT_Utente')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idUpdated')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tipologia', 'idParent']); // stesso nome e stesso padre NO
        });
        Schema::table('TT_Utente', function (Blueprint $table) {
            $table->foreign('idTipologia')->on('TT_Tipologia')->references('id')->onUpdate('cascade')->onDelete('cascade');
        });
        Schema::connection('mysql2')->dropIfExists('TT_Tipologia');
        Schema::connection('mysql2')->create('TT_Tipologia', function (Blueprint $table) {
            $table->id();

            $table->foreignId('idParent')->nullable();

            $table->string('tipologia');
            $table->text('descrizione')->nullable();
            $table->json('data')->nullable();
            $table->boolean('bloccato')->default(false);
            $table->boolean('deleted')->default(false);
            // $table->boolean('livello')->nullable();

            $table->foreignId('idOperatore')->nullable();
            $table->foreignId('idUpdated')->nullable();

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
        Schema::connection('mysql2')->dropIfExists('TT_Tipologia');
        Schema::dropIfExists('TT_Tipologia');
    }
}
