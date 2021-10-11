<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RenameTargetGeojsonColumn extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('TT_Area', function (Blueprint $table) {
            $table->renameColumn('rawGeoJson', 'geo_json');
        });
        Schema::connection('mysql2')->table('TT_Area', function (Blueprint $table) {
            $table->renameColumn('rawGeoJson', 'geo_json');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::connection('mysql2')->table('TT_Area', function (Blueprint $table) {
            $table->renameColumn('geo_json', 'rawGeoJson');
        });
        Schema::table('TT_Area', function (Blueprint $table) {
            $table->renameColumn('geo_json', 'rawGeoJson');
        });
    }
}
