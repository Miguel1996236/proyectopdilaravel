<x-app-layout>
    <x-slot name="header">{{ __('Comparar encuestas') }}</x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-exchange-alt mr-1"></i>{{ __('Selecciona dos encuestas para comparar') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if ($quizzes->count() < 2)
                        <div class="text-center py-5">
                            <i class="fas fa-info-circle fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">{{ __('Necesitas al menos 2 encuestas cerradas para comparar resultados.') }}</p>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            <strong>{{ __('Importante:') }}</strong>
                            {{ __('Para obtener resultados significativos, las encuestas deben tener relación temática. Por ejemplo, encuestas de satisfacción de diferentes periodos.') }}
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
                                                {{ $quiz->title }} ({{ $quiz->attempts_count }} intentos)
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
                                                {{ $quiz->title }} ({{ $quiz->attempts_count }} intentos)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('quiz_b')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-center mt-3">
                                <button type="submit" class="btn btn-primary mr-2">
                                    <i class="fas fa-chart-bar mr-1"></i>{{ __('Comparar resultados') }}
                                </button>
                                <button type="submit" formaction="{{ route('comparisons.ai') }}" class="btn btn-success js-show-loader">
                                    <i class="fas fa-robot mr-1"></i>{{ __('Comparar con IA') }}
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

