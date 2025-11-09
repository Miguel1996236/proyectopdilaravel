<x-app-layout>
    <x-slot name="header">
        {{ __('Crear encuesta') }}
    </x-slot>

    <div class="row">
        <div class="col-lg-9">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Datos generales de la encuesta') }}</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('quizzes.store') }}">
                        @include('quizzes._form')

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('quizzes.index') }}" class="btn btn-light mr-2">
                                {{ __('Cancelar') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i>{{ __('Guardar encuesta') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Ayuda rápida') }}</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        {{ __('Crea encuestas para medir satisfacción del curso, diagnóstico inicial u otros objetivos pedagógicos. Podrás agregar preguntas una vez guardes la encuesta.') }}
                    </p>
                    <ul class="small pl-3 mb-0">
                        <li>{{ __('El estado "Borrador" mantiene la encuesta oculta a los participantes.') }}</li>
                        <li>{{ __('El estado "Publicada" genera automáticamente un código de invitación.') }}</li>
                        <li>{{ __('Puedes limitar la disponibilidad con fechas de apertura y cierre.') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

