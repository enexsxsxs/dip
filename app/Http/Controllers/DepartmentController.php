<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        $items = Department::withCount('equipment')->orderBy('name')->get();
        return view('departments.index', ['departments' => $items]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['name' => 'required|string|max:155']);
        Department::create(['name' => $request->input('name')]);
        return redirect()->route('departments.index')->with('success', 'Отдел добавлен.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        if ($department->equipment()->exists()) {
            return redirect()->route('departments.index')->with('error', 'Нельзя удалить отдел, к которому привязано оборудование.');
        }
        $department->delete();
        return redirect()->route('departments.index')->with('deleted', 'Отдел удалён.');
    }
}
