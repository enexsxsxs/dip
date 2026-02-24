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
            'patronymic' => ['nullable', 'string', 'max:255'],
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
            'patronymic' => $request->patronymic ?? '',
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'name' => trim($request->surname.' '.$request->name.' '.($request->patronymic ?? '')),
            'is_active' => true,
            'date_joined' => now(),
        ]);

        return redirect()
            ->route('users.index')
            ->with('status', 'Пользователь успешно добавлен.');
    }

    /**
     * Форма редактирования пользователя.
     */
    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Обновление пользователя.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $rules = [
            'surname' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'patronymic' => ['nullable', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:users,email,'.$user->id,
            ],
            'role' => ['required', 'string', 'in:'.implode(',', User::ROLES)],
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['required', 'confirmed', Rules\Password::defaults()];
        }

        $request->validate($rules);

        $role = $user->id === auth()->id()
            ? $user->role
            : $request->role;

        $data = [
            'last_name' => $request->surname,
            'first_name' => $request->name,
            'patronymic' => $request->patronymic ?? '',
            'email' => $request->email,
            'role' => $role,
            'name' => trim($request->surname.' '.$request->name.' '.($request->patronymic ?? '')),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()
            ->route('users.index')
            ->with('status', 'Пользователь успешно обновлён.');
    }

    /**
     * Удаление пользователя. Нельзя удалить себя или другого администратора.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('users.index')
                ->with('error', 'Нельзя удалить самого себя.');
        }

        if ($user->isAdmin()) {
            return redirect()
                ->route('users.index')
                ->with('error', 'Нельзя удалить администратора.');
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('deleted', 'Пользователь удалён.');
    }
}
