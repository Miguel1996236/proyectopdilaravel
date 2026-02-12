<x-app-layout>
    <x-slot name="header">{{ __('Grupos de estudiantes') }}</x-slot>

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div></div>
        <a href="{{ route('groups.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i>{{ __('Nuevo grupo') }}
        </a>
    </div>

    @if ($groups->isEmpty())
        <div class="card shadow mb-4">
            <div class="card-body text-center py-5">
                <i class="fas fa-users fa-3x text-gray-300 mb-3"></i>
                <p class="text-muted">{{ __('No tienes grupos creados aún.') }}</p>
                <a href="{{ route('groups.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i>{{ __('Crear primer grupo') }}
                </a>
            </div>
        </div>
    @else
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>{{ __('Nombre') }}</th>
                                <th>{{ __('Descripción') }}</th>
                                <th class="text-center">{{ __('Miembros') }}</th>
                                <th>{{ __('Creado') }}</th>
                                <th class="text-center">{{ __('Acciones') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($groups as $group)
                                <tr>
                                    <td>
                                        <a href="{{ route('groups.show', $group) }}" class="font-weight-bold text-primary">
                                            {{ $group->name }}
                                        </a>
                                    </td>
                                    <td>{{ \Illuminate\Support\Str::limit($group->description, 60) ?: '-' }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-primary badge-pill">{{ $group->members_count }}</span>
                                    </td>
                                    <td>{{ $group->created_at->format('d/m/Y') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('groups.show', $group) }}" class="btn btn-info btn-sm" title="{{ __('Ver') }}">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('groups.edit', $group) }}" class="btn btn-warning btn-sm" title="{{ __('Editar') }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('groups.export', $group) }}" class="btn btn-success btn-sm" title="{{ __('Exportar Excel') }}">
                                            <i class="fas fa-file-excel"></i>
                                        </a>
                                        <form action="{{ route('groups.destroy', $group) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('¿Eliminar este grupo?') }}');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="{{ __('Eliminar') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $groups->links() }}
            </div>
        </div>
    @endif
</x-app-layout>

