<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perfil_crohn', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('tipo_doenca', ['crohn', 'retocolite', 'indeterminada', 'nao_sei'])->nullable();
            $table->integer('ano_diagnostico')->nullable();
            $table->enum('localizacao', ['ileal', 'colonico', 'ileocolonico', 'trato_superior', 'nao_sei'])->nullable();
            $table->enum('situacao_atual', ['remissao', 'crise_leve', 'crise_moderada', 'crise_grave', 'recente'])->nullable();
            $table->boolean('tem_acompanhamento')->nullable();
            $table->string('frequencia_consultas')->nullable();
            $table->json('objetivos')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('onboarding_completo')->default(false)->after('password');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perfil_crohn');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('onboarding_completo');
        });
    }
};
