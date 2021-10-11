<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TT_Documento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reference_id');   // foreignId() usato solo come alias,
            // o si fa una foreign per
            // ogni tabella documentabile o
            // lo si lascia senza e basta
            $table->string('reference_table'); // Laravel App\Models\Model
            $table->string('seriale');
            $table->string('percorso_allegato')->nullable();
            $table->text('descrizione')->nullable();
            $table->date('dataScadenza')->nullable();
            $table->boolean('rinnovo')->default(false);

            $table->foreignId('idTipologia')->constrained('TT_Tipologia')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idOperatore')->constrained('TT_Utente');

            $table->timestamps();
            $table->softDeletes();
        });
        Schema::connection('mysql2')->dropIfExists('TT_Documento');
        Schema::connection('mysql2')->create('TT_Documento', function (Blueprint $table) {
            $table->id();

            $table->foreignId('reference_id');   // foreignId() usato solo come alias,
            // o si fa una foreign per
            // ogni tabella documentabile o
            // lo si lascia senza e basta
            $table->string('reference_table'); // Laravel App\Models\Model
            $table->string('seriale');
            $table->string('percorso_allegato')->nullable();
            $table->text('descrizione')->nullable();
            $table->date('dataScadenza')->nullable();
            $table->boolean('rinnovo')->default(false);

            $table->foreignId('idTipologia');
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
        Schema::connection('mysql2')->dropIfExists('TT_Documento');
        Schema::dropIfExists('TT_Documento');
    }
}
