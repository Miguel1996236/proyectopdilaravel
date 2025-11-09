@php
use App\Models\User;

$roleCounts = $roleCounts ?? [];
$recentUsers = $recentUsers ?? collect();
$recentSurveys = $recentSurveys ?? collect();
$recentResponses = $recentResponses ?? collect();
$pendingAnalysis = $pendingAnalysis ?? collect();
$recentAttempts = $recentAttempts ?? collect();
$charts = $charts ?? [];
@endphp

<x-app-layout>
    <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm font-medium text-gray-400">
                @if ($role === User::ROLE_ADMIN)
                    {{ __('Panel de administración') }}
                @elseif ($role === User::ROLE_TEACHER)
                    {{ __('Panel de docente') }}
                @else
                    {{ __('Panel de estudiante') }}
                @endif
            </p>
            <h1 class="text-2xl font-semibold text-gray-900 sm:text-3xl">{{ __('Resumen general') }}</h1>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('quizzes.create') }}" class="inline-flex items-center gap-2 rounded-full bg-brand-500 px-4 py-2 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                {{ __('Nueva encuesta') }}
            </a>
            <button type="button" class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 4H3m18 16H3m4-6h10"/>
                </svg>
                {{ __('Filtrar') }}
            </button>
        </div>
    </div>

    @if ($role === User::ROLE_ADMIN)
        @include('dashboard.tailadmin.admin', [
            'stats' => $stats,
            'roleCounts' => $roleCounts,
            'recentUsers' => $recentUsers,
            'charts' => $charts,
        ])
    @elseif ($role === User::ROLE_TEACHER)
        @include('dashboard.tailadmin.teacher', [
            'stats' => $stats,
            'recentSurveys' => $recentSurveys,
            'recentResponses' => $recentResponses,
            'pendingAnalysis' => $pendingAnalysis,
            'charts' => $charts,
        ])
    @else
        @include('dashboard.tailadmin.student', [
            'stats' => $stats,
            'recentAttempts' => $recentAttempts,
            'charts' => $charts,
        ])
    @endif

    @push('scripts')
        @foreach ($charts as $chartInstance)
            {!! $chartInstance->script() !!}
        @endforeach
    @endpush
</x-app-layout>
