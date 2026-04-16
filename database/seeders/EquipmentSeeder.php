<?php

namespace Database\Seeders;

use App\Models\Cabinet;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedTypes();
        $this->seedDepartments();
        $this->seedCabinets();
        $this->seedRefrigerators();
        $this->seedSterilizers();
    }

    private function seedTypes(): void
    {
        $types = [
            'Холодильник-сейф',
            'Шкаф холодильный фармацефтический',
            'Стерилизатор ИК',
            'Стерилизатор паровой (автоклав)',
            'Стерилизатор паровой кассетный',
            'Стерилизатор озоновый',
        ];
        foreach ($types as $name) {
            EquipmentType::firstOrCreate(['name' => $name]);
        }
    }

    private function seedDepartments(): void
    {
        foreach (['Аптека', 'Глазная'] as $name) {
            Department::firstOrCreate(['name' => $name]);
        }
    }

    private function seedCabinets(): void
    {
        foreach (['СДП', 'Коридор', 'КДП 4', 'Опер блок'] as $number) {
            Cabinet::firstOrCreate(['number' => $number]);
        }
    }

    private function seedRefrigerators(): void
    {
        $types = [
            'Холодильник-сейф',
            'Шкаф холодильный фармацефтический',
            'Шкаф холодильный фармацефтический',
            'Шкаф холодильный фармацефтический',
            'Шкаф холодильный фармацефтический',
        ];
        $rows = [
            ['name' => 'Valberg', 'serial_number' => '53011212', 'year_of_manufacture' => '2010', 'department' => 'Аптека', 'cabinet' => 'СДП'],
            ['name' => 'Pozis Paracels', 'serial_number' => '204CV20011858', 'year_of_manufacture' => '2010', 'department' => 'Аптека', 'cabinet' => 'Коридор'],
            ['name' => 'Polair ШХФ-0,5', 'serial_number' => 'A122030916', 'year_of_manufacture' => '2016', 'department' => 'Аптека', 'cabinet' => 'Коридор'],
            ['name' => 'Polair', 'serial_number' => 'A12203', 'year_of_manufacture' => '2016', 'department' => 'Аптека', 'cabinet' => 'Коридор'],
            ['name' => 'Polair', 'serial_number' => '8108558', 'year_of_manufacture' => '2008', 'department' => 'Аптека', 'cabinet' => 'Коридор'],
        ];

        $number = 1;
        foreach ($rows as $i => $row) {
            Equipment::firstOrCreate(
                ['number' => $number],
                [
                    'equipment_type_id' => EquipmentType::where('name', $types[$i])->first()?->id,
                    'name' => $row['name'],
                    'serial_number' => $row['serial_number'],
                    'year_of_manufacture' => $row['year_of_manufacture'],
                    'department_id' => Department::where('name', $row['department'])->first()?->id,
                    'cabinet_id' => Cabinet::where('number', $row['cabinet'])->first()?->id,
                ]
            );
            $number++;
        }
    }

    private function seedSterilizers(): void
    {
        $rows = [
            [
                'number' => 1,
                'type' => 'Стерилизатор ИК',
                'name' => 'Московский Авциационный Институт СТ-ИК-МАИ',
                'serial_number' => '1.0 224',
                'production_date' => null,
                'inventory_number' => '08105369',
                'department' => 'Глазная',
                'cabinet' => 'КДП 4',
            ],
            [
                'number' => 2,
                'type' => 'Стерилизатор паровой (автоклав)',
                'name' => 'Melag Vacuklav 23 B+',
                'serial_number' => '201823-B1279',
                'production_date' => '2018',
                'inventory_number' => '71012400014',
                'department' => 'Глазная',
                'cabinet' => 'Опер блок',
            ],
            [
                'number' => 3,
                'type' => 'Стерилизатор паровой кассетный',
                'name' => 'STATIM 5000S',
                'serial_number' => '140717100009',
                'production_date' => '01.09.2017',
                'inventory_number' => '21012400174',
                'department' => 'Глазная',
                'cabinet' => 'Опер блок',
            ],
            [
                'number' => 4,
                'type' => 'Стерилизатор озоновый',
                'name' => 'Орион-Си ОП1-М (Orion-C OP1-M)',
                'serial_number' => '2137',
                'production_date' => '12.2008',
                'inventory_number' => null,
                'department' => 'Глазная',
                'cabinet' => 'Опер блок',
            ],
        ];

        foreach ($rows as $row) {
            $productionDate = null;
            $yearOfManufacture = null;
            if ($row['production_date']) {
                if (preg_match('/^\d{4}$/', $row['production_date'])) {
                    $yearOfManufacture = $row['production_date'];
                    $productionDate = Carbon::createFromFormat('Y', $row['production_date'])->startOfYear();
                } elseif (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/', $row['production_date'], $m)) {
                    $productionDate = Carbon::createFromDate((int) $m[3], (int) $m[2], (int) $m[1]);
                    $yearOfManufacture = $m[3];
                } elseif (preg_match('/^(\d{1,2})\.(\d{4})$/', $row['production_date'], $m)) {
                    $productionDate = Carbon::createFromDate((int) $m[2], (int) $m[1], 1);
                    $yearOfManufacture = $m[2];
                }
            }

            Equipment::firstOrCreate(
                ['number' => $row['number']],
                [
                    'equipment_type_id' => EquipmentType::where('name', $row['type'])->first()?->id,
                    'name' => $row['name'],
                    'serial_number' => $row['serial_number'],
                    'production_date' => $productionDate,
                    'year_of_manufacture' => $yearOfManufacture,
                    'inventory_number' => $row['inventory_number'],
                    'department_id' => Department::where('name', $row['department'])->first()?->id,
                    'cabinet_id' => Cabinet::where('number', $row['cabinet'])->first()?->id,
                ]
            );
        }
    }
}
