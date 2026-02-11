<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Verificar que el usuario autenticado sea administrador
     */
    protected function ensureAdmin(): void
    {
        abort_unless(Auth::user()?->role === User::ROLE_ADMIN, 403);
    }

    public function index(): View
    {
        $this->ensureAdmin();

        $users = User::query()
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        $this->ensureAdmin();

        $roles = $this->availableRoles();

        return view('admin.users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->ensureAdmin();

        $data = $request->validated();

        $password = $data['password'] ?? Str::random(10);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => Hash::make($password),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('status', __('Usuario creado correctamente.'));
    }

    public function edit(User $user): View
    {
        $this->ensureAdmin();

        $roles = $this->availableRoles();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->ensureAdmin();

        $data = $request->validated();

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
        ];

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);

        return redirect()
            ->route('admin.users.index')
            ->with('status', __('Usuario actualizado correctamente.'));
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->ensureAdmin();

        abort_if(Auth::id() === $user->id, 403, __('No puedes eliminar tu propia cuenta.'));

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', __('Usuario eliminado correctamente.'));
    }

    /**
     * @return array<string, string>
     */
    protected function availableRoles(): array
    {
        return [
            User::ROLE_ADMIN => __('Administrador'),
            User::ROLE_TEACHER => __('Docente'),
            User::ROLE_STUDENT => __('Estudiante'),
        ];
    }
}
