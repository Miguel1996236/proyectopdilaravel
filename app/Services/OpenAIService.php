<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAIService
{
    protected string $apiKey;

    protected ?string $organization;

    protected string $baseUrl;

    protected string $defaultModel;

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $profiles;

    public function __construct()
    {
        $config = config('services.openai', []);

        $this->apiKey = (string) ($config['api_key'] ?? '');
        $this->organization = $config['organization'] ?? null;
        $this->baseUrl = rtrim($config['base_url'] ?? 'https://api.openai.com/v1', '/');
        $this->defaultModel = (string) ($config['default_model'] ?? 'gpt-4o-mini');
        $this->profiles = $config['profiles'] ?? [];

        if (blank($this->apiKey)) {
            throw new RuntimeException('Missing OpenAI API key. Define OPENAI_API_KEY in your environment file.');
        }
    }

    /**
     * Generate a chat completion response for a given prompt.
     *
     * @param  string  $prompt
     * @param  array<string, mixed>  $options
     */
    public function chat(string $prompt, array $options = [], ?string $profile = null): array
    {
        $payload = $this->buildPayload($prompt, $options, $profile);

        $response = $this->client()->post("{$this->baseUrl}/chat/completions", $payload);
        $response->throw();

        return $response->json();
    }

    /**
     * Retrieve a profile configuration or the default if none is found.
     *
     * @return array<string, mixed>
     */
    public function profile(?string $profile = null): array
    {
        if (blank($profile)) {
            return [
                'model' => $this->defaultModel,
            ];
        }

        $config = $this->profiles[$profile] ?? null;

        if (blank($config)) {
            throw new RuntimeException("OpenAI profile [{$profile}] not defined.");
        }

        return $config;
    }

    /**
     * Build the payload for the chat completion request.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function buildPayload(string $prompt, array $options, ?string $profile): array
    {
        $profileConfig = $this->profile($profile);

        $model = $options['model'] ?? $profileConfig['model'] ?? $this->defaultModel;
        if (blank($model)) {
            throw new RuntimeException('No OpenAI model specified.');
        }

        $payload = [
            'model' => $model,
            'messages' => $options['messages'] ?? [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
        ];

        $temperature = Arr::get($options, 'temperature', $profileConfig['temperature'] ?? null);
        if (! is_null($temperature)) {
            $payload['temperature'] = (float) $temperature;
        }

        $maxTokens = Arr::get($options, 'max_tokens', $profileConfig['max_tokens'] ?? null);
        if (! is_null($maxTokens)) {
            $payload['max_tokens'] = (int) $maxTokens;
        }

        $additionalKeys = [
            'top_p',
            'n',
            'stop',
            'frequency_penalty',
            'presence_penalty',
            'response_format',
            'tools',
            'tool_choice',
        ];

        foreach ($additionalKeys as $key) {
            if (array_key_exists($key, $options)) {
                $payload[$key] = $options[$key];
            }
        }

        return $payload;
    }

    protected function client(): PendingRequest
    {
        $request = Http::withToken($this->apiKey)
            ->acceptJson()
            ->timeout((int) env('OPENAI_TIMEOUT', 30));

        if ($this->organization) {
            $request->withHeaders([
                'OpenAI-Organization' => $this->organization,
            ]);
        }

        return $request;
    }
}

