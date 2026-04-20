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
        $chiefDoctorSignerRoleId = DB::table('roles')->where('name', 'sign_chief_doctor')->value('id');
        $writeoffHeadSignerRoleId = DB::table('roles')->where('name', 'sign_writeoff_head')->value('id');
        $moveHeadSignerRoleId = DB::table('roles')->where('name', 'sign_move_head')->value('id');

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
                'first_name' => 'Э.Г.',
                'last_name' => 'Гайдарова',
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
                'first_name' => 'О.Н.',
                'last_name' => 'Ефарова',
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

        // Служебные подписанты: не входят в систему, используются только для автоподстановки в PDF.
        User::updateOrCreate(
            ['email' => 'sign-chief-doctor@example.com'],
            [
                'first_name' => 'Г.М.',
                'last_name' => 'Гайдаров',
                'patronymic' => null,
                'password' => Hash::make('password'),
                'role_id' => $chiefDoctorSignerRoleId,
                'email_verified_at' => $now,
                'is_active' => false,
                'date_joined' => $now,
            ]
        );

        User::updateOrCreate(
            ['email' => 'sign-writeoff-head@example.com'],
            [
                'first_name' => 'Э.Г.',
                'last_name' => 'Гайдарова',
                'patronymic' => null,
                'password' => Hash::make('password'),
                'role_id' => $writeoffHeadSignerRoleId,
                'email_verified_at' => $now,
                'is_active' => false,
                'date_joined' => $now,
            ]
        );

        User::updateOrCreate(
            ['email' => 'sign-move-head@example.com'],
            [
                'first_name' => 'Н.М.',
                'last_name' => 'Черных',
                'patronymic' => null,
                'password' => Hash::make('password'),
                'role_id' => $moveHeadSignerRoleId,
                'email_verified_at' => $now,
                'is_active' => false,
                'date_joined' => $now,
            ]
        );

    }
}
