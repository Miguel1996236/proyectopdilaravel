<x-app-layout>
    <x-slot name="header">
        {{ __('Reporte general') }}
    </x-slot>

    <!-- Tarjetas de métricas -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
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
        <div class="col-xl-3 col-md-6 mb-4">
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
        <div class="col-xl-3 col-md-6 mb-4">
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
                        <li class="mb-2">
                            <i class="fas fa-percentage text-info mr-2"></i>
                            <strong>{{ __('Tasa de participación') }}:</strong> {{ number_format($stats['student_participation'], 1) }}%
                        </li>
                    </ul>
                </div>
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
    @endpush
</x-app-layout>
