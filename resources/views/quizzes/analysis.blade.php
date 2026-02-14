<x-app-layout>
    <x-slot name="header">
        {{ __('Informe detallado de IA') }}
    </x-slot>

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">{{ $quiz->title }}</h1>
            <p class="text-muted small mb-0">{{ __('Análisis generado automáticamente a partir de las respuestas registradas.') }}</p>
        </div>
        <div class="d-flex flex-wrap">
            <a href="{{ route('quizzes.show', $quiz) }}" class="btn btn-outline-secondary btn-sm mr-2 mb-2">
                <i class="fas fa-arrow-left mr-1"></i>{{ __('Volver a la encuesta') }}
            </a>
            <a href="{{ route('quizzes.analysis.export', $quiz) }}" class="btn btn-primary btn-sm mb-2" target="_blank">
                <i class="fas fa-file-download mr-1"></i>{{ __('Exportar informe') }}
            </a>
        </div>
    </div>

    @if ($analysis)
        {{-- Resumen ejecutivo + Indicadores --}}
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card shadow border-left-primary">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #f8f9fc 0%, #eef1f8 100%);">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-file-alt mr-1"></i>{{ __('Resumen ejecutivo') }}
                        </h6>
                        <span class="badge badge-success text-uppercase">
                            <i class="fas fa-robot mr-1"></i>{{ __('Generado por IA') }}
                        </span>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            <i class="fas fa-clock mr-1"></i>{{ __('Generado el :date', ['date' => optional($analysisSummary['completed_at'])->format('d/m/Y H:i') ?? '—']) }}
                        </p>
                        <div class="p-3 rounded" style="background-color: #f0f3ff; border-left: 3px solid #4e73df;">
                            <p class="mb-0 text-gray-800" style="font-size: 1rem; line-height: 1.7;">{{ $analysisSummary['summary'] ?? __('El motor de IA no devolvió un resumen.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="card shadow h-100 border-left-info">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-tachometer-alt mr-1"></i>{{ __('Indicadores clave') }}
                        </h6>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-center">
                        <div class="mb-3 p-3 rounded text-center" style="background-color: #f8f9fc;">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">{{ __('Preguntas') }}</div>
                            <div class="h2 font-weight-bold text-gray-800 mb-0">{{ $quiz->questions->count() }}</div>
                        </div>
                        <div class="mb-3 p-3 rounded text-center" style="background-color: #f0faf0;">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">{{ __('Respuestas') }}</div>
                            <div class="h2 font-weight-bold text-gray-800 mb-0">{{ $quiz->attempts->count() }}</div>
                        </div>
                        <div class="p-2 text-center">
                            <span class="badge badge-info"><i class="fas fa-robot mr-1"></i>{{ __('Análisis IA disponible') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gráficos con interpretación IA integrada --}}
        @if (! empty($analysisCharts))
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #f8f9fc 0%, #eef1f8 100%);">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar mr-1"></i>{{ __('Distribución de respuestas por pregunta') }}
                    </h6>
                    <span class="badge badge-light text-muted">
                        {{ count($analysisCharts) }} {{ __('gráficos') }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach ($analysisCharts as $chartEntry)
                            @php
                                /** @var \ArielMejiaDev\LarapexCharts\LarapexChart $chart */
                                $chart = $chartEntry['chart'];
                                $questionTitle = $chartEntry['question'];
                                // Buscar hallazgos IA para esta pregunta
                                $matchedFindings = [];
                                foreach (($analysisSummary['quantitative'] ?? []) as $qf) {
                                    if (isset($qf['question']) && str_contains(strtolower($qf['question']), strtolower(substr($questionTitle, 0, 30)))) {
                                        $matchedFindings = $qf['key_findings'] ?? [];
                                        break;
                                    }
                                }
                            @endphp
                            <div class="col-xl-6 col-lg-6 mb-4">
                                <div class="card border h-100">
                                    <div class="card-header py-2 bg-white">
                                        <h6 class="m-0 font-weight-bold text-gray-800" style="font-size: 0.9rem;">{{ $questionTitle }}</h6>
                                    </div>
                                    <div class="card-body pb-2">
                                        {!! $chart->container() !!}
                                    </div>
                                    @if (! empty($matchedFindings))
                                        <div class="card-footer bg-white py-2" style="border-top: 2px solid #4e73df;">
                                            <div class="d-flex align-items-center mb-1">
                                                <span class="badge badge-primary mr-2" style="font-size: 0.65rem;"><i class="fas fa-robot mr-1"></i>IA</span>
                                                <small class="font-weight-bold text-gray-700">{{ __('Interpretación') }}</small>
                                            </div>
                                            <ul class="mb-0 pl-3" style="font-size: 0.8rem;">
                                                @foreach ($matchedFindings as $finding)
                                                    <li class="text-gray-700 mb-1">{{ $finding }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Hallazgos cuantitativos + Temas cualitativos --}}
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chart-line mr-1"></i>{{ __('Hallazgos cuantitativos') }}
                        </h6>
                        <span class="badge badge-light text-muted" style="font-size: 0.7rem;">{{ __('Datos estadísticos') }}</span>
                    </div>
                    <div class="card-body">
                        @forelse ($quantitativeInsights as $insight)
                            <div class="mb-3 p-3 rounded" style="background-color: #f8f9fc; border-left: 3px solid #4e73df;">
                                <h6 class="text-gray-800 font-weight-bold mb-2" style="font-size: 0.9rem;">{{ $insight['question'] }}</h6>
                                <div class="row">
                                    <div class="col-auto">
                                        <span class="text-xs font-weight-bold text-muted text-uppercase">{{ __('Respuestas') }}</span>
                                        <div class="font-weight-bold text-gray-800">{{ $insight['total_responses'] ?? 0 }}</div>
                                    </div>
                                    @if (! empty($insight['average']))
                                        <div class="col-auto">
                                            <span class="text-xs font-weight-bold text-muted text-uppercase">{{ __('Promedio') }}</span>
                                            <div class="font-weight-bold text-primary">{{ $insight['average'] }}</div>
                                        </div>
                                    @endif
                                </div>
                                @if (! empty($insight['options']))
                                    <div class="mt-2">
                                        @foreach ($insight['options'] as $option)
                                            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.8rem;">
                                                <span class="text-gray-700">{{ $option['label'] }}</span>
                                                <span class="font-weight-bold">{{ $option['count'] }} <small class="text-muted">({{ $option['percentage'] ?? $option['count'] }}{{ isset($option['percentage']) ? '%' : '' }})</small></span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted small mb-0">{{ __('No hay datos cuantitativos disponibles.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card shadow h-100 border-left-info">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-comments mr-1"></i>{{ __('Temas cualitativos') }}
                        </h6>
                        <span class="badge badge-info" style="font-size: 0.65rem;"><i class="fas fa-robot mr-1"></i>{{ __('Detectado por IA') }}</span>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">{{ __('Patrones y temas identificados por la IA a partir de las respuestas abiertas.') }}</p>
                        @forelse (($analysisSummary['qualitative'] ?? []) as $theme)
                            <div class="mb-3 p-3 rounded" style="background-color: #f0f8ff; border-left: 3px solid #36b9cc;">
                                <h6 class="text-gray-800 font-weight-bold mb-2" style="font-size: 0.9rem;">
                                    <i class="fas fa-tag mr-1 text-info"></i>{{ $theme['theme'] ?? $theme['question'] ?? __('Tema') }}
                                </h6>
                                @if (! empty($theme['evidence']))
                                    @foreach ($theme['evidence'] as $quote)
                                        <div class="mb-1 pl-3" style="border-left: 2px solid #d1ecf1; font-size: 0.85rem; font-style: italic; color: #5a5c69;">
                                            <i class="fas fa-quote-left mr-1 text-info" style="font-size: 0.6rem;"></i>"{{ $quote }}"
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-muted small mb-0">{{ __('Sin testimonios destacados.') }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted small mb-0">{{ __('Aún no existen temas cualitativos destacados.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Respuestas abiertas (por pregunta) --}}
        @if (! empty($qualitativeInsights))
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-pencil-alt mr-1"></i>{{ __('Respuestas abiertas') }}
                    </h6>
                    <span class="badge badge-light text-muted" style="font-size: 0.7rem;">{{ __('Testimonios de participantes') }}</span>
                </div>
                <div class="card-body">
                    @foreach ($qualitativeInsights as $item)
                        <div class="mb-4 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color: #e3e6f0 !important;">
                            <h6 class="text-gray-800 font-weight-bold mb-3" style="font-size: 0.95rem;">
                                <i class="fas fa-question-circle text-primary mr-1"></i>{{ $item['question'] ?? __('Pregunta') }}
                            </h6>
                            <div class="row">
                                @foreach ($item['responses'] ?? [] as $response)
                                    <div class="col-md-6 mb-2">
                                        <div class="p-2 rounded" style="background-color: #f8f9fc; border-left: 3px solid #4e73df; font-size: 0.9rem; color: #5a5c69;">
                                            "{{ $response }}"
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Recomendaciones de IA --}}
        <div class="card shadow mb-4 border-left-success">
            <div class="card-header py-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #f0faf0 0%, #e8f5e9 100%);">
                <h6 class="m-0 font-weight-bold text-success">
                    <i class="fas fa-lightbulb mr-1"></i>{{ __('Recomendaciones de IA') }}
                </h6>
                <span class="badge badge-success text-uppercase" style="font-size: 0.65rem;">
                    <i class="fas fa-robot mr-1"></i>{{ __('Acciones sugeridas') }}
                </span>
            </div>
            <div class="card-body">
                @if (! empty($analysisSummary['recommendations']))
                    <p class="text-muted small mb-3">{{ __('Basadas en el análisis de todas las respuestas, la IA sugiere las siguientes acciones para mejorar la experiencia educativa:') }}</p>
                    <div class="row">
                        @foreach ($analysisSummary['recommendations'] as $index => $recommendation)
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-start p-3 rounded h-100" style="background-color: #f0faf0; border-left: 4px solid #1cc88a;">
                                    <div class="mr-3 text-center" style="min-width: 36px;">
                                        <span class="badge badge-success rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 1rem;">{{ $index + 1 }}</span>
                                    </div>
                                    <p class="mb-0 text-gray-800" style="font-size: 0.95rem; line-height: 1.6;">{{ $recommendation }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">{{ __('La IA no generó recomendaciones para esta encuesta. Puedes regenerar el informe desde la vista principal de la encuesta.') }}</p>
                @endif
            </div>
        </div>
    @else
        <div class="alert alert-info" role="alert">
            <i class="fas fa-info-circle mr-2"></i>{{ __('Todavía no se ha generado un informe de IA para esta encuesta. Cierra la encuesta o solicita el análisis desde la pantalla principal.') }}
        </div>
    @endif

    @push('scripts')
        @if (! empty($analysisCharts))
            @foreach ($analysisCharts as $chartEntry)
                {!! $chartEntry['chart']->script() !!}
            @endforeach
        @endif
    @endpush
</x-app-layout>
