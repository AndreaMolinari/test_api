<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactBooksTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        
        Schema::create('TT_Contatto', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->text('descrizione')->nullable();
            $table->boolean('predefinito')->default(false);
            $table->string('contatto')->nullable();
            $table->boolean('bloccato')->default(false);

            $table->foreignId('idAnagrafica')->nullable()->constrained('TT_Anagrafica')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idTipologia')->nullable()->constrained('TT_Tipologia')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idParent')->nullable()->constrained('TT_Contatto')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idUtente')->nullable()->constrained('TT_Utente')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idOperatore')->constrained('TT_Utente');

            $table->timestamps();
            $table->softDeletes();
        });
        Schema::connection('mysql2')->dropIfExists('TT_Contatto');
        Schema::connection('mysql2')->create('TT_Contatto', function (Blueprint $table) {
            $table->id();

            $table->string('nome')->nullable();
            $table->text('descrizione')->nullable();
            $table->boolean('predefinito')->nullable();
            $table->boolean('bloccato')->nullable();
            $table->string('contatto')->nullable();

            $table->foreignId('idAnagrafica')->nullable();
            $table->foreignId('idTipologia')->nullable();
            $table->foreignId('idParent')->nullable();
            $table->foreignId('idUtente')->nullable();
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
        Schema::connection('mysql2')->dropIfExists('TT_Contatto');
        Schema::dropIfExists('TT_Contatto');
    }
}
