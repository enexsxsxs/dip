<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\EquipmentType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EquipmentTypeController extends Controller
{
    public function index(): View
    {
        $items = EquipmentType::withCount('equipment')->orderBy('name')->get();
        return view('equipment-types.index', ['equipmentTypes' => $items]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['name' => 'required|string|max:255']);
        $type = EquipmentType::create(['name' => $request->input('name')]);
        ActivityLog::record(
            EquipmentType::class,
            $type->id,
            'created',
            $type->name,
            'Добавлен вид оборудования.',
        );

        return redirect()->route('equipment-types.index')->with('success', 'Вид оборудования добавлен.');
    }

    public function destroy(EquipmentType $equipmentType): RedirectResponse
    {
        if ($equipmentType->equipment()->exists()) {
            return redirect()->route('equipment-types.index')->with('error', 'Нельзя удалить вид оборудования, к которому привязано оборудование.');
        }
        $label = $equipmentType->name;
        $id = $equipmentType->id;
        $equipmentType->delete();
        ActivityLog::record(
            EquipmentType::class,
            $id,
            'deleted',
            $label,
            'Вид оборудования удалён из справочника.',
        );

        return redirect()->route('equipment-types.index')->with('deleted', 'Вид оборудования скрыт из справочника. Восстановить можно в разделе «Архив и журнал».');
    }
}
