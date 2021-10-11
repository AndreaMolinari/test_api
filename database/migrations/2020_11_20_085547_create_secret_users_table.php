<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSecretUsersTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('TT_SecretUser', function (Blueprint $table) {
            $table->id();

            $table->string('secret', 256)->unique();
            $table->string('description')->nullable();

            $table->foreignId('idUtente')->constrained('TT_Utente')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('idOperatore')->constrained('TT_Utente');

            $table->timestamps();
            $table->timestamp('revoked_at')->nullable();
        });
        Schema::connection('mysql2')->dropIfExists('TT_SecretUser');
        Schema::connection('mysql2')->create('TT_SecretUser', function (Blueprint $table) {
            $table->id();

            $table->string('secret', 256);
            $table->string('description')->nullable();

            $table->foreignId('idUtente');
            $table->foreignId('idOperatore');

            $table->timestamps();
            $table->timestamp('revoked_at')->nullable();
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
    public function down() {
        Schema::connection('mysql2')->dropIfExists('TT_SecretUser');
        Schema::dropIfExists('TT_SecretUser');
    }
}
