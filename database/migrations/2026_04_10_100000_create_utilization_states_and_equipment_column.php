<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utilization_states', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
        });

        DB::table('utilization_states')->insert([
            ['code' => 'none'],
            ['code' => 'utilized'],
        ]);

        $noneId = (int) DB::table('utilization_states')->where('code', 'none')->value('id');

        Schema::table('equipment', function (Blueprint $table) use ($noneId) {
            $table->foreignId('utilization_state_id')
                ->after('writeoff_state_id')
                ->default($noneId)
                ->constrained('utilization_states')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropForeign(['utilization_state_id']);
            $table->dropColumn('utilization_state_id');
        });

        Schema::dropIfExists('utilization_states');
    }
};
