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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('condominium_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();

            $table->string('item_description')->nullable();

            $table->enum('status', ['pendente', 'entregue', 'devolvido'])->default('pendente');

            $table->timestamp('received_at')->nullable();

            $table->string('delivered_to_name')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('condominium_id')->references('id')->on('condominiums')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
