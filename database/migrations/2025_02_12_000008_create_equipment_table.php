<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
        });

        Schema::create('equipment_conditions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
        });

        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('number')->unique();
            $table->foreignId('equipment_type_id')->nullable()->constrained('equipment_types')->nullOnDelete();
            $table->string('name', 255);
            $table->string('serial_number', 100)->nullable();
            $table->date('production_date')->nullable();
            $table->string('year_of_manufacture', 55)->nullable();
            $table->date('date_accepted_to_accounting')->nullable();
            $table->string('inventory_number', 100)->nullable();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('cabinet_id')->nullable()->constrained('cabinets')->nullOnDelete();
            $table->foreignId('group_id')->nullable()->constrained('groups')->nullOnDelete();
            $table->foreignId('equipment_condition_id')->nullable()->constrained('equipment_conditions')->nullOnDelete();
            $table->string('ru_number', 100)->nullable();
            $table->date('ru_date')->nullable();
            $table->string('grsi', 255)->nullable();
            $table->string('registration_certificate', 100)->nullable();
            $table->string('date_of_registration', 20)->nullable();
            $table->string('valid_until', 20)->nullable();
            $table->string('valid_to', 20)->nullable();
            $table->string('verification_period', 55)->nullable();
            $table->string('last_verification_date', 20)->nullable();
            $table->string('instruction_pdf', 100)->nullable();
            $table->string('registration_certificate_pdf', 100)->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('service_organization_id')->nullable()->constrained('service_organizations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
        Schema::dropIfExists('equipment_conditions');
        Schema::dropIfExists('equipment_types');
    }
};
