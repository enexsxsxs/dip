<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
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
        $users = User::query()
            ->with('roleModel')
            ->orderByDesc('is_active')
            ->orderBy('id')
            ->paginate(15);

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
            'role' => ['required', 'string', 'in:'.implode(',', User::LOGIN_ROLES)],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'last_name' => $request->surname,
            'first_name' => $request->name,
            'patronymic' => $request->patronymic ?? '',
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'is_active' => true,
            'date_joined' => now(),
        ]);

        $roleLabel = User::ROLE_LABELS[$request->role] ?? $request->role;
        ActivityLog::record(
            User::class,
            $user->id,
            'created',
            $user->name.' ('.$user->email.')',
            'Создан пользователь, роль: '.$roleLabel.'.',
        );

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
            'role' => ['required', 'string', 'in:'.implode(',', User::LOGIN_ROLES)],
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
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        ActivityLog::record(
            User::class,
            $user->id,
            'updated',
            $user->name.' ('.$user->email.')',
            'Изменены данные пользователя.',
        );

        return redirect()
            ->route('users.index')
            ->with('status', 'Пользователь успешно обновлён.');
    }

    /**
     * Увольнение: запись в БД сохраняется, вход блокируется (is_active = false).
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('users.index')
                ->with('error', 'Нельзя уволить самого себя.');
        }

        if ($user->isAdmin()) {
            return redirect()
                ->route('users.index')
                ->with('error', 'Нельзя уволить администратора.');
        }

        if (! $user->is_active) {
            return redirect()
                ->route('users.index')
                ->with('error', 'Этот сотрудник уже уволен.');
        }

        $user->update(['is_active' => false]);

        ActivityLog::record(
            User::class,
            $user->id,
            'deactivated',
            $user->name.' ('.$user->email.')',
            'Увольнение: доступ в систему отключён, учётная запись сохранена.',
        );

        return redirect()
            ->route('users.index')
            ->with('status', 'Сотрудник уволен: доступ в систему отключён, запись сохранена.');
    }

    /**
     * Восстановление доступа уволенному сотруднику (кроме конфликтов с правилами выше).
     */
    public function restore(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('users.index')
                ->with('error', 'Некорректная операция.');
        }

        if ($user->isAdmin()) {
            return redirect()
                ->route('users.index')
                ->with('error', 'Операция не применима к администратору.');
        }

        if ($user->is_active) {
            return redirect()
                ->route('users.index')
                ->with('error', 'Сотрудник уже активен.');
        }

        $user->update(['is_active' => true]);

        ActivityLog::record(
            User::class,
            $user->id,
            'restored',
            $user->name.' ('.$user->email.')',
            'Доступ сотрудника восстановлен.',
        );

        return redirect()
            ->route('users.index')
            ->with('status', 'Доступ сотрудника восстановлен.');
    }
}
