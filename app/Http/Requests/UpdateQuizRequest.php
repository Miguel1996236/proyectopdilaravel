<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() &&
            in_array($this->user()->role, [User::ROLE_ADMIN, User::ROLE_TEACHER], true);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['draft', 'published', 'closed'])],
            'opens_at' => ['nullable', 'date'],
            'closes_at' => ['nullable', 'date', 'after_or_equal:opens_at'],
            'max_attempts' => ['nullable', 'integer', 'min:1'],
            'require_login' => ['nullable', 'boolean'],
            'target_audience' => ['nullable', Rule::in(['all', 'students', 'teachers'])],
            'randomize_questions' => ['nullable', 'boolean'],
            'theme_color' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'settings' => ['nullable', 'array'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título',
            'description' => 'descripción',
            'status' => 'estado',
            'opens_at' => 'fecha de apertura',
            'closes_at' => 'fecha de cierre',
            'max_attempts' => 'intentos máximos',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'require_login' => $this->boolean('require_login'),
            'randomize_questions' => $this->boolean('randomize_questions'),
            'max_attempts' => $this->input('max_attempts') ?: 1,
        ]);
    }
}
