<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');
        $userRoleId = DB::table('roles')->where('name', 'user')->value('id');
        $nurseRoleId = DB::table('roles')->where('name', 'senior_nurse')->value('id');
        $accountantRoleId = DB::table('roles')->where('name', 'accountant')->value('id');

        $now = now();

        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'first_name' => 'Администратор',
                'last_name' => 'Системы',
                'patronymic' => null,
                'password' => Hash::make('password'),
                'role_id' => $adminRoleId,
                'email_verified_at' => $now,
                'is_active' => true,
                'date_joined' => $now,
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'first_name' => 'Пользователь',
                'last_name' => 'Просмотр',
                'patronymic' => null,
                'password' => Hash::make('password'),
                'role_id' => $userRoleId,
                'email_verified_at' => $now,
                'is_active' => true,
                'date_joined' => $now,
            ]
        );

        User::updateOrCreate(
            ['email' => 'nurse@example.com'],
            [
                'first_name' => 'Старшая',
                'last_name' => 'Медсестра',
                'patronymic' => null,
                'password' => Hash::make('password'),
                'role_id' => $nurseRoleId,
                'email_verified_at' => $now,
                'is_active' => true,
                'date_joined' => $now,
            ]
        );

        User::updateOrCreate(
            ['email' => 'accountant@example.com'],
            [
                'first_name' => 'Тестовый',
                'last_name' => 'Бухгалтер',
                'patronymic' => null,
                'password' => Hash::make('password'),
                'role_id' => $accountantRoleId,
                'email_verified_at' => $now,
                'is_active' => true,
                'date_joined' => $now,
            ]
        );
    }
}
