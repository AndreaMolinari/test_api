<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('TT_Utente', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('password_dec')->nullable();
            $table->string('actiaMail')->nullable();
            $table->string('actiaUser')->nullable();
            $table->string('actiaPassword')->nullable();

            $table->datetime('data_inizio')->nullable();
            $table->datetime('data_fine')->nullable();

            $table->boolean('bloccato')->default(false);
            $table->rememberToken();

            $table->foreignId('idTipologia'); //? Foreign Key created in typology
            $table->foreignId('idAnagrafica')->nullable(); //? Foreign Key created in registry
            $table->foreignId('idParent')->nullable()->constrained('TT_Utente')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idOperatore')->constrained('TT_Utente'); // Nullable perchè il primo utente non lo crea nessuno

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::connection('mysql2')->dropIfExists('TT_Utente');
        Schema::connection('mysql2')->create('TT_Utente', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->string('username');
            $table->string('password');
            $table->string('password_dec')->nullable();
            $table->string('actiaMail')->nullable();
            $table->string('actiaUser')->nullable();
            $table->string('actiaPassword')->nullable();
            
            $table->datetime('data_inizio')->nullable();
            $table->datetime('data_fine')->nullable();
            
            $table->boolean('bloccato')->default(false);
            
            $table->rememberToken();

            $table->foreignId('idTipologia'); //? Foreign Key created in typology
            $table->foreignId('idAnagrafica')->nullable(); //? Foreign Key created in registry
            $table->foreignId('idParent')->nullable();
            $table->foreignId('idOperatore')->nullable(); // Nullable perchè il primo utente non lo crea nessuno

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
    public function down()
    {
        Schema::connection('mysql2')->dropIfExists('TT_Utente');
        Schema::dropIfExists('TT_Utente');
    }
}
