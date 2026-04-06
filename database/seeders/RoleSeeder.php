<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (User::ROLES as $name) {
            Role::query()->updateOrCreate(
                ['name' => $name],
                ['name' => $name]
            );
        }
    }
}
