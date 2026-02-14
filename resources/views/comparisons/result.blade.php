<x-app-layout>
    <x-slot name="header">{{ __('Comparación de encuestas') }}</x-slot>

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div></div>
        <a href="{{ route('comparisons.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i>{{ __('Volver al listado') }}
        </a>
    </div>

    {{-- Resumen lado a lado --}}
    <div class="row">
        {{-- Encuesta A --}}
        <div class="col-lg-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">{{ __('Encuesta A') }}</div>
                            <div class="h5 mb-1 font-weight-bold text-gray-800">{{ $quizA->title }}</div>
                            <p class="text-muted small mb-2">{{ \Illuminate\Support\Str::limit($quizA->description, 100) }}</p>
                            <div class="mb-1">
                                <span class="badge badge-info">{{ $statsA['questions_count'] }} {{ __('preguntas') }}</span>
                                <span class="badge badge-primary">{{ $statsA['completed'] }} {{ __('completados') }}</span>
                            </div>
                            <small class="text-muted">{{ __('Creada:') }} {{ $statsA['created_at'] }} | {{ __('Cerrada:') }} {{ $statsA['closed_at'] ?? '-' }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Encuesta B --}}
        <div class="col-lg-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">{{ __('Encuesta B') }}</div>
                            <div class="h5 mb-1 font-weight-bold text-gray-800">{{ $quizB->title }}</div>
                            <p class="text-muted small mb-2">{{ \Illuminate\Support\Str::limit($quizB->description, 100) }}</p>
                            <div class="mb-1">
                                <span class="badge badge-info">{{ $statsB['questions_count'] }} {{ __('preguntas') }}</span>
                                <span class="badge badge-primary">{{ $statsB['completed'] }} {{ __('completados') }}</span>
                            </div>
                            <small class="text-muted">{{ __('Creada:') }} {{ $statsB['created_at'] }} | {{ __('Cerrada:') }} {{ $statsB['closed_at'] ?? '-' }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla comparativa de estadísticas --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table mr-1"></i>{{ __('Comparativa de estadísticas') }}
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ __('Métrica') }}</th>
                            <th class="text-center text-primary">{{ __('Encuesta A') }}</th>
                            <th class="text-center text-info">{{ __('Encuesta B') }}</th>
                            <th class="text-center">{{ __('Diferencia') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ __('Total de intentos') }}</td>
                            <td class="text-center">{{ $statsA['total_attempts'] }}</td>
                            <td class="text-center">{{ $statsB['total_attempts'] }}</td>
                            @php $diff = $statsB['total_attempts'] - $statsA['total_attempts']; @endphp
                            <td class="text-center font-weight-bold {{ $diff > 0 ? 'text-success' : ($diff < 0 ? 'text-danger' : '') }}">
                                {{ $diff > 0 ? '+' : '' }}{{ $diff }}
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __('Completados') }}</td>
                            <td class="text-center">{{ $statsA['completed'] }}</td>
                            <td class="text-center">{{ $statsB['completed'] }}</td>
                            @php $diff = $statsB['completed'] - $statsA['completed']; @endphp
                            <td class="text-center font-weight-bold {{ $diff > 0 ? 'text-success' : ($diff < 0 ? 'text-danger' : '') }}">
                                {{ $diff > 0 ? '+' : '' }}{{ $diff }}
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __('Preguntas') }}</td>
                            <td class="text-center">{{ $statsA['questions_count'] }}</td>
                            <td class="text-center">{{ $statsB['questions_count'] }}</td>
                            @php $diff = $statsB['questions_count'] - $statsA['questions_count']; @endphp
                            <td class="text-center">{{ $diff > 0 ? '+' : '' }}{{ $diff }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Análisis IA --}}
    @if (!empty($aiAnalysis))
        <div class="card shadow mb-4 border-left-success">
            <div class="card-header py-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #f0faf0 0%, #e8f5e9 100%);">
                <h6 class="m-0 font-weight-bold text-success">
                    <i class="fas fa-robot mr-1"></i>{{ __('Análisis comparativo con IA') }}
                </h6>
                <div class="d-flex align-items-center">
                    @if (isset($comparison) && $comparison?->analyzed_at)
                        <span class="text-muted small mr-3">
                            <i class="fas fa-clock mr-1"></i>{{ __('Generado el :date', ['date' => $comparison->analyzed_at->format('d/m/Y H:i')]) }}
                        </span>
                    @endif
                    <span class="badge badge-success" style="font-size: 0.65rem;">
                        <i class="fas fa-brain mr-1"></i>{{ __('Generado por IA') }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                {{-- Contenido del análisis con estilos profesionales --}}
                <style>
                    .ai-comparison-content {
                        font-size: 0.95rem;
                        line-height: 1.8;
                        color: #3a3b45;
                    }
                    .ai-comparison-content h1 {
                        font-size: 1.4rem;
                        font-weight: 700;
                        color: #2e59d9;
                        border-bottom: 2px solid #4e73df;
                        padding-bottom: 0.5rem;
                        margin-top: 1.5rem;
                        margin-bottom: 1rem;
                    }
                    .ai-comparison-content h2 {
                        font-size: 1.2rem;
                        font-weight: 700;
                        color: #4e73df;
                        border-bottom: 1px solid #e3e6f0;
                        padding-bottom: 0.4rem;
                        margin-top: 1.5rem;
                        margin-bottom: 0.8rem;
                    }
                    .ai-comparison-content h3 {
                        font-size: 1.05rem;
                        font-weight: 600;
                        color: #5a5c69;
                        margin-top: 1.2rem;
                        margin-bottom: 0.6rem;
                    }
                    .ai-comparison-content h4 {
                        font-size: 0.95rem;
                        font-weight: 600;
                        color: #858796;
                        margin-top: 1rem;
                        margin-bottom: 0.5rem;
                    }
                    .ai-comparison-content p {
                        margin-bottom: 0.8rem;
                    }
                    .ai-comparison-content ul, .ai-comparison-content ol {
                        margin-bottom: 1rem;
                        padding-left: 1.5rem;
                    }
                    .ai-comparison-content li {
                        margin-bottom: 0.4rem;
                        padding-left: 0.3rem;
                    }
                    .ai-comparison-content strong {
                        color: #2e59d9;
                    }
                    .ai-comparison-content blockquote {
                        border-left: 4px solid #4e73df;
                        padding: 0.5rem 1rem;
                        margin: 1rem 0;
                        background-color: #f0f3ff;
                        border-radius: 0 4px 4px 0;
                        color: #5a5c69;
                    }
                    .ai-comparison-content table {
                        width: 100%;
                        border-collapse: collapse;
                        margin: 1rem 0;
                    }
                    .ai-comparison-content table th,
                    .ai-comparison-content table td {
                        border: 1px solid #e3e6f0;
                        padding: 0.5rem 0.75rem;
                        font-size: 0.85rem;
                    }
                    .ai-comparison-content table th {
                        background-color: #f8f9fc;
                        font-weight: 600;
                        color: #4e73df;
                    }
                    .ai-comparison-content hr {
                        border: 0;
                        border-top: 1px solid #e3e6f0;
                        margin: 1.5rem 0;
                    }
                    .ai-comparison-content code {
                        background-color: #f8f9fc;
                        padding: 0.15rem 0.4rem;
                        border-radius: 3px;
                        font-size: 0.85em;
                        color: #e74a3b;
                    }
                </style>
                <div class="ai-comparison-content">
                    {!! \Illuminate\Support\Str::markdown($aiAnalysis) !!}
                </div>

                <hr style="border-top: 2px solid #e3e6f0; margin-top: 1.5rem;">
                <div class="text-center mt-3">
                    <form action="{{ route('comparisons.ai') }}" method="POST" class="d-inline js-show-loader">
                        @csrf
                        <input type="hidden" name="quiz_a" value="{{ $quizA->id }}">
                        <input type="hidden" name="quiz_b" value="{{ $quizB->id }}">
                        <button type="submit" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-sync-alt mr-1"></i>{{ __('Regenerar análisis con IA') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if (!empty($aiError))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle mr-1"></i>
            <strong>{{ __('Error al generar análisis con IA:') }}</strong> {{ $aiError }}
        </div>
        <div class="text-center mb-4">
            <form action="{{ route('comparisons.ai') }}" method="POST" class="d-inline js-show-loader">
                @csrf
                <input type="hidden" name="quiz_a" value="{{ $quizA->id }}">
                <input type="hidden" name="quiz_b" value="{{ $quizB->id }}">
                <button type="submit" class="btn btn-warning btn-sm">
                    <i class="fas fa-redo mr-1"></i>{{ __('Reintentar análisis') }}
                </button>
            </form>
        </div>
    @endif

    {{-- Botón para generar análisis IA si nunca se ha hecho --}}
    @if (empty($aiAnalysis) && empty($aiError))
        <div class="card shadow mb-4">
            <div class="card-body text-center py-5">
                <i class="fas fa-robot fa-3x text-gray-300 mb-3"></i>
                <h5 class="text-gray-800 font-weight-bold mb-2">{{ __('Análisis de IA disponible') }}</h5>
                <p class="text-muted mb-4">{{ __('Genera un análisis comparativo detallado utilizando inteligencia artificial para obtener insights profundos sobre las diferencias entre ambas encuestas.') }}</p>
                <form action="{{ route('comparisons.ai') }}" method="POST" class="d-inline js-show-loader">
                    @csrf
                    <input type="hidden" name="quiz_a" value="{{ $quizA->id }}">
                    <input type="hidden" name="quiz_b" value="{{ $quizB->id }}">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-robot mr-2"></i>{{ __('Generar análisis con IA') }}
                    </button>
                </form>
            </div>
        </div>
    @endif
</x-app-layout>
