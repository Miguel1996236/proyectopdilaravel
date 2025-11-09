<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuizRequest extends FormRequest
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
            'status' => ['nullable', Rule::in(['draft', 'published', 'closed'])],
            'opens_at' => ['nullable', 'date'],
            'closes_at' => ['nullable', 'date', 'after_or_equal:opens_at'],
            'max_attempts' => ['nullable', 'integer', 'min:1'],
            'require_login' => ['nullable', 'boolean'],
            'settings' => ['nullable', 'array'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título',
            'description' => 'descripción',
            'opens_at' => 'fecha de apertura',
            'closes_at' => 'fecha de cierre',
            'max_attempts' => 'intentos máximos',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'require_login' => $this->boolean('require_login'),
            'max_attempts' => $this->input('max_attempts') ?: 1,
        ]);
    }
}
