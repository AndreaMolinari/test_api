<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateHistoryTriggers extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        $history_db_name = 'historyrecord';

        $tables = DB::select('SHOW TABLES');
        foreach ($tables as $table)
            foreach ($table as $table_in_db => $table_name) {
                if (!(Str::startsWith(strtoupper($table_name), 'TT_') || Str::startsWith(strtoupper($table_name), 'TC_')))
                    continue;

                $set_statement = "triggered_id=OLD.id, triggered_action='update'";
                $columns = DB::select('show columns from ' . $table_name);
                foreach ($columns as $column) {
                    if ($column->Field == 'id') continue;
                    $set_statement .= ", $column->Field=OLD.$column->Field"; // insert into table (id, val, ...)
                }

                // UPDATE TRIGGER
                DB::unprepared("CREATE TRIGGER {$table_name}Mod BEFORE UPDATE ON {$table_name} FOR EACH ROW INSERT INTO {$history_db_name}.{$table_name} SET {$set_statement}");

                // DELETE TRIGGER
                $set_statement = str_replace("triggered_action='update'", "triggered_action='delete'", $set_statement);
                DB::unprepared("CREATE TRIGGER {$table_name}Del BEFORE DELETE ON {$table_name} FOR EACH ROW INSERT INTO {$history_db_name}.{$table_name} SET {$set_statement}");
            }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        $tables = DB::select('SHOW TABLES');
        foreach ($tables as $table)
            foreach ($table as $table_in_db => $table_name) {
                if (!(Str::startsWith(strtoupper($table_name), 'TT_') || Str::startsWith(strtoupper($table_name), 'TC_')))
                    continue;

                DB::unprepared("DROP TRIGGER IF EXISTS {$table_name}Mod");
                DB::unprepared("DROP TRIGGER IF EXISTS {$table_name}Del");
            }
    }
}
