<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Макеты PDF-заявок и заполненные заявки (3НФ).
 * — Макет и экземпляр заявки — разные сущности; вариативная форма — в JSON (одно поле на сущность).
 * — Подписанты — FK на users; ответственное подразделение — FK на departments (не «голое» число).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_layout', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->json('schema');
            $table->decimal('scores', 4, 2)->nullable();
            $table->boolean('has_header')->default(false);
            $table->string('type', 32)->default('pdf');
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('user_assigner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('division_assigner_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->json('data');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('request_layout_id')->constrained('request_layout')->cascadeOnDelete();
            $table->decimal('scores', 4, 2)->nullable();
            $table->string('refusal', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requests');
        Schema::dropIfExists('request_layout');
    }
};
