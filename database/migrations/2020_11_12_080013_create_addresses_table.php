<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TT_Indirizzo', function (Blueprint $table) {
            $table->id();
            $table->string('istat')->nullable();
            $table->string('nazione')->nullable();
            $table->string('provincia')->nullable();
            $table->string('comune');
            $table->string('cap')->nullable();
            $table->string('via');
            $table->string('civico')->nullable();
            $table->boolean('bloccato')->default(false);

            $table->foreignId('idOperatore')->constrained('TT_Utente');

            $table->timestamps();
            $table->softDeletes();
        });
        Schema::connection('mysql2')->dropIfExists('TT_Indirizzo');
        Schema::connection('mysql2')->create('TT_Indirizzo', function (Blueprint $table) {
                        $table->id();

            $table->string('istat')->nullable();
            $table->string('nazione')->nullable();
            $table->string('provincia')->nullable();
            $table->string('comune');
            $table->string('cap')->nullable();
            $table->string('via');
            $table->string('civico')->nullable();
            $table->boolean('bloccato')->default(false);

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
        Schema::connection('mysql2')->dropIfExists('TT_Indirizzo');
        Schema::dropIfExists('TT_Indirizzo');
    }
}
