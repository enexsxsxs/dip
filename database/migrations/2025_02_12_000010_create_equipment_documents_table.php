<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_documents', function (Blueprint $table) {
            $table->id();
            $table->string('document', 100);
            $table->string('name', 255);
            $table->string('type', 20)->nullable();
            $table->dateTime('uploaded_at');
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_documents');
    }
};
