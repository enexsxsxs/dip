<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 255)->nullable()->after('id');
            $table->string('first_name', 255)->nullable()->after('username');
            $table->string('last_name', 255)->nullable()->after('first_name');
            $table->timestamp('last_login')->nullable()->after('password');
            $table->boolean('is_superuser')->default(false)->after('last_login');
            $table->boolean('is_staff')->default(false)->after('is_superuser');
            $table->boolean('is_active')->default(true)->after('is_staff');
            $table->timestamp('date_joined')->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username', 'first_name', 'last_name', 'last_login',
                'is_superuser', 'is_staff', 'is_active', 'date_joined'
            ]);
        });
    }
};
