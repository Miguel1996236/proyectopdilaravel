<x-app-layout>
    <x-slot name="header">
        {{ __('Gestión de usuarios') }}
    </x-slot>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('Usuarios registrados') }}</h6>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-user-plus mr-1"></i>{{ __('Nuevo usuario') }}
            </a>
        </div>
        <div class="card-body">
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ __('Nombre') }}</th>
                            <th>{{ __('Correo') }}</th>
                            <th>{{ __('Rol') }}</th>
                            <th class="text-right">{{ __('Acciones') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td class="text-muted small">{{ $user->email }}</td>
                                <td>
                                    <span class="badge badge-pill badge-secondary text-uppercase">{{ __($user->role) }}</span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary mr-1" title="{{ __('Editar') }}">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if (auth()->id() !== $user->id)
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline js-show-loader" onsubmit="return confirm('{{ __('¿Eliminar a :name?', ['name' => $user->name]) }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('Eliminar') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">{{ __('No hay usuarios registrados todavía.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>
