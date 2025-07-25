<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('last_viewed_condominium_id')->nullable()->after('last_login_at');

            $table->foreign('last_viewed_condominium_id')
                ->references('id')->on('condominiums')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['last_viewed_condominium_id']);
            $table->dropColumn('last_viewed_condominium_id');
        });
    }
};
