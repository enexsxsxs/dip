<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->first();
        if (! $admin) {
            return;
        }

        Report::query()->firstOrCreate(
            ['title' => 'Вводный отчёт (пример)'],
            [
                'body' => 'Это пример отчёта после заполнения сидером. Его можно изменить или удалить в интерфейсе.',
                'report_date' => now()->toDateString(),
                'user_id' => $admin->id,
            ]
        );
    }
}
