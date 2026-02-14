<x-app-layout>
    <x-slot name="header">{{ __('Comparar encuestas') }}</x-slot>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            {{-- Listado de comparaciones realizadas --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history mr-1"></i>{{ __('Comparaciones realizadas') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if ($comparisons->isEmpty())
                        <div class="text-center py-4">
                            <i class="fas fa-exchange-alt fa-2x text-gray-300 mb-3"></i>
                            <p class="text-muted mb-0">{{ __('Aún no has guardado ninguna comparación.') }}</p>
                            <p class="text-muted small">{{ __('Selecciona dos encuestas abajo y usa "Comparar con IA" para guardar el análisis.') }}</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{ __('Encuesta A') }}</th>
                                        <th class="text-center" style="width: 50px;">—</th>
                                        <th>{{ __('Encuesta B') }}</th>
                                        <th>{{ __('Fecha') }}</th>
                                        <th class="text-center" style="width: 100px;">{{ __('Acciones') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($comparisons as $comp)
                                        <tr>
                                            <td>
                                                <strong>{{ $comp->quizA?->title ?? __('—') }}</strong>
                                                @if ($comp->quizA)
                                                        <br><small class="text-muted">{{ $comp->quizA->attempts_count ?? 0 }} {{ __('respuestas') }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center text-muted">
                                                <i class="fas fa-exchange-alt"></i>
                                            </td>
                                            <td>
                                                <strong>{{ $comp->quizB?->title ?? __('—') }}</strong>
                                                @if ($comp->quizB)
                                                        <br><small class="text-muted">{{ $comp->quizB->attempts_count ?? 0 }} {{ __('respuestas') }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($comp->analyzed_at)
                                                    {{ $comp->analyzed_at->format('d/m/Y H:i') }}
                                                @else
                                                    {{ $comp->updated_at->format('d/m/Y H:i') }}
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('comparisons.show', $comp) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye mr-1"></i>{{ __('Ver') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if ($comparisons->hasPages())
                            <div class="d-flex justify-content-center mt-3">
                                {{ $comparisons->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Nueva comparación --}}
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-plus-circle mr-1"></i>{{ __('Nueva comparación') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if ($quizzes->count() < 2)
                        <div class="text-center py-4">
                            <i class="fas fa-info-circle fa-2x text-gray-300 mb-3"></i>
                            <p class="text-muted">{{ __('Necesitas al menos 2 encuestas cerradas para comparar resultados.') }}</p>
                        </div>
                    @else
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-lightbulb mr-1"></i>
                            {{ __('Para obtener resultados significativos, las encuestas deben tener relación temática (por ejemplo, satisfacción de diferentes periodos).') }}
                        </div>

                        <form action="{{ route('comparisons.compare') }}" method="POST">
                            @csrf

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="quiz_a" class="font-weight-bold">
                                        <i class="fas fa-file-alt text-primary mr-1"></i>{{ __('Encuesta A') }}
                                    </label>
                                    <select name="quiz_a" id="quiz_a" class="form-control @error('quiz_a') is-invalid @enderror" required>
                                        <option value="">{{ __('Seleccionar...') }}</option>
                                        @foreach ($quizzes as $quiz)
                                            <option value="{{ $quiz->id }}" @selected(old('quiz_a') == $quiz->id)>
                                                {{ $quiz->title }} ({{ $quiz->attempts_count }} {{ __('respuestas') }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('quiz_a')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="quiz_b" class="font-weight-bold">
                                        <i class="fas fa-file-alt text-info mr-1"></i>{{ __('Encuesta B') }}
                                    </label>
                                    <select name="quiz_b" id="quiz_b" class="form-control @error('quiz_b') is-invalid @enderror" required>
                                        <option value="">{{ __('Seleccionar...') }}</option>
                                        @foreach ($quizzes as $quiz)
                                            <option value="{{ $quiz->id }}" @selected(old('quiz_b') == $quiz->id)>
                                                {{ $quiz->title }} ({{ $quiz->attempts_count }} {{ __('respuestas') }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('quiz_b')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex flex-wrap justify-content-center mt-3">
                                <button type="submit" class="btn btn-primary mr-2 mb-2">
                                    <i class="fas fa-chart-bar mr-1"></i>{{ __('Comparar resultados') }}
                                </button>
                                <button type="submit" formaction="{{ route('comparisons.ai') }}" class="btn btn-success mb-2 js-show-loader">
                                    <i class="fas fa-robot mr-1"></i>{{ __('Comparar con IA (y guardar)') }}
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
