<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EquipmentTableTest extends TestCase
{
    use RefreshDatabase;

    public function user_can_search_equipment_by_name_and_sort_by_serial_number()
    {

        $user = User::factory()->create(['role' => 'Старшая медсестра']);

        Equipment::factory()->create(['name' => 'Холодильник ХФ-140 POZIS', 'serial_number' => 'SN-002']);
        Equipment::factory()->create(['name' => 'Холодильник ХФД-280 POZIS', 'serial_number' => 'SN-001']);
        Equipment::factory()->create(['name' => 'Тонометр AND-100', 'serial_number' => 'SN-003']);
        Equipment::factory()->create(['name' => 'Монитор пациента', 'serial_number' => 'SN-005']);
        Equipment::factory()->create(['name' => 'Центрифуга', 'serial_number' => 'SN-004']);

        $response = $this->actingAs($user) 
                         ->get(route('equipment.index', [
                             'search' => 'Холодильник', 
                             'sort' => 'serial_number',  
                             'direction' => 'asc'        
                         ]));

        $response->assertStatus(200);

        $response->assertSee('Холодильник ХФ-140 POZIS');
        $response->assertSee('Холодильник ХФД-280 POZIS');
        $response->assertDontSee('Тонометр AND-100'); 

        $equipments = $response->viewData('equipments'); 

        $this->assertEquals('SN-001', $equipments->first()->serial_number);
        $this->assertEquals('Холодильник ХФД-280 POZIS', $equipments->first()->name);
    }
}