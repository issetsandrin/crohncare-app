<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicamentos', function (Blueprint $table) {
            $table->string('periodicidade_tipo')->default('diario')->after('instrucoes');
            $table->json('periodicidade_valor')->nullable()->after('periodicidade_tipo');
        });
    }

    public function down(): void
    {
        Schema::table('medicamentos', function (Blueprint $table) {
            $table->dropColumn(['periodicidade_tipo', 'periodicidade_valor']);
        });
    }
};
