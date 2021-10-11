<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShippingDetailsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TC_DDTComponente', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shippable_id'); // Componente o Sim
            $table->string('shippable_type'); // Laravel App\Models\Model
            $table->double('prezzo')->nullable();
            $table->foreignId('idDDT')->constrained('TT_DDT')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idOperatore')->constrained('TT_Utente');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['shippable_id', 'shippable_type', 'idDDT'], 'TC_DDTComponente_unique');
        });
        Schema::connection('mysql2')->dropIfExists('TC_DDTComponente');
        Schema::connection('mysql2')->create('TC_DDTComponente', function (Blueprint $table) {
            $table->id();


            $table->foreignId('shippable_id');
            $table->string('shippable_type'); // Laravel App\Models\Model
            $table->double('prezzo')->nullable();
            $table->foreignId('idDDT');
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
        Schema::connection('mysql2')->dropIfExists('TC_DDTComponente');
        Schema::dropIfExists('TC_DDTComponente');
    }
}
