@auth
    <x-app-layout>
        <x-slot name="header">
            {{ __('Encuesta completada') }}
        </x-slot>

        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-check-circle fa-4x text-success mb-4"></i>
                        <h2 class="h4 text-gray-900 mb-3">{{ __('¡Gracias por participar!') }}</h2>
                        <p class="text-muted mb-4">{{ __('Tu respuesta fue registrada exitosamente.') }}</p>
                        <div class="d-flex flex-column flex-sm-row justify-content-center">
                            <a href="{{ route('dashboard') }}" class="btn btn-primary mr-sm-2 mb-2 mb-sm-0">
                                <i class="fas fa-tachometer-alt mr-1"></i>{{ __('Volver al dashboard') }}
                            </a>
                            <a href="{{ route('surveys.access.form') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-barcode mr-1"></i>{{ __('Responder otra encuesta') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-app-layout>
@else
    <!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('Encuesta completada') }} - {{ config('app.name', 'Sistema de Encuestas') }}</title>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet" type="text/css" crossorigin="anonymous">
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
        <link href="{{ asset('assets/css/sb-admin-2.min.css') }}" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body style="background-color: #f8f9fc;">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card shadow mb-4">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-check-circle fa-4x text-success mb-4"></i>
                            <h2 class="h4 text-gray-900 mb-3">{{ __('¡Gracias por participar!') }}</h2>
                            <p class="text-muted mb-4">{{ __('Tu respuesta fue registrada exitosamente.') }}</p>
                            <div class="d-flex flex-column flex-sm-row justify-content-center">
                                <a href="{{ route('login') }}" class="btn btn-primary mr-sm-2 mb-2 mb-sm-0">
                                    <i class="fas fa-sign-in-alt mr-1"></i>{{ __('Iniciar sesión') }}
                                </a>
                                <a href="{{ route('surveys.access.form') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-barcode mr-1"></i>{{ __('Responder otra encuesta') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
@endauth
