<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Sistema de Encuestas'))</title>

    <link rel="stylesheet" href="{{ asset('assets/css/tailadmin.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
@php
    $user = auth()->user();
    $role = $user?->role;
    $navigation = [
        [
            'label' => __('Dashboard'),
            'route' => route('dashboard'),
            'active' => request()->routeIs('dashboard'),
        ],
        [
            'label' => __('Encuestas'),
            'route' => route('quizzes.index'),
            'active' => request()->routeIs('quizzes.*'),
            'visible' => in_array($role, [\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_TEACHER]),
        ],
        [
            'label' => __('Reportes'),
            'route' => route('reports.summary'),
            'active' => request()->routeIs('reports.*'),
            'visible' => in_array($role, [\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_TEACHER]),
        ],
        [
            'label' => __('Usuarios'),
            'route' => route('admin.users.index'),
            'active' => request()->routeIs('admin.users.*'),
            'visible' => $role === \App\Models\User::ROLE_ADMIN,
        ],
        [
            'label' => __('Ingresar código'),
            'route' => route('surveys.access.form'),
            'active' => request()->routeIs('surveys.access.*'),
            'visible' => $role === \App\Models\User::ROLE_STUDENT,
        ],
    ];
@endphp
<body class="bg-gray-50 font-sans antialiased" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex">
        <div class="fixed inset-0 z-40 bg-gray-900/50 lg:hidden" x-show="sidebarOpen" x-transition @click="sidebarOpen = false"></div>

        <aside class="fixed inset-y-0 left-0 z-50 w-72 transform bg-white border-r border-gray-200 shadow-lg lg:static lg:translate-x-0 lg:w-72"
            x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
            :class="{'-translate-x-full lg:translate-x-0': !sidebarOpen, 'translate-x-0': sidebarOpen}">
            <div class="flex items-center justify-between px-6 h-16 border-b border-gray-200">
                <a href="{{ route('dashboard') }}" class="text-lg font-semibold tracking-wide text-gray-900">
                    {{ config('app.name', 'TailAdmin') }}
                </a>
                <button class="lg:hidden text-gray-500 hover:text-gray-700" @click="sidebarOpen = false">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <nav class="flex-1 overflow-y-auto px-4 py-6 custom-scrollbar">
                <div class="space-y-4">
                    @foreach ($navigation as $item)
                        @continue(isset($item['visible']) && ! $item['visible'])
                        <a href="{{ $item['route'] }}"
                           class="flex items-center justify-between gap-3 rounded-xl px-4 py-2.5 text-sm font-medium transition
                                {{ $item['active'] ? 'bg-brand-50 text-brand-600 border border-brand-100' : 'text-gray-600 hover:bg-gray-100' }}">
                            <span>{{ $item['label'] }}</span>
                            @if ($item['active'])
                                <span class="inline-flex h-2 w-2 rounded-full bg-brand-500"></span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </nav>
            <div class="px-6 pb-6">
                <div class="rounded-2xl bg-brand-50 p-4 text-sm text-brand-700">
                    <p class="font-semibold mb-1">{{ __('¿Necesitas ayuda?') }}</p>
                    <p class="text-brand-600">{{ __('Revisa la documentación o ponte en contacto con el administrador.') }}</p>
                </div>
            </div>
        </aside>

        <div class="flex-1 min-h-screen flex flex-col lg:ml-72 transition-all duration-200">
            <header class="sticky top-0 z-30 bg-white border-b border-gray-200">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <button class="lg:hidden inline-flex items-center justify-center rounded-lg border border-gray-200 p-2 text-gray-600"
                                @click="sidebarOpen = !sidebarOpen">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5M3.75 12h16.5m-16.5 6.75h16.5"/>
                            </svg>
                        </button>
                        <div>
                            <p class="text-xs text-gray-500">{{ __('Bienvenido de nuevo') }}</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $user?->name }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="hidden sm:inline-flex items-center gap-2 text-sm text-gray-500 border border-gray-200 rounded-full px-3 py-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 1 1 0-16 8 8 0 0 1 0 16z"/>
                            </svg>
                            {{ __('Buscar…') }}
                        </span>
                        <div class="h-10 w-10 rounded-full bg-brand-500/10 flex items-center justify-center text-brand-600 font-semibold uppercase">
                            {{ \Illuminate\Support\Str::of($user?->name ?? 'U')->substr(0, 2) }}
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1">
                <div class="mx-auto w-full max-w-screen-2xl px-4 py-6 sm:px-6 lg:px-8">
                    @if (session('status'))
                        <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                            <p class="font-semibold mb-2">{{ __('Se encontraron algunos problemas:') }}</p>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{ $slot }}
                </div>
            </main>

            <footer class="border-t border-gray-200 bg-white">
                <div class="mx-auto flex max-w-screen-2xl items-center justify-between px-4 py-4 sm:px-6">
                    <p class="text-xs text-gray-500">
                        &copy; {{ now()->year }} {{ config('app.name', 'Sistema de Encuestas') }}. {{ __('Todos los derechos reservados.') }}
                    </p>
                    <a href="https://tailadmin.com" target="_blank" rel="noopener"
                       class="text-xs font-medium text-brand-500 hover:text-brand-600 transition">
                        TailAdmin Template
                    </a>
                </div>
            </footer>
        </div>
    </div>

    <script src="{{ \ArielMejiaDev\LarapexCharts\LarapexChart::cdn() }}"></script>
    @stack('scripts')
</body>
</html>
