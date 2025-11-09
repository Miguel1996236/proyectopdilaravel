<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'organization' => env('OPENAI_ORGANIZATION'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'default_model' => env('OPENAI_DEFAULT_MODEL', env('OPENAI_MODEL_1', 'gpt-4o-mini')),
        'profiles' => array_filter([
            'profile_1' => [
                'model' => env('OPENAI_MODEL_1'),
                'temperature' => env('OPENAI_TEMP_1'),
                'max_tokens' => env('OPENAI_MAXTOKENS_1'),
            ],
            'profile_2' => [
                'model' => env('OPENAI_MODEL_2'),
                'temperature' => env('OPENAI_TEMP_2'),
                'max_tokens' => env('OPENAI_MAXTOKENS_2'),
            ],
            'profile_3' => [
                'model' => env('OPENAI_MODEL_3'),
                'temperature' => env('OPENAI_TEMP_3'),
                'max_tokens' => env('OPENAI_MAXTOKENS_3'),
            ],
            'profile_4' => [
                'model' => env('OPENAI_MODEL_4'),
                'temperature' => env('OPENAI_TEMP_4'),
                'max_tokens' => env('OPENAI_MAXTOKENS_4'),
            ],
        ], fn ($profile) => filled($profile['model'] ?? null)),
    ],

];
