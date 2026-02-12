<?php

namespace App\Http\Controllers;

use App\Models\StudentGroup;
use App\Models\StudentGroupMember;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Rap2hPoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentGroupController extends Controller
{
    protected function ensureTeacherOrAdmin(): void
    {
        abort_unless(
            in_array(Auth::user()?->role, [User::ROLE_ADMIN, User::ROLE_TEACHER]),
            403
        );
    }

    public function index(): View
    {
        $this->ensureTeacherOrAdmin();

        $groups = StudentGroup::where('user_id', Auth::id())
            ->withCount('members')
            ->latest()
            ->paginate(10);

        return view('groups.index', compact('groups'));
    }

    public function create(): View
    {
        $this->ensureTeacherOrAdmin();

        return view('groups.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureTeacherOrAdmin();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'members' => ['nullable', 'array'],
            'members.*.name' => ['required', 'string', 'max:255'],
            'members.*.email' => ['required', 'email', 'max:255'],
        ]);

        $group = StudentGroup::create([
            'user_id' => Auth::id(),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        if (! empty($data['members'])) {
            foreach ($data['members'] as $member) {
                $user = User::where('email', $member['email'])->first();
                $group->members()->create([
                    'name' => $member['name'],
                    'email' => $member['email'],
                    'user_id' => $user?->id,
                ]);
            }
        }

        return redirect()
            ->route('groups.show', $group)
            ->with('status', __('Grupo creado correctamente.'));
    }

    public function show(StudentGroup $group): View
    {
        $this->ensureTeacherOrAdmin();
        $this->ensureOwnership($group);

        $group->load('members');

        return view('groups.show', compact('group'));
    }

    public function edit(StudentGroup $group): View
    {
        $this->ensureTeacherOrAdmin();
        $this->ensureOwnership($group);

        $group->load('members');

        return view('groups.edit', compact('group'));
    }

    public function update(Request $request, StudentGroup $group): RedirectResponse
    {
        $this->ensureTeacherOrAdmin();
        $this->ensureOwnership($group);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'members' => ['nullable', 'array'],
            'members.*.name' => ['required', 'string', 'max:255'],
            'members.*.email' => ['required', 'email', 'max:255'],
        ]);

        $group->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        // Reemplazar miembros
        $group->members()->delete();

        if (! empty($data['members'])) {
            foreach ($data['members'] as $member) {
                $user = User::where('email', $member['email'])->first();
                $group->members()->create([
                    'name' => $member['name'],
                    'email' => $member['email'],
                    'user_id' => $user?->id,
                ]);
            }
        }

        return redirect()
            ->route('groups.show', $group)
            ->with('status', __('Grupo actualizado correctamente.'));
    }

    public function destroy(StudentGroup $group): RedirectResponse
    {
        $this->ensureTeacherOrAdmin();
        $this->ensureOwnership($group);

        $group->delete();

        return redirect()
            ->route('groups.index')
            ->with('status', __('Grupo eliminado correctamente.'));
    }

    public function exportExcel(StudentGroup $group): StreamedResponse
    {
        $this->ensureTeacherOrAdmin();
        $this->ensureOwnership($group);

        $group->load('members');

        $collection = $group->members->map(fn ($m) => [
            __('Nombre') => $m->name,
            __('Correo electrónico') => $m->email,
            __('Registrado') => $m->user_id ? __('Sí') : __('No'),
        ]);

        return (new FastExcel($collection))->download(
            'grupo-' . \Illuminate\Support\Str::slug($group->name) . '.xlsx'
        );
    }

    protected function ensureOwnership(StudentGroup $group): void
    {
        abort_if(
            Auth::user()->role !== User::ROLE_ADMIN && $group->user_id !== Auth::id(),
            403,
            __('No tienes permisos para acceder a este grupo.')
        );
    }
}

