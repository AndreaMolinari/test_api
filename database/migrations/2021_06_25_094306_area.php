<?php

use App\Models\TT_TipologiaModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Area extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('TT_Area', function (Blueprint $table) {
            $table->id();

            $table->string('nome');
            $table->string('gruppo');
            $table->json('rawGeoJson')->nullable();

            $table->foreignId('idParent')->nullable()->constrained('TT_Area')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idUtente')->constrained('TT_Utente')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idOperatore');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['nome', 'gruppo', 'idUtente'], 'area_unique');
        });
        Schema::connection('mysql2')->dropIfExists('TT_Area');
        Schema::connection('mysql2')->create('TT_Area', function (Blueprint $table) {
            $table->id();

            $table->string('nome');
            $table->string('gruppo');
            $table->json('rawGeoJson')->nullable();

            $table->foreignId('idParent')->nullable();
            $table->foreignId('idUtente');
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
    public function down()
    {
        Schema::connection('mysql2')->dropIfExists('TT_Area');
        Schema::dropIfExists('TT_Area');
    }
}
