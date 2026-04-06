<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Equipment;
use App\Models\Department;
use App\Models\MovementRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MovementRequestCreateTest extends TestCase
{
    use RefreshDatabase;

    public function user_cannot_create_movement_request_for_written_off_equipment()
    {
        $nurse = User::factory()->create(['role' => 'Старшая медсестра']);

        $writtenOffEquipment = Equipment::factory()->create([
            'name' => 'Тонометр AND-100',
            'status' => 'Списано'
        ]);

        $department = Department::factory()->create(['name' => 'Кардиологическое отделение']);

        $requestData = [
            'type' => 'Перемещение',
            'equipment_id' => $writtenOffEquipment->id,
            'new_department_id' => $department->id,
            'comment' => 'Попытка переместить списанное',
        ];

        $response = $this->actingAs($nurse)
                         ->post(route('movement-requests.store'), $requestData);

        $response->assertSessionHasErrors(['equipment_id' => 'Нельзя создать заявку для оборудования со статусом "Списано"']);
        $response->assertRedirect();
     
        $this->assertDatabaseMissing('movement_requests', [
            'equipment_id' => $writtenOffEquipment->id,
            'comment' => 'Попытка переместить списанное',
        ]);

        $this->assertEquals(0, MovementRequest::count());
    }

}