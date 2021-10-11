<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateHistoryDB extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $list = DB::select(DB::raw("SHOW DATABASES WHERE `Database` LIKE 'historyrecord'"));
        if( count($list) !== 1 ){
            Schema::createDatabase('historyrecord');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropDatabaseIfExists('historyrecord');
    }
}
