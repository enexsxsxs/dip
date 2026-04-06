<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_request_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
        });

        Schema::create('equipment_request_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
        });

        DB::table('equipment_request_types')->insert([
            ['code' => 'writeoff'],
            ['code' => 'move'],
        ]);

        DB::table('equipment_request_statuses')->insert([
            ['code' => 'pending'],
            ['code' => 'approved'],
            ['code' => 'rejected'],
        ]);

        $pendingId = (int) DB::table('equipment_request_statuses')->where('code', 'pending')->value('id');

        Schema::create('equipment_requests', function (Blueprint $table) use ($pendingId) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('request_type_id')->constrained('equipment_request_types')->restrictOnDelete();
            $table->foreignId('request_status_id')->default($pendingId)->constrained('equipment_request_statuses')->restrictOnDelete();
            $table->foreignId('from_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('to_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->text('comment')->nullable();
            $table->string('photo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_requests');
        Schema::dropIfExists('equipment_request_statuses');
        Schema::dropIfExists('equipment_request_types');
    }
};
