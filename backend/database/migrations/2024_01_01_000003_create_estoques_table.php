<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estoques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicamento_id')->unique()->constrained()->cascadeOnDelete();
            $table->integer('quantidade_atual')->default(0);
            $table->integer('dose_diaria')->default(1);
            $table->date('data_ultimo_reabastecimento')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estoques');
    }
};
