<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Типы и файлы документов по оборудованию (3НФ: тип документа — отдельная сущность).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_document_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
        });

        foreach ([
            'instruction',
            'registration_certificate',
            'commissioning_act',
            'ru_scan',
            'utilization_act',
            'signed_report_act',
        ] as $code) {
            DB::table('equipment_document_types')->insert(['code' => $code]);
        }

        Schema::create('equipment_documents', function (Blueprint $table) {
            $table->id();
            $table->string('document', 255);
            $table->string('name', 255);
            $table->foreignId('document_type_id')->constrained('equipment_document_types')->restrictOnDelete();
            $table->dateTime('uploaded_at');
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
        });

        Schema::create('equipment_images', function (Blueprint $table) {
            $table->id();
            $table->string('image', 255);
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_images');
        Schema::dropIfExists('equipment_documents');
        Schema::dropIfExists('equipment_document_types');
    }
};
