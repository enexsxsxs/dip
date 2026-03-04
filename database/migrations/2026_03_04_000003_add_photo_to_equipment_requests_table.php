<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('equipment_requests', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('comment')->comment('Путь к фото причины списания');
        });
    }

    public function down(): void
    {
        Schema::table('equipment_requests', function (Blueprint $table) {
            $table->dropColumn('photo');
        });
    }
};

