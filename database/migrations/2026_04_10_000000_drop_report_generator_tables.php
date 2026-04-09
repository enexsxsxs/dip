<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('report_generations');
        Schema::dropIfExists('report_headers');
        Schema::dropIfExists('report_templates');
    }

    public function down(): void
    {
        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code', 80)->index();
            $table->string('title', 255);
            $table->string('format', 10);
            $table->string('file_path');
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_active')->default(true)->index();
            $table->text('notes')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('report_headers', function (Blueprint $table) {
            $table->id();
            $table->string('report_code', 80)->index();
            $table->string('title', 255);
            $table->json('payload')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('report_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_template_id')->constrained('report_templates')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('equipment_id')->nullable()->constrained('equipment')->nullOnDelete();
            $table->foreignId('equipment_request_id')->nullable()->constrained('equipment_requests')->nullOnDelete();
            $table->json('input_data')->nullable();
            $table->string('output_path');
            $table->string('output_name');
            $table->string('status', 32)->default('generated')->index();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });
    }
};
