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
        <label class="font-weight-bold">
            <i class="fas fa-calendar-check text-success mr-1"></i>{{ __('Disponible desde') }}
        </label>
        <input type="date" id="opens_at_date"
               value="{{ old('opens_at') ? \Illuminate\Support\Str::before(old('opens_at'), 'T') : optional($quiz->opens_at)?->format('Y-m-d') }}"
               class="form-control mb-2 @error('opens_at') is-invalid @enderror">
        @php
            $opensHour24 = old('opens_at')
                ? (int) \Illuminate\Support\Str::before(\Illuminate\Support\Str::after(old('opens_at'), 'T'), ':')
                : optional($quiz->opens_at)?->format('G');
            $opensMin = old('opens_at')
                ? \Illuminate\Support\Str::after(\Illuminate\Support\Str::after(old('opens_at'), 'T'), ':')
                : optional($quiz->opens_at)?->format('i');
            $opensAmpm = $opensHour24 !== null ? ($opensHour24 >= 12 ? 'PM' : 'AM') : null;
            $opensHour12 = $opensHour24 !== null ? (($opensHour24 % 12) ?: 12) : null;
        @endphp
        <div class="d-flex align-items-center">
            <select id="opens_at_hour" class="form-control form-control-sm @error('opens_at') is-invalid @enderror">
                <option value="">{{ __('Hora') }}</option>
                @for ($h = 1; $h <= 12; $h++)
                    <option value="{{ $h }}" @selected($opensHour12 !== null && (int)$opensHour12 === $h)>{{ $h }}</option>
                @endfor
            </select>
            <span class="mx-1 font-weight-bold">:</span>
            <select id="opens_at_min" class="form-control form-control-sm @error('opens_at') is-invalid @enderror">
                <option value="">{{ __('Min') }}</option>
                @foreach (['00','05','10','15','20','25','30','35','40','45','50','55'] as $m)
                    <option value="{{ $m }}" @selected($opensMin !== null && $opensMin === $m)>{{ $m }}</option>
                @endforeach
            </select>
            <select id="opens_at_ampm" class="form-control form-control-sm ml-1 @error('opens_at') is-invalid @enderror">
                <option value="AM" @selected($opensAmpm === 'AM')>AM</option>
                <option value="PM" @selected($opensAmpm === 'PM')>PM</option>
            </select>
        </div>
        <input type="hidden" name="opens_at" id="opens_at"
               value="{{ old('opens_at', optional($quiz->opens_at)?->format('Y-m-d\TH:i')) }}">
        <small class="form-text text-muted">{{ __('Fecha y hora en que la encuesta estará disponible') }}</small>
        @error('opens_at')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-4">
        <label class="font-weight-bold">
            <i class="fas fa-calendar-times text-danger mr-1"></i>{{ __('Disponible hasta') }}
        </label>
        <input type="date" id="closes_at_date"
               value="{{ old('closes_at') ? \Illuminate\Support\Str::before(old('closes_at'), 'T') : optional($quiz->closes_at)?->format('Y-m-d') }}"
               class="form-control mb-2 @error('closes_at') is-invalid @enderror">
        @php
            $closesHour24 = old('closes_at')
                ? (int) \Illuminate\Support\Str::before(\Illuminate\Support\Str::after(old('closes_at'), 'T'), ':')
                : optional($quiz->closes_at)?->format('G');
            $closesMin = old('closes_at')
                ? \Illuminate\Support\Str::after(\Illuminate\Support\Str::after(old('closes_at'), 'T'), ':')
                : optional($quiz->closes_at)?->format('i');
            $closesAmpm = $closesHour24 !== null ? ($closesHour24 >= 12 ? 'PM' : 'AM') : null;
            $closesHour12 = $closesHour24 !== null ? (($closesHour24 % 12) ?: 12) : null;
        @endphp
        <div class="d-flex align-items-center">
            <select id="closes_at_hour" class="form-control form-control-sm @error('closes_at') is-invalid @enderror">
                <option value="">{{ __('Hora') }}</option>
                @for ($h = 1; $h <= 12; $h++)
                    <option value="{{ $h }}" @selected($closesHour12 !== null && (int)$closesHour12 === $h)>{{ $h }}</option>
                @endfor
            </select>
            <span class="mx-1 font-weight-bold">:</span>
            <select id="closes_at_min" class="form-control form-control-sm @error('closes_at') is-invalid @enderror">
                <option value="">{{ __('Min') }}</option>
                @foreach (['00','05','10','15','20','25','30','35','40','45','50','55'] as $m)
                    <option value="{{ $m }}" @selected($closesMin !== null && $closesMin === $m)>{{ $m }}</option>
                @endforeach
            </select>
            <select id="closes_at_ampm" class="form-control form-control-sm ml-1 @error('closes_at') is-invalid @enderror">
                <option value="AM" @selected($closesAmpm === 'AM')>AM</option>
                <option value="PM" @selected($closesAmpm === 'PM')>PM</option>
            </select>
        </div>
        <input type="hidden" name="closes_at" id="closes_at"
               value="{{ old('closes_at', optional($quiz->closes_at)?->format('Y-m-d\TH:i')) }}">
        <small class="form-text text-muted">{{ __('Fecha y hora en que se cierra la encuesta') }}</small>
        @error('closes_at')
            <div class="invalid-feedback d-block">{{ $message }}</div>
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
        // Sincronizar color
        const colorInput = document.getElementById('theme_color');
        const colorText = document.getElementById('theme_color_text');
        if (colorInput && colorText) {
            colorInput.addEventListener('input', function() {
                colorText.value = this.value;
            });
        }

        // Sincronizar selects de hora/min/ampm con hidden input en formato 24h
        function syncDateTime(prefix) {
            const dateEl  = document.getElementById(prefix + '_date');
            const hourEl  = document.getElementById(prefix + '_hour');
            const minEl   = document.getElementById(prefix + '_min');
            const ampmEl  = document.getElementById(prefix + '_ampm');
            const hiddenEl = document.getElementById(prefix);
            if (!dateEl || !hourEl || !minEl || !ampmEl || !hiddenEl) return;

            function update() {
                const d = dateEl.value;
                let h = parseInt(hourEl.value);
                const m = minEl.value || '00';
                const ampm = ampmEl.value;

                if (!d || isNaN(h)) {
                    hiddenEl.value = '';
                    return;
                }

                // Convertir 12h a 24h
                if (ampm === 'AM' && h === 12) h = 0;
                else if (ampm === 'PM' && h !== 12) h += 12;

                const hh = String(h).padStart(2, '0');
                hiddenEl.value = d + 'T' + hh + ':' + m;
            }

            dateEl.addEventListener('change', update);
            hourEl.addEventListener('change', update);
            minEl.addEventListener('change', update);
            ampmEl.addEventListener('change', update);
        }

        syncDateTime('opens_at');
        syncDateTime('closes_at');
    });
</script>
@endpush

