<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Cabinet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CabinetController extends Controller
{
    public function index(): View
    {
        $items = Cabinet::withCount('equipment')->orderBy('number')->get();
        return view('cabinets.index', ['cabinets' => $items]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['number' => 'required|string|max:55']);
        $cabinet = Cabinet::create(['number' => $request->input('number')]);
        ActivityLog::record(
            Cabinet::class,
            $cabinet->id,
            'created',
            'Кабинет №'.$cabinet->number,
            'Добавлен кабинет/помещение.',
        );

        return redirect()->route('cabinets.index')->with('success', 'Помещение/кабинет добавлен.');
    }

    public function destroy(Cabinet $cabinet): RedirectResponse
    {
        if ($cabinet->equipment()->exists()) {
            return redirect()->route('cabinets.index')->with('error', 'Нельзя удалить кабинет, к которому привязано оборудование.');
        }
        $label = 'Кабинет №'.$cabinet->number;
        $id = $cabinet->id;
        $cabinet->delete();
        ActivityLog::record(
            Cabinet::class,
            $id,
            'deleted',
            $label,
            'Кабинет/помещение удалено из справочника.',
        );

        return redirect()->route('cabinets.index')->with('deleted', 'Помещение/кабинет скрыт из справочника. Восстановить можно в разделе «Архив и журнал».');
    }
}
