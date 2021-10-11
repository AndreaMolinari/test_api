<?php

use App\Models\Targets\TT_AreaModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UtenteTargetForeign extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('TT_Utente', fn (Blueprint $table) => $table
            ->foreignId('idArea')
            ->nullable()
            ->constrained('TT_Area')
            ->cascadeOnUpdate()
            ->cascadeOnDelete()
        );
        Schema::connection('mysql2')->table('TT_Utente', fn (Blueprint $table) => $table
            ->foreignId('idArea')
            ->nullable()
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql2')->table('TT_Utente', fn (Blueprint $table) => $table
            ->dropConstrainedForeignId('idArea')
        );
        Schema::table('TT_Utente', fn (Blueprint $table) => $table
            ->dropConstrainedForeignId('idArea')
        );
    }
}
