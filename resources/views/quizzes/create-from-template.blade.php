<x-app-layout>
    <x-slot name="header">
        {{ __('Crear encuesta desde plantilla') }}
    </x-slot>

    <div class="row">
        <div class="col-12">
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Elige una plantilla') }}</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        {{ __('Las plantillas incluyen preguntas predefinidas listas para usar. Solo personaliza el título y la descripción.') }}
                    </p>

                    <div class="row">
                        @foreach ($templates as $key => $template)
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100 border-left-primary shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="rounded-circle bg-light p-2 mr-3">
                                                <i class="fas {{ $template['icon'] ?? 'fa-list' }} text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="font-weight-bold text-dark mb-1">{{ $template['name'] }}</h6>
                                                <p class="small text-muted mb-0">
                                                    {{ Str::limit($template['description'] ?? '', 100) }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="small text-muted mb-3">
                                            <i class="fas fa-question-circle mr-1"></i>
                                            {{ count($template['questions'] ?? []) }} {{ __('preguntas') }}
                                        </div>
                                        <button type="button" class="btn btn-sm btn-primary btn-block use-template-btn"
                                                data-template-key="{{ $key }}"
                                                data-template-name="{{ $template['name'] }}"
                                                data-template-description="{{ $template['description'] ?? '' }}">
                                            <i class="fas fa-magic mr-1"></i>{{ __('Usar esta plantilla') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('quizzes.index') }}" class="btn btn-light">
                            <i class="fas fa-arrow-left mr-1"></i>{{ __('Volver') }}
                        </a>
                        <a href="{{ route('quizzes.create') }}" class="btn btn-outline-secondary">
                            {{ __('Crear encuesta en blanco') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para confirmar y personalizar -->
    <div class="modal fade" id="templateModal" tabindex="-1" role="dialog" aria-labelledby="templateModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('quizzes.store-from-template') }}" id="templateForm">
                    @csrf
                    <input type="hidden" name="template_key" id="modal_template_key" value="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="templateModalLabel">{{ __('Crear encuesta desde plantilla') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-3" id="modal_template_description"></p>
                        <div class="form-group">
                            <label for="modal_title" class="font-weight-bold">{{ __('Título de la encuesta') }} <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="modal_title" class="form-control @error('title') is-invalid @enderror"
                                   placeholder="{{ __('Ej: Evaluación del profesor - Matemáticas 2025') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="modal_description" class="font-weight-bold">{{ __('Descripción (opcional)') }}</label>
                            <textarea name="description" id="modal_description" rows="3" class="form-control"
                                      placeholder="{{ __('Describe brevemente el contexto de esta encuesta') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">{{ __('Cancelar') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check mr-1"></i>{{ __('Crear encuesta') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.use-template-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const key = this.dataset.templateKey;
                    const name = this.dataset.templateName;
                    const description = this.dataset.templateDescription || '';

                    document.getElementById('modal_template_key').value = key;
                    document.getElementById('modal_template_description').textContent = description;
                    document.getElementById('modal_title').value = name;
                    document.getElementById('modal_description').value = '';

                    $('#templateModal').modal('show');
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
