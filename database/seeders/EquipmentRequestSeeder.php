<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class EquipmentRequestSeeder extends Seeder
{
    /**
     * Одна демо-заявка на перемещение (для проверки списка заявок у администратора).
     */
    public function run(): void
    {
        $nurse = User::query()->where('email', 'nurse@example.com')->first();
        $equipment = Equipment::query()->orderBy('id')->first();
        $departments = Department::query()->orderBy('id')->get();

        if (! $nurse || ! $equipment || $departments->count() < 2) {
            return;
        }

        $fromId = $equipment->department_id ?? $departments->first()->id;
        $to = $departments->firstWhere('id', '!=', $fromId);
        if (! $to) {
            return;
        }

        $already = EquipmentRequest::query()
            ->where('equipment_id', $equipment->id)
            ->whereRelation('requestType', 'code', EquipmentRequest::TYPE_MOVE)
            ->whereRelation('requestStatus', 'code', EquipmentRequest::STATUS_PENDING)
            ->exists();

        if ($already) {
            return;
        }

        EquipmentRequest::query()->create([
            'equipment_id' => $equipment->id,
            'user_id' => $nurse->id,
            'type' => EquipmentRequest::TYPE_MOVE,
            'status' => EquipmentRequest::STATUS_PENDING,
            'from_department_id' => $fromId,
            'to_department_id' => $to->id,
            'comment' => 'Демо-заявка из сидера (перемещение оборудования).',
        ]);
    }
}
