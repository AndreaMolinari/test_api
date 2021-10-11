<?php

use App\Models\TT_CampoAnagraficaModel;
use App\Models\TT_ContattoModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropUselessColumns extends Migration {
    private function up_func(string $connection) {
        Schema::connection($connection)->table('TC_FlottaServizio', function (Blueprint $table) {
            if (Schema::hasColumn('TC_FlottaServizio', 'idGruppo'))
                $table->dropColumn('idGruppo');
        });

        Schema::connection($connection)->table('TT_Tipologia', function (Blueprint $table) {
            if (Schema::hasColumn('TT_Tipologia', 'idUpdated'))
                $table->dropColumn('idUpdated');
            if (Schema::hasColumn('TT_Tipologia', 'deleted'))
                $table->dropColumn('deleted');
        });

        // Setta il nuovo campo che indica se è nascosto
        if (Schema::hasColumn('TT_CampoAnagrafica', 'deleted')) {
            TT_CampoAnagraficaModel::where('deleted', 1)->get()->each(function ($item) {
                $item->delete(); // Setta il deleted_at e li nasconde effetivamente
            });
        }

        if (Schema::hasColumn('TT_Contatto', 'deleted')) {
            TT_ContattoModel::where('deleted', 1)->get()->each(function ($item) {
                $item->delete(); // Setta il deleted_at e li nasconde effetivamente
            });
        }

        Schema::connection($connection)->table('TT_CampoAnagrafica', function (Blueprint $table) {
            if (Schema::hasColumn('TT_CampoAnagrafica', 'deleted'))
                $table->dropColumn('deleted');
        });

        Schema::connection($connection)->table('TT_Flotta', function (Blueprint $table) {
            if (Schema::hasColumn('TT_Flotta', 'gruppo'))
                $table->dropColumn('gruppo');
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        foreach (['mysql', 'mysql2'] as $conn)
            $this->up_func($conn);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        //
    }
}
