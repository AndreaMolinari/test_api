<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Italycities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('italy_cities', function (Blueprint $table) {
            $table->bigInteger('istat');
            $table->string('comune')->nullable();
            $table->string('regione')->nullable();
            $table->string('provincia')->nullable();
            $table->string('prefisso')->nullable();
            $table->string('cod_fisco')->nullable();
            $table->double('superficie', 15, 8)->nullable();
            $table->bigInteger('num_residenti')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
