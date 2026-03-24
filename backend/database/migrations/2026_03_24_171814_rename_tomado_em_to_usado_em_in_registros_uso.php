<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registros_uso', function (Blueprint $table) {
            $table->renameColumn('tomado_em', 'usado_em');
        });
    }

    public function down(): void
    {
        Schema::table('registros_uso', function (Blueprint $table) {
            $table->renameColumn('usado_em', 'tomado_em');
        });
    }
};
