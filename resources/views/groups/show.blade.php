<x-app-layout>
    <x-slot name="header">{{ $group->name }}</x-slot>

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <p class="text-muted mb-0">{{ $group->description ?: __('Sin descripción') }}</p>
        </div>
        <div class="d-flex flex-wrap">
            <a href="{{ route('groups.index') }}" class="btn btn-outline-secondary btn-sm mr-2 mb-2">
                <i class="fas fa-arrow-left mr-1"></i>{{ __('Volver') }}
            </a>
            <a href="{{ route('groups.edit', $group) }}" class="btn btn-warning btn-sm mr-2 mb-2">
                <i class="fas fa-edit mr-1"></i>{{ __('Editar') }}
            </a>
            <a href="{{ route('groups.export', $group) }}" class="btn btn-success btn-sm mr-2 mb-2">
                <i class="fas fa-file-excel mr-1"></i>{{ __('Exportar Excel') }}
            </a>
            <form action="{{ route('groups.destroy', $group) }}" method="POST" class="d-inline mb-2"
                  onsubmit="return confirm('{{ __('¿Eliminar este grupo y todos sus miembros?') }}');">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash mr-1"></i>{{ __('Eliminar') }}
                </button>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                {{ __('Miembros') }}
                <span class="badge badge-primary ml-1">{{ $group->members->count() }}</span>
            </h6>
        </div>
        <div class="card-body">
            @if ($group->members->isEmpty())
                <p class="text-center text-muted py-3">{{ __('No hay miembros en este grupo.') }}</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('Nombre') }}</th>
                                <th>{{ __('Correo electrónico') }}</th>
                                <th class="text-center">{{ __('Registrado') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($group->members as $index => $member)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $member->name }}</td>
                                    <td>{{ $member->email }}</td>
                                    <td class="text-center">
                                        @if ($member->user_id)
                                            <span class="badge badge-success">{{ __('Sí') }}</span>
                                        @else
                                            <span class="badge badge-secondary">{{ __('No') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

