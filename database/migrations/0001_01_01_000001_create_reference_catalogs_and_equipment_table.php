<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Справочники и реестр оборудования (3НФ: состояния и типы вынесены в отдельные таблицы, ссылки только по FK).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 155);
            $table->softDeletes();
        });

        Schema::create('cabinets', function (Blueprint $table) {
            $table->id();
            $table->string('number', 55);
            $table->softDeletes();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('contact_info', 255)->nullable();
        });

        Schema::create('service_organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('contact_info', 255)->nullable();
        });

        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
        });

        Schema::create('equipment_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->softDeletes();
        });

        Schema::create('equipment_conditions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
        });

        Schema::create('writeoff_states', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
        });

        Schema::create('utilization_states', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
        });

        DB::table('writeoff_states')->insert([
            ['code' => 'none'],
            ['code' => 'requested'],
            ['code' => 'approved'],
        ]);

        DB::table('utilization_states')->insert([
            ['code' => 'none'],
            ['code' => 'utilized'],
        ]);

        $writeoffNoneId = (int) DB::table('writeoff_states')->where('code', 'none')->value('id');
        $utilNoneId = (int) DB::table('utilization_states')->where('code', 'none')->value('id');

        Schema::create('equipment', function (Blueprint $table) use ($writeoffNoneId, $utilNoneId) {
            $table->id();
            $table->unsignedBigInteger('number');
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
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('service_organization_id')->nullable()->constrained('service_organizations')->nullOnDelete();
            $table->foreignId('writeoff_state_id')->default($writeoffNoneId)->constrained('writeoff_states')->restrictOnDelete();
            $table->foreignId('utilization_state_id')->default($utilNoneId)->constrained('utilization_states')->restrictOnDelete();
            $table->softDeletes();
            $table->index('number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
        Schema::dropIfExists('utilization_states');
        Schema::dropIfExists('writeoff_states');
        Schema::dropIfExists('equipment_conditions');
        Schema::dropIfExists('equipment_types');
        Schema::dropIfExists('groups');
        Schema::dropIfExists('service_organizations');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('cabinets');
        Schema::dropIfExists('departments');
    }
};
