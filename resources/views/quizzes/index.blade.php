@php
    $user = auth()->user();
@endphp

<x-app-layout>
    <x-slot name="header">
        {{ __('Encuestas') }}
    </x-slot>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                {{ __('Listado de encuestas') }}
            </h6>
            @if (in_array($user->role, [$user::ROLE_ADMIN, $user::ROLE_TEACHER], true))
                <div class="d-flex flex-wrap">
                    <a href="{{ route('quizzes.create') }}" class="btn btn-sm btn-primary btn-icon-split">
                        <span class="icon text-white-50">
                            <i class="fas fa-plus"></i>
                        </span>
                        <span class="text">{{ __('Nueva encuesta') }}</span>
                    </a>
                    <a href="{{ route('quizzes.create-from-template') }}" class="btn btn-sm btn-outline-primary ml-2" title="{{ __('Crear desde plantilla predefinida') }}">
                        <i class="fas fa-magic mr-1"></i>{{ __('Desde plantilla') }}
                    </a>
                </div>
            @endif
        </div>
        <div class="card-body p-0">
            @if ($quizzes->count())
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ __('Título') }}</th>
                                <th class="d-none d-md-table-cell">{{ __('Estado') }}</th>
                                <th class="d-none d-lg-table-cell">{{ __('Periodo') }}</th>
                                <th class="text-center">{{ __('Preguntas') }}</th>
                                <th class="text-center">{{ __('Respuestas') }}</th>
                                <th class="text-right">{{ __('Acciones') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($quizzes as $quiz)
                            <tr>
                                <td>
                                    <div class="font-weight-bold text-dark">{{ $quiz->title }}</div>
                                    @if ($quiz->description)
                                        <div class="text-muted small">{{ \Illuminate\Support\Str::limit($quiz->description, 90) }}</div>
                                    @endif
                                </td>
                                <td class="d-none d-md-table-cell">
                                    @php
                                        $statusClasses = [
                                            'draft' => 'badge badge-warning',
                                            'published' => 'badge badge-success',
                                            'closed' => 'badge badge-secondary',
                                        ];
                                        $statusLabels = [
                                            'draft' => __('Borrador'),
                                            'published' => __('Publicada'),
                                            'closed' => __('Cerrada'),
                                        ];
                                    @endphp
                                    <span class="{{ $statusClasses[$quiz->status] ?? 'badge badge-light' }}">
                                        {{ $statusLabels[$quiz->status] ?? ucfirst($quiz->status) }}
                                    </span>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <div class="small text-muted">
                                        @if ($quiz->opens_at)
                                            <span class="d-block"><i class="fas fa-play mr-1 text-success"></i>{{ $quiz->opens_at->format('d/m/Y H:i') }}</span>
                                        @else
                                            <span class="d-block"><i class="fas fa-play mr-1 text-muted"></i>{{ __('Sin fecha de inicio') }}</span>
                                        @endif
                                        @if ($quiz->closes_at)
                                            <span class="d-block"><i class="fas fa-stop mr-1 text-danger"></i>{{ $quiz->closes_at->format('d/m/Y H:i') }}</span>
                                        @else
                                            <span class="d-block"><i class="fas fa-stop mr-1 text-muted"></i>{{ __('Sin fecha de cierre') }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info">{{ $quiz->questions_count }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-primary">{{ $quiz->attempts_count }}</span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('quizzes.show', $quiz) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Ver detalles') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if ($quiz->status !== 'closed')
                                        <a href="{{ route('quizzes.edit', $quiz) }}" class="btn btn-sm btn-outline-primary" title="{{ __('Editar encuesta') }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    <form action="{{ route('quizzes.destroy', $quiz) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('¿Estás seguro de eliminar esta encuesta?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('Eliminar') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $quizzes->links('pagination::bootstrap-4') }}
                </div>
            @else
                <div class="p-5 text-center">
                    <img src="{{ asset('img/undraw_rocket.svg') }}" alt="{{ __('Sin encuestas') }}" width="120" class="mb-4 opacity-75">
                    <h5 class="text-muted mb-3">{{ __('Aún no tienes encuestas creadas') }}</h5>
                    @if (in_array($user->role, [$user::ROLE_ADMIN, $user::ROLE_TEACHER], true))
                        <div class="d-flex flex-column flex-sm-row justify-content-center">
                            <a href="{{ route('quizzes.create') }}" class="btn btn-primary mb-2 mb-sm-0 mr-sm-2">
                                <i class="fas fa-plus mr-1"></i>{{ __('Crear encuesta') }}
                            </a>
                            <a href="{{ route('quizzes.create-from-template') }}" class="btn btn-outline-primary">
                                <i class="fas fa-magic mr-1"></i>{{ __('Desde plantilla') }}
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

