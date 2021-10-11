<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Rolestipologia extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('TC_RolesTipologia', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('idTipologia');
            $table->bigInteger('roles');
            
            $table->boolean('bloccato')->default(False);
            $table->bigInteger('idOperatore');
            $table->timestamps();
        });

        Schema::connection('mysql2')->dropIfExists('TC_RolesTipologia');
        Schema::connection('mysql2')->create('TC_RolesTipologia', function (Blueprint $table) {
            $table->bigInteger('id')->unsigned();
            $table->bigInteger('idTipologia')->nullable();
            $table->bigInteger('roles')->nullable();
            
            $table->boolean('deleted')->nullable();
            
            $table->boolean('bloccato')->nullable();
            $table->bigInteger('idOperatore')->nullable();

            $table->timestamps();
            
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
        Schema::dropIfExists('TC_RolesTipologia');
    }
}
