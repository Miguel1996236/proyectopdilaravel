<x-app-layout>
    <x-slot name="header">{{ __('Crear grupo') }}</x-slot>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Nuevo grupo de estudiantes') }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('groups.store') }}" method="POST">
                        @csrf
                        @include('groups._form', ['group' => null])

                        <hr>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('groups.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left mr-1"></i>{{ __('Cancelar') }}
                            </a>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-save mr-1"></i>{{ __('Crear grupo') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info"><i class="fas fa-info-circle mr-1"></i>{{ __('Información') }}</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">{{ __('Los grupos te permiten organizar a tus estudiantes para enviarles recordatorios y notificaciones de manera eficiente.') }}</p>
                    <ul class="small text-muted">
                        <li>{{ __('Puedes agregar estudiantes por nombre y correo.') }}</li>
                        <li>{{ __('Si el correo coincide con un usuario registrado, se vinculará automáticamente.') }}</li>
                        <li>{{ __('Puedes exportar la lista del grupo a Excel.') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

