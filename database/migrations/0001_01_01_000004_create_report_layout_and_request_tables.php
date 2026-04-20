<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Макеты шапок PDF, макеты заявок и заполненные заявки (3НФ).
 * Вариативная форма — JSON; макет шапки — отдельная сущность с FK.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_headers', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->json('schema');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('request_layout', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->json('schema');
            $table->foreignId('document_header_id')->nullable()->constrained('document_headers')->nullOnDelete();
            $table->boolean('has_header')->default(false);
            $table->string('type', 32)->default('pdf');
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('registry_number')->unique();
            $table->json('data');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('request_layout_id')->constrained('request_layout')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requests');
        Schema::dropIfExists('request_layout');
        Schema::dropIfExists('document_headers');
    }
};
