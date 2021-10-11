<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBrandsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TT_Brand', function (Blueprint $table) {
            $table->id();
            $table->string('marca')->unique();
            $table->boolean('bloccato')->default(false);

            $table->foreignId('idFornitore')->nullable()->constrained('TT_Anagrafica')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idOperatore')->constrained('TT_Utente');

            $table->timestamps();
            $table->softDeletes();
        });
        Schema::connection('mysql2')->dropIfExists('TT_Brand');
        Schema::connection('mysql2')->create('TT_Brand', function (Blueprint $table) {
            $table->id();

            $table->string('marca');
            $table->boolean('bloccato')->default(false);

            $table->foreignId('idFornitore')->nullable();
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
        Schema::connection('mysql2')->dropIfExists('TT_Brand');
        Schema::dropIfExists('TT_Brand');
    }
}
