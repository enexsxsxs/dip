<?php

namespace App\Http\Controllers;

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
        EquipmentType::create(['name' => $request->input('name')]);
        return redirect()->route('equipment-types.index')->with('success', 'Вид оборудования добавлен.');
    }

    public function destroy(EquipmentType $equipmentType): RedirectResponse
    {
        if ($equipmentType->equipment()->exists()) {
            return redirect()->route('equipment-types.index')->with('error', 'Нельзя удалить вид оборудования, к которому привязано оборудование.');
        }
        $equipmentType->delete();
        return redirect()->route('equipment-types.index')->with('deleted', 'Вид оборудования удалён.');
    }
}
