<x-app-layout>
    <x-slot name="header">
        {{ __('Agregar pregunta a la encuesta') }}: <span class="text-primary">{{ $quiz->title }}</span>
    </x-slot>

    <div class="row">
        <div class="col-lg-9">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Nueva pregunta') }}</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('quizzes.questions.store', $quiz) }}">
                        @csrf
                        @include('questions._form')

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('quizzes.edit', $quiz) }}" class="btn btn-light">
                                <i class="fas fa-arrow-left mr-1"></i>{{ __('Regresar') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i>{{ __('Guardar pregunta') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Consejos') }}</h6>
                </div>
                <div class="card-body">
                    <ul class="small pl-3 mb-0">
                        <li>{{ __('Formula la pregunta de forma clara y directa.') }}</li>
                        <li>{{ __('Define respuestas correctas para obtener análisis más precisos.') }}</li>
                        <li>{{ __('Usa el tipo escala para medir satisfacción o niveles de acuerdo.') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

