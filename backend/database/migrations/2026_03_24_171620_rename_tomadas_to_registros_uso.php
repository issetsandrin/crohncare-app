<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('tomadas', 'registros_uso');
    }

    public function down(): void
    {
        Schema::rename('registros_uso', 'tomadas');
    }
};
