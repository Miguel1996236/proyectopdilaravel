@php
    $formData = compact('quiz', 'invitation', 'questions');
@endphp

@auth
    <x-app-layout>
        <x-slot name="header">
            {{ $quiz->title }}
        </x-slot>

        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-10">
                @include('surveys.partials.respond-form', $formData)
            </div>
        </div>
    </x-app-layout>
@else
    <!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $quiz->title }} - {{ config('app.name', 'EduQuiz') }}</title>

        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet" type="text/css" crossorigin="anonymous">
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
        <link href="{{ asset('assets/css/sb-admin-2.min.css') }}" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
        <style>
            .survey-guest-header {
                background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            }
            .survey-guest-brand {
                color: #fff;
                text-decoration: none;
                font-size: 1.1rem;
                font-weight: 800;
                letter-spacing: 0.05rem;
            }
            .survey-guest-brand:hover {
                color: rgba(255,255,255,.85);
                text-decoration: none;
            }
            .survey-guest-brand .brand-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 2rem;
                height: 2rem;
                border-radius: .5rem;
                background: rgba(255,255,255,.15);
                margin-right: .5rem;
                transform: rotate(-15deg);
            }
        </style>
    </head>
    <body style="background-color: #f8f9fc;">
        {{-- Barra superior con branding --}}
        <nav class="survey-guest-header py-3 mb-0 shadow-sm">
            <div class="container">
                <div class="d-flex align-items-center justify-content-between">
                    <a href="{{ route('login') }}" class="survey-guest-brand d-flex align-items-center">
                        <span class="brand-icon"><i class="fas fa-clipboard-list"></i></span>
                        EduQuiz
                    </a>
                    <div>
                        <a href="{{ route('login') }}" class="btn btn-sm btn-light font-weight-bold">
                            <i class="fas fa-sign-in-alt mr-1"></i>{{ __('Iniciar sesión') }}
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-xl-8 col-lg-10">
                    @include('surveys.partials.respond-form', $formData)
                </div>
            </div>
        </div>

        {{-- Footer discreto --}}
        <div class="text-center py-3">
            <small class="text-muted">
                {{ config('app.name', 'EduQuiz') }} &copy; {{ date('Y') }} &mdash;
                <a href="{{ route('surveys.access.form') }}" class="text-muted">{{ __('Ingresar otro código') }}</a>
            </small>
        </div>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery.easing@1.4.1/jquery.easing.min.js" crossorigin="anonymous"></script>
        <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>
        @stack('scripts')
    </body>
    </html>
@endauth
