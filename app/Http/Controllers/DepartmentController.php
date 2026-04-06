<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
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
        $department = Department::create(['name' => $request->input('name')]);
        ActivityLog::record(
            Department::class,
            $department->id,
            'created',
            $department->name,
            'Добавлен отдел.',
        );

        return redirect()->route('departments.index')->with('success', 'Отдел добавлен.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        if ($department->equipment()->exists()) {
            return redirect()->route('departments.index')->with('error', 'Нельзя удалить отдел, к которому привязано оборудование.');
        }
        $label = $department->name;
        $id = $department->id;
        $department->delete();
        ActivityLog::record(
            Department::class,
            $id,
            'deleted',
            $label,
            'Отдел удалён из справочника.',
        );

        return redirect()->route('departments.index')->with('deleted', 'Отдел скрыт из справочника. Восстановить можно в разделе «Архив и журнал».');
    }
}
