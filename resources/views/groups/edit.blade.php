<x-app-layout>
    <x-slot name="header">{{ __('Editar grupo') }}</x-slot>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ $group->name }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('groups.update', $group) }}" method="POST">
                        @csrf @method('PUT')
                        @include('groups._form', ['group' => $group])

                        <hr>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('groups.show', $group) }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left mr-1"></i>{{ __('Cancelar') }}
                            </a>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-save mr-1"></i>{{ __('Guardar cambios') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

