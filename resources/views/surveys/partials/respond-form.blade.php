@php $themeColor = $quiz->theme_color ?? '#4e73df'; @endphp

@push('styles')
<style>
    .survey-header-card  { border-top: 5px solid {{ $themeColor }}; border-radius: .35rem; }
    .survey-theme-badge  { background-color: {{ $themeColor }} !important; color: #fff !important; }
    .survey-theme-btn    { background-color: {{ $themeColor }} !important; border-color: {{ $themeColor }} !important; color: #fff !important; }
    .survey-theme-btn:hover { filter: brightness(0.88); color: #fff !important; }
    .survey-question-card {
        border: 1px solid #e3e6f0;
        border-left: 4px solid {{ $themeColor }};
        border-radius: .35rem;
        transition: box-shadow .15s ease;
    }
    .survey-question-card:hover { box-shadow: 0 .15rem .6rem rgba(0,0,0,.08); }
    .survey-question-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.75rem;
        height: 1.75rem;
        border-radius: 50%;
        background-color: {{ $themeColor }};
        color: #fff;
        font-size: .8rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    .survey-progress-bar { height: 6px; border-radius: 3px; background: #e9ecef; overflow: hidden; }
    .survey-progress-fill { height: 100%; border-radius: 3px; background: {{ $themeColor }}; transition: width .3s ease; }
</style>
@endpush

{{-- =================== ENCABEZADO =================== --}}
<div class="card shadow-sm mb-3 survey-header-card">
    <div class="card-body py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start">
            <div class="flex-grow-1 mb-2 mb-md-0">
                <h1 class="h4 font-weight-bold text-gray-900 mb-1">{{ $quiz->title }}</h1>
                @if ($quiz->description)
                    <p class="text-muted mb-0" style="font-size:.9rem;">{{ $quiz->description }}</p>
                @endif
            </div>
            <span class="badge badge-pill survey-theme-badge px-3 py-2 text-uppercase" style="font-size:.75rem;">
                <i class="fas fa-key mr-1"></i>{{ $invitation->code }}
            </span>
        </div>
    </div>
</div>

{{-- =================== ALERTAS =================== --}}
@if ($errors->has('general'))
    <div class="alert alert-danger small shadow-sm">
        <i class="fas fa-exclamation-circle mr-1"></i>{{ $errors->first('general') }}
    </div>
@endif

@if (session('status'))
    <div class="alert alert-success small shadow-sm">
        <i class="fas fa-check-circle mr-1"></i>{{ session('status') }}
    </div>
@endif

<form method="POST" action="{{ route('surveys.respond.submit', $invitation->code) }}" class="js-show-loader" id="surveyForm">
    @csrf

    {{-- =================== DATOS DEL PARTICIPANTE =================== --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <h6 class="font-weight-bold text-gray-800 mb-3">
                <i class="fas fa-user-edit mr-1 text-muted"></i>{{ __('Información del participante') }}
            </h6>
            <div class="form-row">
                <div class="form-group col-md-6 mb-md-0">
                    <label class="small font-weight-bold text-gray-600" for="participant_name">{{ __('Nombre') }} <span class="text-muted font-weight-normal">({{ __('opcional') }})</span></label>
                    <input type="text" id="participant_name" name="participant_name"
                           class="form-control @error('participant_name') is-invalid @enderror"
                           value="{{ old('participant_name', auth()->user()->name ?? '') }}"
                           placeholder="{{ __('Tu nombre completo') }}">
                </div>
                <div class="form-group col-md-6 mb-0">
                    <label class="small font-weight-bold text-gray-600" for="participant_email">{{ __('Correo electrónico') }} <span class="text-muted font-weight-normal">({{ __('opcional') }})</span></label>
                    <input type="email" id="participant_email" name="participant_email"
                           class="form-control @error('participant_email') is-invalid @enderror"
                           value="{{ old('participant_email', auth()->user()->email ?? '') }}"
                           placeholder="{{ __('correo@ejemplo.com') }}">
                    @error('participant_email')
                        <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- =================== BARRA DE PROGRESO =================== --}}
    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <small class="text-muted font-weight-bold">
                <i class="fas fa-tasks mr-1"></i>{{ __('Progreso') }}
            </small>
            <small class="text-muted font-weight-bold" id="surveyProgressText">0 / {{ count($questions) }}</small>
        </div>
        <div class="survey-progress-bar">
            <div class="survey-progress-fill" id="surveyProgressFill" style="width: 0%"></div>
        </div>
    </div>

    {{-- =================== PREGUNTAS =================== --}}
    @foreach ($questions as $index => $question)
        <div class="card shadow-sm mb-3 survey-question-card" data-question-card="{{ $question->id }}">
            <div class="card-body">
                {{-- Encabezado de la pregunta --}}
                <div class="d-flex align-items-start mb-3">
                    <span class="survey-question-number mr-2 mt-1">{{ $index + 1 }}</span>
                    <div class="flex-grow-1">
                        <h6 class="font-weight-bold text-gray-900 mb-0">
                            {{ $question->title }}
                            <span class="text-danger" title="{{ __('Obligatoria') }}">*</span>
                        </h6>
                        @if ($question->description)
                            <p class="text-muted small mb-0 mt-1">{{ $question->description }}</p>
                        @endif
                    </div>
                </div>

                @php
                    $fieldName  = "responses.{$question->id}";
                    $fieldError = $errors->first($fieldName) ?: $errors->first("{$fieldName}.*");
                @endphp

                {{-- Cuerpo según tipo de pregunta --}}
                @switch($question->type)
                    @case('multiple_choice')
                        <div class="pl-4">
                            @foreach ($question->options as $option)
                                <div class="custom-control custom-radio mb-2">
                                    <input class="custom-control-input survey-input @if($fieldError) is-invalid @endif"
                                           type="radio"
                                           id="question-{{ $question->id }}-option-{{ $option->id }}"
                                           name="responses[{{ $question->id }}]"
                                           value="{{ $option->id }}"
                                           {{ (string) $option->id === (string) old("responses.{$question->id}") ? 'checked' : '' }}
                                           required>
                                    <label class="custom-control-label" for="question-{{ $question->id }}-option-{{ $option->id }}">
                                        {{ $option->label }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @break

                    @case('multi_select')
                        @php
                            $selected = collect(old("responses.{$question->id}", []))->map(fn ($value) => (int) $value)->all();
                        @endphp
                        <div class="pl-4">
                            <small class="text-muted d-block mb-2"><i class="fas fa-info-circle mr-1"></i>{{ __('Puedes seleccionar varias') }}</small>
                            @foreach ($question->options as $option)
                                <div class="custom-control custom-checkbox mb-2">
                                    <input class="custom-control-input survey-input @if($fieldError) is-invalid @endif"
                                           type="checkbox"
                                           id="question-{{ $question->id }}-option-{{ $option->id }}"
                                           name="responses[{{ $question->id }}][]"
                                           value="{{ $option->id }}"
                                           {{ in_array($option->id, $selected, true) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="question-{{ $question->id }}-option-{{ $option->id }}">
                                        {{ $option->label }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @break

                    @case('scale')
                        @php
                            $min     = data_get($question->settings, 'scale_min', 1);
                            $max     = data_get($question->settings, 'scale_max', 5);
                            $step    = max(1, data_get($question->settings, 'scale_step', 1));
                            $current = old("responses.{$question->id}");
                        @endphp
                        <div class="pl-4">
                            <select class="custom-select survey-input @if($fieldError) is-invalid @endif"
                                    name="responses[{{ $question->id }}]" required style="max-width: 280px;">
                                <option value="">{{ __('Selecciona una opción') }}</option>
                                @for ($value = $min; $value <= $max; $value += $step)
                                    <option value="{{ $value }}" {{ (string) $value === (string) $current ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        @break

                    @case('open_text')
                        <div class="pl-4">
                            <textarea class="form-control survey-input @if($fieldError) is-invalid @endif"
                                      name="responses[{{ $question->id }}]" rows="4" required
                                      placeholder="{{ __('Escribe tu respuesta aquí...') }}">{{ old("responses.{$question->id}") }}</textarea>
                        </div>
                        @break

                    @case('numeric')
                        <div class="pl-4">
                            <input type="number" step="any"
                                   class="form-control survey-input @if($fieldError) is-invalid @endif"
                                   name="responses[{{ $question->id }}]"
                                   value="{{ old("responses.{$question->id}") }}"
                                   placeholder="{{ __('Ingresa un valor numérico') }}"
                                   required style="max-width: 280px;">
                        </div>
                        @break

                    @default
                        <p class="text-muted small pl-4">{{ __('Tipo de pregunta no soportado actualmente.') }}</p>
                @endswitch

                @if ($fieldError)
                    <div class="pl-4 mt-2">
                        <span class="text-danger small"><i class="fas fa-exclamation-triangle mr-1"></i>{{ $fieldError }}</span>
                    </div>
                @endif
            </div>
        </div>
    @endforeach

    {{-- =================== BOTÓN ENVIAR =================== --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body text-center py-4">
            <p class="text-muted small mb-3">
                <i class="fas fa-lock mr-1"></i>{{ __('Tus respuestas son confidenciales y se utilizarán únicamente con fines académicos.') }}
            </p>
            <button type="submit" class="btn survey-theme-btn btn-lg px-5 font-weight-bold">
                <i class="fas fa-paper-plane mr-2"></i>{{ __('Enviar respuestas') }}
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form    = document.getElementById('surveyForm');
    const total   = {{ count($questions) }};
    const fillBar = document.getElementById('surveyProgressFill');
    const progTxt = document.getElementById('surveyProgressText');

    function updateProgress() {
        let answered = 0;
        form.querySelectorAll('[data-question-card]').forEach(function(card) {
            const inputs = card.querySelectorAll('.survey-input');
            let hasValue = false;
            inputs.forEach(function(input) {
                if (input.type === 'radio' || input.type === 'checkbox') {
                    if (input.checked) hasValue = true;
                } else if (input.tagName === 'SELECT') {
                    if (input.value !== '') hasValue = true;
                } else {
                    if (input.value.trim() !== '') hasValue = true;
                }
            });
            if (hasValue) answered++;
        });

        const pct = total > 0 ? Math.round((answered / total) * 100) : 0;
        if (fillBar) fillBar.style.width = pct + '%';
        if (progTxt) progTxt.textContent = answered + ' / ' + total;
    }

    // Escuchar cambios en todos los inputs
    form.querySelectorAll('.survey-input').forEach(function(el) {
        el.addEventListener('change', updateProgress);
        el.addEventListener('input', updateProgress);
    });

    // Inicializar
    updateProgress();
});
</script>
@endpush
