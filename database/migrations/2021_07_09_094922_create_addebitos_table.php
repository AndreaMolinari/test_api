<?php

use App\Models\v5\Anagrafica;
use App\Models\v5\Utente;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddebitosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('TT_Addebito', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Anagrafica::class, 'idAnagrafica')->constrained('TT_Anagrafica')->cascadeOnUpdate()->cascadeOnDelete();

            $table->longText('descrizione')->nullable();

            $table->double('prezzoUnitario')->default(0);
            $table->integer('sconto')->default(0);
            $table->integer('iva')->default(22);

            $table->foreignIdFor(Utente::class, 'idOperatore')->constrained('TT_Utente')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::connection('mysql2')->dropIfExists('TT_Addebito');
        Schema::connection('mysql2')->create('TT_Addebito', function (Blueprint $table) {
            $table->id();

            $table->foreignId('idAnagrafica');
            $table->foreignId('idOperatore');

            $table->longText('descrizione')->nullable();

            $table->double('prezzoUnitario');
            $table->integer('sconto');
            $table->integer('iva');

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
        Schema::connection('mysql2')->dropIfExists('TT_Addebito');
        Schema::dropIfExists('TT_Addebito');
    }
}
