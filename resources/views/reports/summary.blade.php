<x-app-layout>
    <x-slot name="header">
        {{ __('Reportes') }}
    </x-slot>

    <div class="d-sm-flex align-items-center justify-content-end mb-3">
        <a href="{{ route('exports.surveys') }}" class="btn btn-success btn-sm">
            <i class="fas fa-file-excel mr-1"></i>{{ __('Exportar a Excel') }}
        </a>
    </div>

    <!-- Tarjetas de métricas -->
    <div class="row">
        <div class="col-xl-{{ auth()->user()->role === \App\Models\User::ROLE_ADMIN ? '3' : '4' }} col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">{{ __('Encuestas activas') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['active_surveys']) }}</div>
                            <div class="text-xs text-muted mt-1">
                                {{ __('de') }} {{ number_format($stats['total_surveys']) }} {{ __('totales') }}
                            </div>
                        </div>
                        <i class="fas fa-bullhorn fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        @if(auth()->user()->role === \App\Models\User::ROLE_ADMIN)
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">{{ __('Participación estudiantil') }}</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['student_participation'], 1) }}%</div>
                                <div class="text-xs text-muted mt-1">
                                    {{ $stats['students_with_attempts'] }} {{ __('de') }} {{ $stats['total_students'] }} {{ __('estudiantes') }}
                                </div>
                            </div>
                            <i class="fas fa-user-graduate fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="col-xl-{{ auth()->user()->role === \App\Models\User::ROLE_ADMIN ? '3' : '4' }} col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">{{ __('Análisis IA generados') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['ai_analyses']) }}</div>
                            <div class="text-xs text-muted mt-1">
                                {{ __('Informes completados') }}
                            </div>
                        </div>
                        <i class="fas fa-robot fa-2x text-info"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-{{ auth()->user()->role === \App\Models\User::ROLE_ADMIN ? '3' : '4' }} col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">{{ __('Intentos completados') }}</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['completed_attempts']) }}</div>
                            <div class="text-xs text-muted mt-1">
                                {{ __('de') }} {{ number_format($stats['total_attempts']) }} {{ __('totales') }}
                            </div>
                        </div>
                        <i class="fas fa-check-circle fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row">
        @if(isset($charts['weekly_activity']) && $charts['weekly_activity'])
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">{{ __('Actividad semanal') }}</h6>
                    </div>
                    <div class="card-body">
                        {!! $charts['weekly_activity']->container() !!}
                    </div>
                </div>
            </div>
        @endif

        @if(isset($charts['survey_status']) && $charts['survey_status'])
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">{{ __('Estado de encuestas') }}</h6>
                    </div>
                    <div class="card-body">
                        {!! $charts['survey_status']->container() !!}
                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                <span class="badge badge-primary">{{ __('Publicadas') }}: {{ $stats['active_surveys'] }}</span>
                                <span class="badge badge-success ml-2">{{ __('Cerradas') }}: {{ $stats['closed_surveys'] }}</span>
                                <span class="badge badge-secondary ml-2">{{ __('Borradores') }}: {{ $stats['draft_surveys'] }}</span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Top encuestas más activas -->
        <div class="col-xl-6 col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-trophy mr-2"></i>{{ __('Encuestas más activas') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if($topSurveys->isNotEmpty())
                        <div class="list-group list-group-flush">
                            @foreach($topSurveys as $index => $survey)
                                <div class="list-group-item px-0 py-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-1">
                                                <span class="badge badge-warning mr-2">#{{ $index + 1 }}</span>
                                                <strong class="text-gray-800">{{ \Illuminate\Support\Str::limit($survey->title, 40) }}</strong>
                                            </div>
                                            @if($survey->description)
                                                <small class="text-muted d-block">{{ \Illuminate\Support\Str::limit($survey->description, 50) }}</small>
                                            @endif
                                        </div>
                                        <a href="{{ route('quizzes.show', $survey) }}" class="btn btn-sm btn-outline-primary ml-2" title="{{ __('Ver detalles') }}">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <small class="text-muted d-block">{{ __('Intentos') }}</small>
                                            <strong class="text-primary">{{ $survey->attempts_count ?? 0 }}</strong>
                                        </div>
                                        <div class="col-4">
                                            <small class="text-muted d-block">{{ __('Participación') }}</small>
                                            @php
                                                $rate = $survey->participation_rate ?? 0;
                                                $rateColor = $rate >= 70 ? 'success' : ($rate >= 40 ? 'warning' : 'danger');
                                            @endphp
                                            <strong class="text-{{ $rateColor }}">{{ number_format($rate, 1) }}%</strong>
                                        </div>
                                        <div class="col-4">
                                            <small class="text-muted d-block">{{ __('Estado') }}</small>
                                            @php
                                                $statusColors = [
                                                    'draft' => 'secondary',
                                                    'published' => 'primary',
                                                    'closed' => 'success',
                                                ];
                                                $statusLabels = [
                                                    'draft' => __('Borrador'),
                                                    'published' => __('Publicada'),
                                                    'closed' => __('Cerrada'),
                                                ];
                                            @endphp
                                            <span class="badge badge-{{ $statusColors[$survey->status] ?? 'secondary' }} badge-sm">
                                                {{ $statusLabels[$survey->status] ?? $survey->status }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>{{ __('No hay encuestas disponibles') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if(isset($charts['monthly_participation']) && $charts['monthly_participation'])
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">{{ __('Participación mensual') }}</h6>
                    </div>
                    <div class="card-body">
                        {!! $charts['monthly_participation']->container() !!}
                    </div>
                </div>
            </div>
        @endif

        @if(isset($charts['role_distribution']) && $charts['role_distribution'])
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">{{ __('Distribución por rol') }}</h6>
                    </div>
                    <div class="card-body">
                        {!! $charts['role_distribution']->container() !!}
                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                <span class="badge badge-primary">{{ __('Administradores') }}: {{ $roleCounts['administrador'] ?? 0 }}</span>
                                <span class="badge badge-info ml-2">{{ __('Docentes') }}: {{ $roleCounts['docente'] ?? 0 }}</span>
                                <span class="badge badge-success ml-2">{{ __('Estudiantes') }}: {{ $roleCounts['estudiante'] ?? 0 }}</span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(isset($charts['monthly_trends']) && $charts['monthly_trends'])
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">{{ __('Tendencias mensuales') }}</h6>
                    </div>
                    <div class="card-body">
                        {!! $charts['monthly_trends']->container() !!}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Resumen ejecutivo -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('Resumen ejecutivo') }}</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="font-weight-bold text-gray-700 mb-3">{{ __('Estadísticas generales') }}</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-chart-line text-primary mr-2"></i>
                            <strong>{{ __('Total de encuestas') }}:</strong> {{ number_format($stats['total_surveys']) }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success mr-2"></i>
                            <strong>{{ __('Encuestas activas') }}:</strong> {{ number_format($stats['active_surveys']) }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-lock text-secondary mr-2"></i>
                            <strong>{{ __('Encuestas cerradas') }}:</strong> {{ number_format($stats['closed_surveys']) }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-file-alt text-info mr-2"></i>
                            <strong>{{ __('Borradores') }}:</strong> {{ number_format($stats['draft_surveys']) }}
                        </li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="font-weight-bold text-gray-700 mb-3">{{ __('Participación') }}</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-users text-primary mr-2"></i>
                            <strong>{{ __('Total de intentos') }}:</strong> {{ number_format($stats['total_attempts']) }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-double text-success mr-2"></i>
                            <strong>{{ __('Completados') }}:</strong> {{ number_format($stats['completed_attempts']) }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-clock text-warning mr-2"></i>
                            <strong>{{ __('Pendientes') }}:</strong> {{ number_format($stats['pending_attempts']) }}
                        </li>
                        @if(auth()->user()->role === \App\Models\User::ROLE_ADMIN)
                            <li class="mb-2">
                                <i class="fas fa-percentage text-info mr-2"></i>
                                <strong>{{ __('Tasa de participación') }}:</strong> {{ number_format($stats['student_participation'], 1) }}%
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros para tabla de encuestas -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('Filtros de búsqueda') }}</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.summary') }}" class="row">
                <div class="col-md-3 mb-3">
                    <label for="search" class="form-label">{{ __('Buscar') }}</label>
                    <input type="text" 
                           class="form-control" 
                           id="search" 
                           name="search" 
                           value="{{ $filters['search'] ?? '' }}" 
                           placeholder="{{ __('Título o descripción...') }}">
                </div>
                <div class="col-md-2 mb-3">
                    <label for="status" class="form-label">{{ __('Estado') }}</label>
                    <select class="form-control" id="status" name="status">
                        <option value="">{{ __('Todos') }}</option>
                        <option value="draft" {{ ($filters['status'] ?? '') === 'draft' ? 'selected' : '' }}>{{ __('Borrador') }}</option>
                        <option value="published" {{ ($filters['status'] ?? '') === 'published' ? 'selected' : '' }}>{{ __('Publicada') }}</option>
                        <option value="closed" {{ ($filters['status'] ?? '') === 'closed' ? 'selected' : '' }}>{{ __('Cerrada') }}</option>
                    </select>
                </div>
                @if($teachers->isNotEmpty())
                    <div class="col-md-2 mb-3">
                        <label for="owner" class="form-label">{{ __('Docente') }}</label>
                        <select class="form-control" id="owner" name="owner">
                            <option value="">{{ __('Todos') }}</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ ($filters['owner'] ?? '') == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-2 mb-3">
                    <label for="date_from" class="form-label">{{ __('Desde') }}</label>
                    <input type="date" 
                           class="form-control" 
                           id="date_from" 
                           name="date_from" 
                           value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div class="col-md-2 mb-3">
                    <label for="date_to" class="form-label">{{ __('Hasta') }}</label>
                    <input type="date" 
                           class="form-control" 
                           id="date_to" 
                           name="date_to" 
                           value="{{ $filters['date_to'] ?? '' }}">
                </div>
                <div class="col-md-1 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <div class="col-md-12">
                    <a href="{{ route('reports.summary') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-redo mr-1"></i>{{ __('Limpiar filtros') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de encuestas -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('Listado de encuestas') }}</h6>
            <span class="badge badge-primary">{{ $surveys->total() }} {{ __('encuestas') }}</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ __('Título') }}</th>
                            @if(auth()->user()->role === \App\Models\User::ROLE_ADMIN)
                                <th>{{ __('Propietario') }}</th>
                            @endif
                            <th class="text-center">{{ __('Estado') }}</th>
                            <th class="text-center">{{ __('Preguntas') }}</th>
                            <th class="text-center">{{ __('Intentos') }}</th>
                            <th class="text-center">{{ __('Participación') }}</th>
                            <th class="text-center">{{ __('Análisis IA') }}</th>
                            <th class="text-center">{{ __('Creada') }}</th>
                            <th class="text-center">{{ __('Acciones') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($surveys as $survey)
                            <tr>
                                <td>
                                    <strong>{{ $survey->title }}</strong>
                                    @if($survey->description)
                                        <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($survey->description, 50) }}</small>
                                    @endif
                                </td>
                                @if(auth()->user()->role === \App\Models\User::ROLE_ADMIN)
                                    <td>
                                        @if($survey->owner)
                                            <small>{{ $survey->owner->name }}</small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                @endif
                                <td class="text-center">
                                    @php
                                        $statusColors = [
                                            'draft' => 'secondary',
                                            'published' => 'primary',
                                            'closed' => 'success',
                                        ];
                                        $statusLabels = [
                                            'draft' => __('Borrador'),
                                            'published' => __('Publicada'),
                                            'closed' => __('Cerrada'),
                                        ];
                                    @endphp
                                    <span class="badge badge-{{ $statusColors[$survey->status] ?? 'secondary' }}">
                                        {{ $statusLabels[$survey->status] ?? $survey->status }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info">{{ $survey->questions_count }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-primary">{{ $survey->attempts_count }}</span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $rate = $survey->participation_rate ?? 0;
                                        $rateColor = $rate >= 70 ? 'success' : ($rate >= 40 ? 'warning' : 'danger');
                                    @endphp
                                    <span class="badge badge-{{ $rateColor }}">{{ number_format($rate, 1) }}%</span>
                                </td>
                                <td class="text-center">
                                    @if($survey->analyses_count > 0)
                                        <span class="badge badge-success">
                                            <i class="fas fa-check mr-1"></i>{{ $survey->analyses_count }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <small class="text-muted">{{ $survey->created_at->format('d/m/Y') }}</small>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('quizzes.show', $survey) }}" class="btn btn-sm btn-outline-primary" title="{{ __('Ver detalles') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->role === \App\Models\User::ROLE_ADMIN ? '9' : '8' }}" class="text-center text-muted py-4">
                                    {{ __('No se encontraron encuestas con los filtros aplicados.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="d-flex justify-content-center mt-3">
                {{ $surveys->links() }}
            </div>
        </div>
    </div>

    @push('scripts')
        @if(isset($charts['weekly_activity']) && $charts['weekly_activity'])
            {!! $charts['weekly_activity']->script() !!}
        @endif
        @if(isset($charts['survey_status']) && $charts['survey_status'])
            {!! $charts['survey_status']->script() !!}
        @endif
        @if(isset($charts['monthly_participation']) && $charts['monthly_participation'])
            {!! $charts['monthly_participation']->script() !!}
        @endif
        @if(isset($charts['role_distribution']) && $charts['role_distribution'])
            {!! $charts['role_distribution']->script() !!}
        @endif
        @if(isset($charts['monthly_trends']) && $charts['monthly_trends'])
            {!! $charts['monthly_trends']->script() !!}
        @endif
    @endpush
</x-app-layout>
