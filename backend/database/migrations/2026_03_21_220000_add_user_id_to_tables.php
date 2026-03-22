<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $firstUserId = DB::table('users')->orderBy('id')->value('id');

        // Medicamentos
        Schema::table('medicamentos', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->after('id')->nullable();
        });
        if ($firstUserId) {
            DB::table('medicamentos')->whereNull('user_id')->update(['user_id' => $firstUserId]);
        }
        Schema::table('medicamentos', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        // Diarios
        Schema::table('diarios', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->after('id')->nullable();
        });
        if ($firstUserId) {
            DB::table('diarios')->whereNull('user_id')->update(['user_id' => $firstUserId]);
        }
        Schema::table('diarios', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        // Crises
        Schema::table('crises', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->after('id')->nullable();
        });
        if ($firstUserId) {
            DB::table('crises')->whereNull('user_id')->update(['user_id' => $firstUserId]);
        }
        Schema::table('crises', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('medicamentos', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('diarios', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('crises', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
