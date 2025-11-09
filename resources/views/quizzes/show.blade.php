<x-app-layout>
    <x-slot name="header">
        {{ __('Detalles de la encuesta') }}
    </x-slot>

    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">{{ $quiz->title }}</h6>
                    @php
                        $statusLabels = [
                            'draft' => __('Borrador'),
                            'published' => __('Publicada'),
                            'closed' => __('Cerrada'),
                        ];
                    @endphp
                    <span class="badge badge-pill badge-light text-secondary text-uppercase">
                        {{ $statusLabels[$quiz->status] ?? ucfirst($quiz->status) }}
                    </span>
                </div>
                <div class="card-body">
                    @if ($quiz->description)
                        <p class="mb-4 text-muted">{{ $quiz->description }}</p>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="small text-muted">{{ __('Respuestas registradas') }}</div>
                            <div class="h4 font-weight-bold">{{ $quiz->attempts->count() }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted">{{ __('Preguntas disponibles') }}</div>
                            <div class="h4 font-weight-bold">{{ $quiz->questions->count() }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-muted">{{ __('Requiere autenticación') }}</div>
                            <div class="h4 font-weight-bold">
                                <span class="badge {{ $quiz->require_login ? 'badge-success' : 'badge-secondary' }}">
                                    {{ $quiz->require_login ? __('Sí') : __('No') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <h6 class="font-weight-bold text-secondary text-uppercase">{{ __('Periodo de disponibilidad') }}</h6>
                    <div class="mb-4">
                        <p class="mb-1"><strong>{{ __('Desde:') }}</strong> {{ $quiz->opens_at ? $quiz->opens_at->format('d/m/Y H:i') : __('Sin definir') }}</p>
                        <p class="mb-0"><strong>{{ __('Hasta:') }}</strong> {{ $quiz->closes_at ? $quiz->closes_at->format('d/m/Y H:i') : __('Sin definir') }}</p>
                    </div>

                    <h6 class="font-weight-bold text-secondary text-uppercase">{{ __('Instrucciones adicionales') }}</h6>
                    <p class="text-muted">
                        {{ data_get($quiz->settings, 'additional_instructions') ?? __('No se han definido instrucciones adicionales.') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Códigos de invitación') }}</h6>
                </div>
                <div class="card-body">
                    @if ($quiz->invitations->count())
                        <ul class="list-group">
                            @foreach ($quiz->invitations as $invitation)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $invitation->code }}</strong>
                                        <div class="small text-muted">
                                            {{ __('Usos') }}: {{ $invitation->uses_count }}{{ $invitation->max_uses ? ' / '.$invitation->max_uses : '' }}
                                        </div>
                                        @if ($invitation->expires_at)
                                            <div class="small text-muted">{{ __('Expira:') }} {{ $invitation->expires_at->format('d/m/Y H:i') }}</div>
                                        @endif
                                    </div>
                                    <span class="badge {{ $invitation->is_active ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $invitation->is_active ? __('Activo') : __('Inactivo') }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted small mb-0">{{ __('Sin invitaciones generadas aún.') }}</p>
                    @endif
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Acciones rápidas') }}</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('quizzes.edit', $quiz) }}" class="btn btn-primary btn-block mb-2">
                        <i class="fas fa-edit mr-1"></i>{{ __('Editar encuesta') }}
                    </a>
                    <a href="{{ route('quizzes.index') }}" class="btn btn-outline-secondary btn-block">
                        <i class="fas fa-arrow-left mr-1"></i>{{ __('Volver al listado') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Resumen de respuestas') }}</h6>
                    <span class="badge badge-info badge-pill">{{ __('Encuesta') }}</span>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-4">
                        <div class="col-sm-4 mb-3 mb-sm-0">
                            <div class="text-xs font-weight-bold text-uppercase text-muted">{{ __('Respuestas totales') }}</div>
                            <div class="h3 font-weight-bold text-gray-800">{{ $quiz->attempts->count() }}</div>
                        </div>
                        <div class="col-sm-4 mb-3 mb-sm-0">
                            <div class="text-xs font-weight-bold text-uppercase text-muted">{{ __('Preguntas abiertas') }}</div>
                            <div class="h3 font-weight-bold text-gray-800">{{ $quiz->questions->where('type', 'open_text')->count() }}</div>
                        </div>
                        <div class="col-sm-4">
                            <div class="text-xs font-weight-bold text-uppercase text-muted">{{ __('Tiempo promedio (pendiente)') }}</div>
                            <div class="h3 font-weight-bold text-gray-800">—</div>
                        </div>
                    </div>

                    <h6 class="font-weight-bold text-secondary text-uppercase mb-3">{{ __('Distribución por tipo de pregunta') }}</h6>
                    <div>
                        <canvas id="questionTypeChart" height="120"></canvas>
                    </div>

                    <h6 class="font-weight-bold text-secondary text-uppercase mt-4 mb-3">{{ __('Participación por invitación') }}</h6>
                    <div>
                        <canvas id="invitationUsageChart" height="120"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Insights cualitativos') }}</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        {{ __('Cuando cierres la encuesta y ejecutes el análisis con IA, aparecerán recomendaciones personalizadas para el curso.') }}
                    </p>
                    <ul class="small list-unstyled mb-0 text-muted">
                        <li><i class="fas fa-check-circle text-success mr-2"></i>{{ __('Identificación de fortalezas y áreas de mejora.') }}</li>
                        <li><i class="fas fa-lightbulb text-warning mr-2"></i>{{ __('Sugerencias de seguimiento para el docente.') }}</li>
                        <li><i class="fas fa-chart-line text-primary mr-2"></i>{{ __('Tendencias detectadas en respuestas abiertas.') }}</li>
                    </ul>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Próximos pasos sugeridos') }}</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">
                        {{ __('Cierra la encuesta cuando consideres suficiente la participación para preparar reportes y generar recomendaciones.') }}
                    </p>
                    <ul class="small mb-3 text-muted">
                        <li>{{ __('Verifica la participación por invitación para asegurar cobertura.') }}</li>
                        <li>{{ __('Analiza diferencias entre tipos de pregunta y ajusta tu próximo instrumento.') }}</li>
                        <li>{{ __('Solicita el análisis con IA (en desarrollo) para obtener ideas accionables.') }}</li>
                    </ul>
                    <a href="{{ route('quizzes.edit', $quiz) }}" class="btn btn-sm btn-outline-primary btn-block">
                        <i class="fas fa-edit mr-1"></i>{{ __('Gestionar encuesta') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const typeChartCtx = document.getElementById('questionTypeChart');
        if (typeChartCtx) {
            new Chart(typeChartCtx, {
                type: 'pie',
                data: {
                    labels: {!! json_encode(
                        $quiz->questions->groupBy('type')->map(function ($questions) {
                            $typeLabels = [
                                'multiple_choice' => __('Opción múltiple'),
                                'multi_select' => __('Selección múltiple'),
                                'scale' => __('Escala'),
                                'open_text' => __('Respuesta abierta'),
                                'numeric' => __('Respuesta numérica'),
                            ];
                            $type = $questions->first()->type ?? '';
                            return $typeLabels[$type] ?? $type;
                        })->values()
                    ) !!},
                    datasets: [{
                        data: {!! json_encode($quiz->questions->groupBy('type')->map->count()->values()) !!},
                        backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                        hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617'],
                        borderColor: '#fff',
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    legend: { position: 'bottom' },
                },
            });
        }

        const usageChartCtx = document.getElementById('invitationUsageChart');
        if (usageChartCtx) {
            new Chart(usageChartCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($quiz->invitations->map(fn($inv) => $inv->label ?? $inv->code)) !!},
                    datasets: [{
                        label: '{{ __('Respuestas') }}',
                        data: {!! json_encode($quiz->invitations->map(fn($inv) => $inv->uses_count)) !!},
                        backgroundColor: '#4e73df',
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                precision: 0,
                            },
                        }],
                    },
                    legend: { display: false },
                },
            });
        }
    });
</script>
@endpush

