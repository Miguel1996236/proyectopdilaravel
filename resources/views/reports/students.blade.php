<x-app-layout>
    <x-slot name="header">
        {{ __('Reporte de estudiantes') }}
    </x-slot>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('Filtros de búsqueda') }}</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.students') }}" class="row">
                <div class="col-md-4 mb-3">
                    <label for="search" class="form-label">{{ __('Buscar por nombre o email') }}</label>
                    <input type="text" 
                           class="form-control" 
                           id="search" 
                           name="search" 
                           value="{{ $filters['search'] ?? '' }}" 
                           placeholder="{{ __('Nombre o email...') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="participation" class="form-label">{{ __('Nivel de participación') }}</label>
                    <select class="form-control" id="participation" name="participation">
                        <option value="">{{ __('Todos') }}</option>
                        <option value="high" {{ ($filters['participation'] ?? '') === 'high' ? 'selected' : '' }}>
                            {{ __('Alta (5+ encuestas)') }}
                        </option>
                        <option value="low" {{ ($filters['participation'] ?? '') === 'low' ? 'selected' : '' }}>
                            {{ __('Baja (≤2 encuestas)') }}
                        </option>
                    </select>
                </div>
                <div class="col-md-4 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary mr-2">
                        <i class="fas fa-search mr-1"></i>{{ __('Buscar') }}
                    </button>
                    <a href="{{ route('reports.students') }}" class="btn btn-secondary">
                        <i class="fas fa-redo mr-1"></i>{{ __('Limpiar') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Gráfico de distribución -->
    @if(isset($charts['participation_distribution']) && $charts['participation_distribution'])
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">{{ __('Distribución de participación') }}</h6>
            </div>
            <div class="card-body">
                {!! $charts['participation_distribution']->container() !!}
                <div class="mt-3 text-center">
                    <small class="text-muted">
                        <span class="badge badge-success">{{ __('Alta') }}: {{ $participationStats['high'] }}</span>
                        <span class="badge badge-info ml-2">{{ __('Media') }}: {{ $participationStats['medium'] }}</span>
                        <span class="badge badge-warning ml-2">{{ __('Baja') }}: {{ $participationStats['low'] }}</span>
                        <span class="badge badge-secondary ml-2">{{ __('Sin participación') }}: {{ $participationStats['none'] }}</span>
                    </small>
                </div>
            </div>
        </div>
    @endif

    <!-- Tabla de estudiantes -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('Participación por estudiante') }}</h6>
            <span class="badge badge-primary">{{ $students->total() }} {{ __('estudiantes') }}</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ __('Nombre') }}</th>
                            <th>{{ __('Email') }}</th>
                            <th class="text-center">{{ __('Completadas') }}</th>
                            <th class="text-center">{{ __('Total intentos') }}</th>
                            <th class="text-center">{{ __('Última actividad') }}</th>
                            <th class="text-center">{{ __('Estado') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($students as $student)
                            <tr>
                                <td>
                                    <strong>{{ $student->name }}</strong>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $student->email }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-success">{{ $student->completed_attempts_count ?? 0 }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info">{{ $student->total_attempts_count ?? 0 }}</span>
                                </td>
                                <td class="text-center">
                                    @if($student->quizAttempts->first() && $student->quizAttempts->first()->completed_at)
                                        <small class="text-muted">
                                            {{ $student->quizAttempts->first()->completed_at->diffForHumans() }}
                                        </small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php
                                        $completed = $student->completed_attempts_count ?? 0;
                                        $statusClass = $completed >= 5 ? 'success' : ($completed >= 3 ? 'info' : ($completed >= 1 ? 'warning' : 'secondary'));
                                        $statusText = $completed >= 5 ? __('Alta') : ($completed >= 3 ? __('Media') : ($completed >= 1 ? __('Baja') : __('Sin participación')));
                                    @endphp
                                    <span class="badge badge-{{ $statusClass }}">{{ $statusText }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    {{ __('No se encontraron estudiantes con los filtros aplicados.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="d-flex justify-content-center mt-3">
                {{ $students->links() }}
            </div>
        </div>
    </div>

    @push('scripts')
        @if(isset($charts['participation_distribution']) && $charts['participation_distribution'])
            {!! $charts['participation_distribution']->script() !!}
        @endif
    @endpush
</x-app-layout>
