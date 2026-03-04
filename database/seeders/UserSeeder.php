<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Администратор — полный доступ
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Администратор Системы',
                'first_name' => 'Администратор',
                'last_name' => 'Системы',
                'patronymic' => null,
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
                'is_staff' => true,
                'date_joined' => now(),
            ]
        );

        // Пользователь с правами только на просмотр: список оборудования, карточка, скачивание документов
        User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Пользователь (просмотр)',
                'first_name' => 'Пользователь',
                'last_name' => 'Просмотр',
                'patronymic' => null,
                'email' => 'user@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'is_active' => true,
                'is_staff' => false,
                'date_joined' => now(),
            ]
        );

        // Старшая медсестра: просмотр и редактирование оборудования, добавление отчётов (без удаления оборудования и без справочников)
        User::updateOrCreate(
            ['email' => 'nurse@example.com'],
            [
                'name' => 'Старшая медсестра',
                'first_name' => 'Старшая',
                'last_name' => 'Медсестра',
                'patronymic' => null,
                'email' => 'nurse@example.com',
                'password' => Hash::make('password'),
                'role' => 'senior_nurse',
                'is_active' => true,
                'is_staff' => true,
                'date_joined' => now(),
            ]
        );
    }
}
