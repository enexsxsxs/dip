<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Список пользователей.
     */
    public function index(): View
    {
        $users = User::query()->orderBy('id')->paginate(15);

        return view('users.index', compact('users'));
    }

    /**
     * Форма создания пользователя.
     */
    public function create(): View
    {
        return view('users.create');
    }

    /**
     * Сохранение нового пользователя.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'surname' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'patronymic' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:'.User::class,
            ],
            'role' => ['required', 'string', 'in:'.implode(',', User::ROLES)],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        User::create([
            'last_name' => $request->surname,
            'first_name' => $request->name,
            'patronymic' => $request->patronymic,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'name' => trim($request->surname.' '.$request->name.' '.$request->patronymic),
            'is_active' => true,
            'date_joined' => now(),
        ]);

        return redirect()
            ->route('users.index')
            ->with('status', 'Пользователь успешно добавлен.');
    }
}
