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
            $table->foreignId('condominium_id')
                ->nullable()
                ->after('id')
                ->constrained('condominiums')
                ->onDelete('set null');

            $table->index('condominium_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['condominium_id']);
            $table->dropForeign(['condominium_id']);
            $table->dropColumn('condominium_id');
        });
    }
};
