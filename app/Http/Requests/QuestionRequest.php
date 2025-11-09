<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()
            && in_array($this->user()->role, [User::ROLE_ADMIN, User::ROLE_TEACHER], true);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::in(['multiple_choice', 'multi_select', 'scale', 'open_text', 'numeric'])],
            'weight' => ['nullable', 'integer', 'min:1', 'max:100'],
            'options' => $this->optionsRules(),
            'options.*.label' => $this->optionLabelRules(),
            'options.*.value' => ['nullable', 'string', 'max:255'],
            'options.*.is_correct' => ['nullable', 'boolean'],
            'settings.additional_instructions' => ['nullable', 'string'],
            'settings.scale_min' => ['required_if:type,scale', 'nullable', 'integer', 'lt:settings.scale_max'],
            'settings.scale_max' => ['required_if:type,scale', 'nullable', 'integer', 'gt:settings.scale_min'],
            'settings.scale_step' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'options.required' => __('Debes agregar opciones para este tipo de pregunta.'),
            'options.min' => __('Agrega al menos :min opciones.'),
            'options.*.label.required' => __('Cada opción necesita un texto.'),
            'settings.scale_min.required_if' => __('Define un valor mínimo para la escala.'),
            'settings.scale_max.required_if' => __('Define un valor máximo para la escala.'),
            'settings.scale_max.gt' => __('El valor máximo debe ser mayor que el mínimo.'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $options = collect($this->input('options', []))
            ->map(function ($option) {
                if (!is_array($option)) {
                    return null;
                }
                return [
                    'label' => $option['label'] ?? null,
                    'value' => $option['value'] ?? null,
                    'is_correct' => filter_var($option['is_correct'] ?? false, FILTER_VALIDATE_BOOLEAN),
                ];
            })
            ->filter(fn ($option) => $option !== null)
            ->values()
            ->toArray();

        $this->merge([
            'options' => $options,
            'weight' => $this->input('weight') ?: 1,
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $type = $this->input('type');
            $options = $this->input('options', []);

            if (in_array($type, ['multiple_choice', 'multi_select'], true)) {
                $correctCount = collect($options)->where('is_correct', true)->count();

                if ($correctCount === 0) {
                    $validator->errors()->add('options', __('Selecciona al menos una opción correcta.'));
                }

                if ($type === 'multiple_choice' && $correctCount > 1) {
                    $validator->errors()->add('options', __('En las preguntas de opción múltiple solo debe haber una respuesta correcta.'));
                }
            }
        });
    }

    protected function optionsRules(): array
    {
        if (in_array($this->input('type'), ['multiple_choice', 'multi_select'], true)) {
            return ['required', 'array', 'min:2'];
        }

        return ['nullable', 'array'];
    }

    protected function optionLabelRules(): array
    {
        if (in_array($this->input('type'), ['multiple_choice', 'multi_select'], true)) {
            return ['required', 'string', 'max:255'];
        }

        return ['nullable', 'string', 'max:255'];
    }
}
