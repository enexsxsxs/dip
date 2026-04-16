<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->unsignedInteger('registry_number')->nullable()->after('id');
        });

        $n = 1;
        foreach (DB::table('requests')->orderBy('id')->pluck('id') as $id) {
            DB::table('requests')->where('id', $id)->update(['registry_number' => $n]);
            $n++;
        }

        Schema::table('requests', function (Blueprint $table) {
            $table->unsignedInteger('registry_number')->nullable(false)->change();
            $table->unique('registry_number');
        });
    }

    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropUnique(['registry_number']);
            $table->dropColumn('registry_number');
        });
    }
};
