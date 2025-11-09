@php
    $type = old('type', $question->type ?? 'multiple_choice');
    $options = old('options', isset($question) && $question->relationLoaded('options')
        ? $question->options->map(function ($option) {
            return [
                'label' => $option->label,
                'value' => $option->value,
                'is_correct' => (bool) $option->is_correct,
            ];
        })->toArray()
        : []);

    if (empty($options) && in_array($type, ['multiple_choice', 'multi_select'], true)) {
        $options = [
            ['label' => '', 'value' => '', 'is_correct' => false],
            ['label' => '', 'value' => '', 'is_correct' => false],
        ];
    }
@endphp

<div class="form-group">
    <label for="question-title" class="font-weight-bold">{{ __('Texto de la pregunta') }}</label>
    <input type="text" name="title" id="question-title" value="{{ old('title', $question->title) }}"
           class="form-control @error('title') is-invalid @enderror" required>
    @error('title')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="question-description" class="font-weight-bold">{{ __('Descripción / contexto (opcional)') }}</label>
    <textarea name="description" id="question-description" rows="3"
              class="form-control @error('description') is-invalid @enderror"
              placeholder="{{ __('Información adicional mostrada antes de la pregunta') }}">{{ old('description', $question->description) }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-row">
    <div class="form-group col-md-4">
        <label for="question-type" class="font-weight-bold">{{ __('Tipo de pregunta') }}</label>
        <select name="type" id="question-type" class="form-control @error('type') is-invalid @enderror">
            <option value="multiple_choice" @selected($type === 'multiple_choice')>{{ __('Opción múltiple (una correcta)') }}</option>
            <option value="multi_select" @selected($type === 'multi_select')>{{ __('Selección múltiple (varias correctas)') }}</option>
            <option value="scale" @selected($type === 'scale')>{{ __('Escala (Likert)') }}</option>
            <option value="open_text" @selected($type === 'open_text')>{{ __('Respuesta abierta') }}</option>
            <option value="numeric" @selected($type === 'numeric')>{{ __('Respuesta numérica') }}</option>
        </select>
        @error('type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group col-md-2">
        <label for="question-weight" class="font-weight-bold">{{ __('Peso') }}</label>
        <input type="number" name="weight" id="question-weight" min="1" max="100"
               value="{{ old('weight', $question->weight ?? 1) }}"
               class="form-control @error('weight') is-invalid @enderror">
        @error('weight')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div id="question-options-wrapper" class="{{ in_array($type, ['multiple_choice', 'multi_select'], true) ? '' : 'd-none' }}">
    <div class="d-flex justify-content-between align-items-center">
        <label class="font-weight-bold mb-0">{{ __('Opciones de respuesta') }}</label>
        <button class="btn btn-sm btn-outline-secondary" id="add-option-btn">
            <i class="fas fa-plus mr-1"></i>{{ __('Agregar opción') }}
        </button>
    </div>
    <p class="text-muted small mt-1">
        {{ __('Marca las opciones correctas. Para preguntas de opción múltiple solo se permitirá una correcta.') }}
    </p>

    @error('options')
        <div class="alert alert-danger small py-2">{{ $message }}</div>
    @enderror

    <div id="options-container"></div>
</div>

<div id="question-scale-wrapper" class="{{ $type === 'scale' ? '' : 'd-none' }}">
    <div class="form-row">
        <div class="form-group col-md-4">
            <label for="scale-min" class="font-weight-bold">{{ __('Valor mínimo') }}</label>
            <input type="number" id="scale-min" name="settings[scale_min]" class="form-control @error('settings.scale_min') is-invalid @enderror"
                   value="{{ old('settings.scale_min', data_get($question->settings, 'scale_min', 1)) }}">
            @error('settings.scale_min')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-md-4">
            <label for="scale-max" class="font-weight-bold">{{ __('Valor máximo') }}</label>
            <input type="number" id="scale-max" name="settings[scale_max]" class="form-control @error('settings.scale_max') is-invalid @enderror"
                   value="{{ old('settings.scale_max', data_get($question->settings, 'scale_max', 5)) }}">
            @error('settings.scale_max')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group col-md-4">
            <label for="scale-step" class="font-weight-bold">{{ __('Incremento') }}</label>
            <input type="number" id="scale-step" name="settings[scale_step]" min="1"
                   class="form-control @error('settings.scale_step') is-invalid @enderror"
                   value="{{ old('settings.scale_step', data_get($question->settings, 'scale_step', 1)) }}">
            @error('settings.scale_step')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="question-additional-instructions" class="font-weight-bold">{{ __('Instrucciones adicionales (opcional)') }}</label>
    <textarea name="settings[additional_instructions]" id="question-additional-instructions" rows="2"
              class="form-control">{{ old('settings.additional_instructions', data_get($question->settings, 'additional_instructions')) }}</textarea>
    <small class="text-muted">{{ __('Este texto aparecerá debajo de la pregunta como orientación adicional.') }}</small>
</div>

<template id="option-template">
    <div class="card border option-row mb-2">
        <div class="card-body py-3">
            <div class="form-row align-items-center">
                <div class="form-group col-md-6 mb-2 mb-md-0">
                    <label class="small text-muted">{{ __('Texto visible') }}</label>
                    <input type="text" class="form-control form-control-sm option-label" required>
                </div>
                <div class="form-group col-md-4 mb-2 mb-md-0">
                    <label class="small text-muted">{{ __('Valor (opcional)') }}</label>
                    <input type="text" class="form-control form-control-sm option-value">
                </div>
                <div class="form-group col-md-2 text-right">
                    <div class="custom-control custom-checkbox mt-4">
                        <input type="checkbox" class="custom-control-input option-correct">
                        <label class="custom-control-label">{{ __('Correcta') }}</label>
                    </div>
                </div>
            </div>
            <div class="text-right">
                <button class="btn btn-link btn-sm text-danger remove-option-btn" type="button">
                    <i class="fas fa-times"></i> {{ __('Eliminar opción') }}
                </button>
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const typeSelect = document.getElementById('question-type');
        const optionsWrapper = document.getElementById('question-options-wrapper');
        const scaleWrapper = document.getElementById('question-scale-wrapper');
        const optionsContainer = document.getElementById('options-container');
        const addOptionBtn = document.getElementById('add-option-btn');
        const optionTemplate = document.getElementById('option-template');

        let optionIndex = 0;

        function clearOptions() {
            if (!optionsContainer) {
                return;
            }
            optionsContainer.innerHTML = '';
            optionIndex = 0;
        }

        function ensureDefaultOptions() {
            if (!optionsContainer) {
                return;
            }
            if (optionsContainer.children.length === 0) {
                addOption();
                addOption();
            }
        }

        function toggleSections() {
            const type = typeSelect.value;
            const requiresOptions = ['multiple_choice', 'multi_select'].includes(type);

            if (optionsWrapper) {
                optionsWrapper.classList.toggle('d-none', !requiresOptions);
            }

            if (scaleWrapper) {
                scaleWrapper.classList.toggle('d-none', type !== 'scale');
            }

            if (addOptionBtn) {
                addOptionBtn.classList.toggle('d-none', !requiresOptions);
            }

            if (!requiresOptions) {
                clearOptions();
            } else {
                ensureDefaultOptions();
            }
        }

        function addOption(defaults = {label: '', value: '', is_correct: false}) {
            if (!optionsContainer || !optionTemplate) {
                return;
            }

            const node = optionTemplate.content.cloneNode(true);
            const row = node.querySelector('.option-row');
            const labelInput = node.querySelector('.option-label');
            const valueInput = node.querySelector('.option-value');
            const correctInput = node.querySelector('.option-correct');
            const checkboxId = `option-correct-${Date.now()}-${optionIndex}`;

            row.dataset.index = optionIndex;

            labelInput.name = `options[${optionIndex}][label]`;
            labelInput.value = defaults.label || '';

            valueInput.name = `options[${optionIndex}][value]`;
            valueInput.value = defaults.value || '';

            correctInput.name = `options[${optionIndex}][is_correct]`;
            correctInput.id = checkboxId;
            correctInput.checked = Boolean(defaults.is_correct);
            correctInput.value = '1';

            const correctLabel = row.querySelector('.custom-control-label');
            if (correctLabel) {
                correctLabel.setAttribute('for', checkboxId);
            }

            row.querySelector('.remove-option-btn').addEventListener('click', function () {
                row.remove();
            });

            optionsContainer.appendChild(node);
            optionIndex++;
        }

        if (addOptionBtn) {
            addOptionBtn.addEventListener('click', function (event) {
                event.preventDefault();
                addOption();
            });
        }

        toggleSections();

        if (typeSelect) {
            typeSelect.addEventListener('change', function () {
                toggleSections();
            });
        }

        const existingOptions = @json($options);
        if (Array.isArray(existingOptions) && existingOptions.length) {
            existingOptions.forEach(function (item) {
                addOption(item);
            });
        } else {
            ensureDefaultOptions();
        }
    });
</script>
@endpush

