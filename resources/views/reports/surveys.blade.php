<x-app-layout>
    <x-slot name="header">
        {{ __('Reporte de encuestas') }}
    </x-slot>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('Filtros de búsqueda') }}</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.surveys') }}" class="row">
                <div class="col-md-3 mb-3">
                    <label for="search" class="form-label">{{ __('Buscar') }}</label>
                    <input type="text" 
                           class="form-control" 
                           id="search" 
                           name="search" 
                           value="{{ $filters['search'] ?? '' }}" 
                           placeholder="{{ __('Título o descripción...') }}">
                </div>
                <div class="col-md-2 mb-3">
                    <label for="status" class="form-label">{{ __('Estado') }}</label>
                    <select class="form-control" id="status" name="status">
                        <option value="">{{ __('Todos') }}</option>
                        <option value="draft" {{ ($filters['status'] ?? '') === 'draft' ? 'selected' : '' }}>{{ __('Borrador') }}</option>
                        <option value="published" {{ ($filters['status'] ?? '') === 'published' ? 'selected' : '' }}>{{ __('Publicada') }}</option>
                        <option value="closed" {{ ($filters['status'] ?? '') === 'closed' ? 'selected' : '' }}>{{ __('Cerrada') }}</option>
                    </select>
                </div>
                @if($teachers->isNotEmpty())
                    <div class="col-md-2 mb-3">
                        <label for="owner" class="form-label">{{ __('Docente') }}</label>
                        <select class="form-control" id="owner" name="owner">
                            <option value="">{{ __('Todos') }}</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ ($filters['owner'] ?? '') == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-2 mb-3">
                    <label for="date_from" class="form-label">{{ __('Desde') }}</label>
                    <input type="date" 
                           class="form-control" 
                           id="date_from" 
                           name="date_from" 
                           value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div class="col-md-2 mb-3">
                    <label for="date_to" class="form-label">{{ __('Hasta') }}</label>
                    <input type="date" 
                           class="form-control" 
                           id="date_to" 
                           name="date_to" 
                           value="{{ $filters['date_to'] ?? '' }}">
                </div>
                <div class="col-md-1 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <div class="col-md-12">
                    <a href="{{ route('reports.surveys') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-redo mr-1"></i>{{ __('Limpiar filtros') }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row">
        @if(isset($charts['status_distribution']) && $charts['status_distribution'])
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">{{ __('Distribución por estado') }}</h6>
                    </div>
                    <div class="card-body">
                        {!! $charts['status_distribution']->container() !!}
                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                <span class="badge badge-primary">{{ __('Publicadas') }}: {{ $statusStats['published'] }}</span>
                                <span class="badge badge-success ml-2">{{ __('Cerradas') }}: {{ $statusStats['closed'] }}</span>
                                <span class="badge badge-secondary ml-2">{{ __('Borradores') }}: {{ $statusStats['draft'] }}</span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(isset($charts['monthly_trends']) && $charts['monthly_trends'])
            <div class="col-xl-6 col-lg-6 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">{{ __('Tendencias mensuales') }}</h6>
                    </div>
                    <div class="card-body">
                        {!! $charts['monthly_trends']->container() !!}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Tabla de encuestas -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('Clasificación de encuestas') }}</h6>
            <span class="badge badge-primary">{{ $surveys->total() }} {{ __('encuestas') }}</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ __('Título') }}</th>
                            <th>{{ __('Propietario') }}</th>
                            <th class="text-center">{{ __('Estado') }}</th>
                            <th class="text-center">{{ __('Preguntas') }}</th>
                            <th class="text-center">{{ __('Intentos') }}</th>
                            <th class="text-center">{{ __('Participación') }}</th>
                            <th class="text-center">{{ __('Análisis IA') }}</th>
                            <th class="text-center">{{ __('Creada') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($surveys as $survey)
                            <tr>
                                <td>
                                    <strong>{{ $survey->title }}</strong>
                                    @if($survey->description)
                                        <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($survey->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($survey->owner)
                                        <small>{{ $survey->owner->name }}</small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php
                                        $statusColors = [
                                            'draft' => 'secondary',
                                            'published' => 'primary',
                                            'closed' => 'success',
                                        ];
                                        $statusLabels = [
                                            'draft' => __('Borrador'),
                                            'published' => __('Publicada'),
                                            'closed' => __('Cerrada'),
                                        ];
                                    @endphp
                                    <span class="badge badge-{{ $statusColors[$survey->status] ?? 'secondary' }}">
                                        {{ $statusLabels[$survey->status] ?? $survey->status }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info">{{ $survey->questions_count }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-primary">{{ $survey->attempts_count }}</span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $rate = $survey->participation_rate ?? 0;
                                        $rateColor = $rate >= 70 ? 'success' : ($rate >= 40 ? 'warning' : 'danger');
                                    @endphp
                                    <span class="badge badge-{{ $rateColor }}">{{ number_format($rate, 1) }}%</span>
                                </td>
                                <td class="text-center">
                                    @if($survey->analyses_count > 0)
                                        <span class="badge badge-success">
                                            <i class="fas fa-check mr-1"></i>{{ $survey->analyses_count }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <small class="text-muted">{{ $survey->created_at->format('d/m/Y') }}</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    {{ __('No se encontraron encuestas con los filtros aplicados.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="d-flex justify-content-center mt-3">
                {{ $surveys->links() }}
            </div>
        </div>
    </div>

    @push('scripts')
        @if(isset($charts['status_distribution']) && $charts['status_distribution'])
            {!! $charts['status_distribution']->script() !!}
        @endif
        @if(isset($charts['monthly_trends']) && $charts['monthly_trends'])
            {!! $charts['monthly_trends']->script() !!}
        @endif
    @endpush
</x-app-layout>
