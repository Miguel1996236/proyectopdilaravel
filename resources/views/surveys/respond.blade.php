@php
    $formData = compact('quiz', 'invitation', 'questions');
@endphp

@auth
    <x-app-layout>
        <x-slot name="header">
            {{ $quiz->title }}
        </x-slot>

        @include('surveys.partials.respond-form', $formData)
    </x-app-layout>
@else
    <!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $quiz->title }} - {{ config('app.name', 'Sistema de Encuestas') }}</title>

        <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
        <link href="{{ asset('assets/css/sb-admin-2.min.css') }}" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body style="background-color: #f8f9fc;">
        <div class="container-fluid py-4">
            @include('surveys.partials.respond-form', $formData)
        </div>

        <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>
        <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>
        @stack('scripts')
    </body>
    </html>
@endauth


