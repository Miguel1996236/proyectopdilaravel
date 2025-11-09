<x-app-layout>
    <x-slot name="header">
        {{ __('Editar pregunta') }}: <span class="text-primary">{{ $quiz->title }}</span>
    </x-slot>

    <div class="row">
        <div class="col-lg-9">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Modificar pregunta') }}</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('quizzes.questions.update', [$quiz, $question]) }}">
                        @csrf
                        @method('PUT')

                        @include('questions._form')

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('quizzes.edit', $quiz) }}" class="btn btn-light">
                                <i class="fas fa-arrow-left mr-1"></i>{{ __('Regresar') }}
                            </a>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i>{{ __('Guardar cambios') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Atajos') }}</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        {{ __('Recuerda guardar antes de salir de esta página. Si cambias el tipo de pregunta, deberás definir nuevamente las opciones.') }}
                    </p>
                    <form action="{{ route('quizzes.questions.destroy', [$quiz, $question]) }}" method="POST" onsubmit="return confirm('{{ __('¿Deseas eliminar esta pregunta?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-block">
                            <i class="fas fa-trash mr-1"></i>{{ __('Eliminar pregunta') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

