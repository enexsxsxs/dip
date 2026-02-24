<?php

namespace App\Http\Controllers;

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
        Cabinet::create(['number' => $request->input('number')]);
        return redirect()->route('cabinets.index')->with('success', 'Помещение/кабинет добавлен.');
    }

    public function destroy(Cabinet $cabinet): RedirectResponse
    {
        if ($cabinet->equipment()->exists()) {
            return redirect()->route('cabinets.index')->with('error', 'Нельзя удалить кабинет, к которому привязано оборудование.');
        }
        $cabinet->delete();
        return redirect()->route('cabinets.index')->with('deleted', 'Помещение/кабинет удалён.');
    }
}
