<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnnotationsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TT_Annotazione', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idRiferimento');  // foreignId() usato solo come alias,
            // o si fa una foreign per
            // ogni tabella annotabile o
            // lo si lascia senza e basta
            $table->string('tabella'); // Laravel App\Models\Model
            $table->text('testo');
            $table->foreignId('idOperatore')->constrained('TT_Utente');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::connection('mysql2')->dropIfExists('TT_Annotazione');
        Schema::connection('mysql2')->create('TT_Annotazione', function (Blueprint $table) {
            $table->id();

            $table->foreignId('idRiferimento');  // foreignId() usato solo come alias,
            // o si fa una foreign per
            // ogni tabella annotabile o
            // lo si lascia senza e basta
            $table->string('tabella'); // Laravel App\Models\Model
            $table->text('testo');
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
        Schema::connection('mysql2')->dropIfExists('TT_Annotazione');
        Schema::dropIfExists('TT_Annotazione');
    }
}
