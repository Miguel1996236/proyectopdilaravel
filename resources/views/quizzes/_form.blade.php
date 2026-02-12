@csrf

<div class="form-group">
    <label for="title" class="font-weight-bold">{{ __('Título de la encuesta') }}</label>
    <input type="text" name="title" id="title" value="{{ old('title', $quiz->title) }}"
           class="form-control @error('title') is-invalid @enderror" required>
    @error('title')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="description" class="font-weight-bold">{{ __('Descripción') }}</label>
    <textarea name="description" id="description" rows="3"
              class="form-control @error('description') is-invalid @enderror"
              placeholder="{{ __('Describe brevemente el propósito de la encuesta') }}">{{ old('description', $quiz->description) }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-row">
    <div class="form-group col-md-4">
        <label for="status" class="font-weight-bold">{{ __('Estado') }}</label>
        <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
            @foreach (['draft' => 'Borrador', 'published' => 'Publicada', 'closed' => 'Cerrada'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $quiz->status) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-4">
        <label for="opens_at" class="font-weight-bold">{{ __('Disponible desde') }}</label>
        <input type="datetime-local" name="opens_at" id="opens_at"
               value="{{ old('opens_at', optional($quiz->opens_at)->format('Y-m-d\TH:i')) }}"
               class="form-control @error('opens_at') is-invalid @enderror">
        @error('opens_at')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-4">
        <label for="closes_at" class="font-weight-bold">{{ __('Disponible hasta') }}</label>
        <input type="datetime-local" name="closes_at" id="closes_at"
               value="{{ old('closes_at', optional($quiz->closes_at)->format('Y-m-d\TH:i')) }}"
               class="form-control @error('closes_at') is-invalid @enderror">
        @error('closes_at')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-4">
        <label for="max_attempts" class="font-weight-bold">{{ __('Intentos por participante') }}</label>
        <input type="number" name="max_attempts" id="max_attempts" min="1"
               value="{{ old('max_attempts', $quiz->max_attempts ?? 1) }}"
               class="form-control @error('max_attempts') is-invalid @enderror">
        @error('max_attempts')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-4">
        <label for="target_audience" class="font-weight-bold">{{ __('Dirigida a') }}</label>
        <select name="target_audience" id="target_audience" class="form-control @error('target_audience') is-invalid @enderror">
            <option value="all" @selected(old('target_audience', $quiz->target_audience ?? 'all') === 'all')>{{ __('Todos los usuarios') }}</option>
            <option value="students" @selected(old('target_audience', $quiz->target_audience ?? 'all') === 'students')>{{ __('Solo estudiantes') }}</option>
            <option value="teachers" @selected(old('target_audience', $quiz->target_audience ?? 'all') === 'teachers')>{{ __('Solo docentes') }}</option>
        </select>
        <small class="form-text text-muted">{{ __('Define quién puede responder esta encuesta') }}</small>
        @error('target_audience')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-4 d-flex align-items-center">
        <div class="custom-control custom-switch mt-4">
            <input type="checkbox" class="custom-control-input" id="require_login" name="require_login"
                   value="1" @checked(old('require_login', $quiz->require_login ?? true))>
            <label class="custom-control-label" for="require_login">{{ __('Requiere autenticación') }}</label>
        </div>
        @error('require_login')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-4 d-flex align-items-center">
        <div class="custom-control custom-switch mt-2">
            <input type="checkbox" class="custom-control-input" id="randomize_questions" name="randomize_questions"
                   value="1" @checked(old('randomize_questions', $quiz->randomize_questions ?? false))>
            <label class="custom-control-label" for="randomize_questions">{{ __('Aleatorizar preguntas') }}</label>
        </div>
        <small class="form-text text-muted ml-2 mt-2">{{ __('Las preguntas aparecerán en orden aleatorio') }}</small>
        @error('randomize_questions')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-4">
        <label for="theme_color" class="font-weight-bold">{{ __('Color del tema') }}</label>
        <div class="d-flex align-items-center">
            <input type="color" name="theme_color" id="theme_color"
                   value="{{ old('theme_color', $quiz->theme_color ?? '#4e73df') }}"
                   class="form-control form-control-color" style="width: 50px; height: 38px; padding: 2px; cursor: pointer;">
            <input type="text" id="theme_color_text" value="{{ old('theme_color', $quiz->theme_color ?? '#4e73df') }}"
                   class="form-control ml-2" style="max-width: 100px;" readonly>
        </div>
        <small class="form-text text-muted">{{ __('Color principal visible al responder la encuesta') }}</small>
    </div>
</div>

<div class="form-group">
    <label for="settings_additional_instructions" class="font-weight-bold">{{ __('Instrucciones adicionales (opcional)') }}</label>
    <textarea name="settings[additional_instructions]" id="settings_additional_instructions" rows="3"
              class="form-control">{{ old('settings.additional_instructions', data_get($quiz->settings, 'additional_instructions')) }}</textarea>
    <small class="form-text text-muted">{{ __('Mensaje visible para los participantes antes de responder la encuesta.') }}</small>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const colorInput = document.getElementById('theme_color');
        const colorText = document.getElementById('theme_color_text');
        if (colorInput && colorText) {
            colorInput.addEventListener('input', function() {
                colorText.value = this.value;
            });
        }
    });
</script>
@endpush

