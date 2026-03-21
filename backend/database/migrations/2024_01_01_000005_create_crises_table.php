<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crises', function (Blueprint $table) {
            $table->id();
            $table->dateTime('data_hora');
            $table->text('sintomas');
            $table->integer('intensidade')->comment('1-5');
            $table->string('duracao_estimada')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crises');
    }
};
