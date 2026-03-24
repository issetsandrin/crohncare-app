<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exames', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nome');
            $table->string('tipo')->nullable();
            $table->dateTime('data');
            $table->string('local')->nullable();
            $table->text('observacoes')->nullable();
            $table->enum('status', ['agendado', 'realizado', 'cancelado'])->default('agendado');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exames');
    }
};
