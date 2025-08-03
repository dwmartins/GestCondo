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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('position', [
                'porteiro',
                'zelador',
                'faxineiro',
                'jardineiro',
                'piscineiro',
                'eletricista',
                'encanador',
                'pedreiro',
                'vigia',
                'seguranca',
                'administrativo',
                'recepcionista',
                'assistente_sindico',
                'tesoureiro',
                'tecnico_elevador',
                'tecnico_ar',
                'supervisor'
            ]);
            $table->json('schedule')->nullable();
            $table->date('admission_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->enum('status', ['active', 'on_leave', 'fired', 'suspended', 'vacation']);
            $table->text('observations')->nullable();
            $table->timestamps();

            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
