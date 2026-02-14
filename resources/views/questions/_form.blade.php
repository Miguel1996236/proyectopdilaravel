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

    if (empty($options) && $type === 'true_false') {
        $options = [
            ['label' => __('Verdadero'), 'value' => 'true', 'is_correct' => false],
            ['label' => __('Falso'), 'value' => 'false', 'is_correct' => false],
        ];
    } elseif (empty($options) && in_array($type, ['multiple_choice', 'multi_select'], true)) {
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
            <option value="multiple_choice" @selected($type === 'multiple_choice')>{{ __('Opción múltiple') }}</option>
            <option value="multi_select" @selected($type === 'multi_select')>{{ __('Selección múltiple') }}</option>
            <option value="true_false" @selected($type === 'true_false')>{{ __('Verdadero / Falso') }}</option>
            <option value="scale" @selected($type === 'scale')>{{ __('Escala (Likert)') }}</option>
            <option value="open_text" @selected($type === 'open_text')>{{ __('Respuesta abierta') }}</option>
            <option value="numeric" @selected($type === 'numeric')>{{ __('Respuesta numérica') }}</option>
        </select>
        @error('type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <button type="button" class="btn btn-link btn-sm p-0 mt-1" data-toggle="modal" data-target="#question-type-guide-modal">
            <i class="fas fa-info-circle"></i> {{ __('Ver guía completa') }}
        </button>
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

{{-- Card de ayuda contextual según el tipo seleccionado --}}
<div id="question-type-help-card" class="card border-info mb-3">
    <div class="card-header bg-info text-white py-2">
        <i class="fas fa-lightbulb mr-1"></i> {{ __('Ayuda: tipo de pregunta') }}
    </div>
    <div class="card-body py-3">
        <div id="help-what-sees" class="mb-2"></div>
        <div id="help-example" class="mb-2"></div>
        <div id="help-evaluation" class="mb-0"></div>
    </div>
</div>

<div id="question-options-wrapper" class="{{ in_array($type, ['multiple_choice', 'multi_select', 'true_false'], true) ? '' : 'd-none' }}">
    <div class="d-flex justify-content-between align-items-center">
        <label class="font-weight-bold mb-0">{{ __('Opciones de respuesta') }}</label>
        <button class="btn btn-sm btn-outline-secondary" id="add-option-btn">
            <i class="fas fa-plus mr-1"></i>{{ __('Agregar opción') }}
        </button>
    </div>
    <p class="text-muted small mt-1" id="options-instruction-text">
        {{ __('Marca la(s) opción(es) correcta(s) si la pregunta es evaluativa. Si es de encuesta, deja todas sin marcar.') }}
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

{{-- Modal guía completa de tipos de pregunta --}}
<div class="modal fade" id="question-type-guide-modal" tabindex="-1" role="dialog" aria-labelledby="question-type-guide-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="question-type-guide-modal-label">
                    <i class="fas fa-book mr-1"></i> {{ __('Guía de tipos de pregunta') }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ __('Tipo') }}</th>
                                <th>{{ __('Qué ve el alumno') }}</th>
                                <th>{{ __('Ejemplo') }}</th>
                                <th>{{ __('Evaluación') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>{{ __('Opción múltiple') }}</strong></td>
                                <td>{{ __('Varias opciones, solo 1 respuesta (radio)') }}</td>
                                <td>{{ __('¿Cuál snack prefieres para la merienda?') }}</td>
                                <td>{{ __('Con correcta → evaluativa; sin correcta → encuesta') }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('Selección múltiple') }}</strong></td>
                                <td>{{ __('Varias opciones, varias respuestas (checkboxes)') }}</td>
                                <td>{{ __('¿Qué actividades realizas en tu tiempo libre?') }}</td>
                                <td>{{ __('Con correctas → evaluativa; sin correctas → encuesta') }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('Verdadero / Falso') }}</strong></td>
                                <td>{{ __('2 opciones fijas: Verdadero / Falso') }}</td>
                                <td>{{ __('La fotosíntesis ocurre en las plantas.') }}</td>
                                <td>{{ __('Con correcta → evaluativa; sin correcta → encuesta') }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('Escala (Likert)') }}</strong></td>
                                <td>{{ __('Escala numérica (ej. 1–5 o 1–7)') }}</td>
                                <td>{{ __('Estoy satisfecho con la metodología del curso (1=En desacuerdo, 5=De acuerdo)') }}</td>
                                <td>{{ __('Promedios, distribución, tendencias') }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('Respuesta abierta') }}</strong></td>
                                <td>{{ __('Cuadro de texto libre') }}</td>
                                <td>{{ __('¿Qué mejorarías del curso?') }}</td>
                                <td>{{ __('Análisis de temas, IA, palabras clave') }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('Respuesta numérica') }}</strong></td>
                                <td>{{ __('Campo numérico') }}</td>
                                <td>{{ __('¿Cuántas horas dedicaste a estudiar esta semana?') }}</td>
                                <td>{{ __('Promedio, min/max, distribución') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
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

        const typeHelp = {
            multiple_choice: {
                whatSees: @json(__('Varias opciones, solo 1 respuesta (radio)')),
                example: @json(__('¿Cuál snack prefieres para la merienda?')),
                evaluation: @json(__('Con correcta → evaluativa; sin correcta → encuesta'))
            },
            multi_select: {
                whatSees: @json(__('Varias opciones, varias respuestas (checkboxes)')),
                example: @json(__('¿Qué actividades realizas en tu tiempo libre?')),
                evaluation: @json(__('Con correctas → evaluativa; sin correctas → encuesta'))
            },
            true_false: {
                whatSees: @json(__('2 opciones fijas: Verdadero / Falso')),
                example: @json(__('La fotosíntesis ocurre en las plantas.')),
                evaluation: @json(__('Con correcta → evaluativa; sin correcta → encuesta'))
            },
            scale: {
                whatSees: @json(__('Escala numérica (ej. 1–5 o 1–7)')),
                example: @json(__('Estoy satisfecho con la metodología del curso (1=En desacuerdo, 5=De acuerdo)')),
                evaluation: @json(__('Promedios, distribución, tendencias'))
            },
            open_text: {
                whatSees: @json(__('Cuadro de texto libre')),
                example: @json(__('¿Qué mejorarías del curso?')),
                evaluation: @json(__('Análisis de temas, IA, palabras clave'))
            },
            numeric: {
                whatSees: @json(__('Campo numérico')),
                example: @json(__('¿Cuántas horas dedicaste a estudiar esta semana?')),
                evaluation: @json(__('Promedio, min/max, distribución'))
            }
        };

        let optionIndex = 0;

        function updateHelpCard() {
            const type = typeSelect ? typeSelect.value : 'multiple_choice';
            const help = typeHelp[type] || typeHelp.multiple_choice;
            const whatSeesEl = document.getElementById('help-what-sees');
            const exampleEl = document.getElementById('help-example');
            const evaluationEl = document.getElementById('help-evaluation');
            if (whatSeesEl) {
                whatSeesEl.innerHTML = '<strong>{{ __("Qué ve el alumno") }}:</strong> ' + (help.whatSees || '');
            }
            if (exampleEl) {
                exampleEl.innerHTML = '<strong>{{ __("Ejemplo") }}:</strong> ' + (help.example || '');
            }
            if (evaluationEl) {
                evaluationEl.innerHTML = '<strong>{{ __("Evaluación") }}:</strong> ' + (help.evaluation || '');
            }
        }

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
            const requiresOptions = ['multiple_choice', 'multi_select', 'true_false'].includes(type);
            const isTrueFalse = type === 'true_false';

            if (optionsWrapper) {
                optionsWrapper.classList.toggle('d-none', !requiresOptions);
            }

            if (scaleWrapper) {
                scaleWrapper.classList.toggle('d-none', type !== 'scale');
            }

            if (addOptionBtn) {
                addOptionBtn.classList.toggle('d-none', !requiresOptions || isTrueFalse);
            }

            if (!requiresOptions) {
                clearOptions();
            } else if (isTrueFalse) {
                clearOptions();
                addOption({label: '{{ __("Verdadero") }}', value: 'true', is_correct: false}, true);
                addOption({label: '{{ __("Falso") }}', value: 'false', is_correct: false}, true);
            } else {
                ensureDefaultOptions();
            }
        }

        function addOption(defaults = {label: '', value: '', is_correct: false}, fixed = false) {
            if (!optionsContainer || !optionTemplate) {
                return;
            }

            const node = optionTemplate.content.cloneNode(true);
            const row = node.querySelector('.option-row');
            const labelInput = node.querySelector('.option-label');
            const valueInput = node.querySelector('.option-value');
            const correctInput = node.querySelector('.option-correct');
            const removeBtn = row.querySelector('.remove-option-btn');
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

            if (fixed) {
                labelInput.readOnly = true;
                valueInput.readOnly = true;
                if (removeBtn) removeBtn.style.display = 'none';
            } else {
                removeBtn.addEventListener('click', function () {
                    row.remove();
                });
            }

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
        updateHelpCard();

        if (typeSelect) {
            typeSelect.addEventListener('change', function () {
                toggleSections();
                updateHelpCard();
            });
        }

        const existingOptions = @json($options);
        const currentType = typeSelect ? typeSelect.value : 'multiple_choice';
        if (currentType === 'true_false') {
            if (Array.isArray(existingOptions) && existingOptions.length === 2) {
                clearOptions();
                existingOptions.forEach(function (item) {
                    addOption(item, true);
                });
            } else {
                toggleSections();
            }
        } else if (Array.isArray(existingOptions) && existingOptions.length) {
            existingOptions.forEach(function (item) {
                addOption(item);
            });
        } else {
            ensureDefaultOptions();
        }
    });
</script>
@endpush

