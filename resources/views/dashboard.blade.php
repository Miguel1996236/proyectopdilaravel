@php use App\Models\User; @endphp

<x-app-layout>
    <x-slot name="header">
        @if ($role === User::ROLE_ADMIN)
            {{ __('Panel de administración') }}
        @elseif ($role === User::ROLE_TEACHER)
            {{ __('Panel de docente') }}
        @else
            {{ __('Panel de estudiante') }}
        @endif
    </x-slot>

    @if ($role === User::ROLE_ADMIN)
        @include('dashboard.partials.admin', [
            'stats' => $stats,
            'roleCounts' => $roleCounts,
            'recentUsers' => $recentUsers,
            'chart' => $chart,
        ])
    @elseif ($role === User::ROLE_TEACHER)
        @include('dashboard.partials.teacher', [
            'stats' => $stats,
            'recentSurveys' => $recentSurveys,
            'recentResponses' => $recentResponses,
            'pendingAnalysis' => $pendingAnalysis,
            'chart' => $chart,
        ])
    @else
        @include('dashboard.partials.student', [
            'stats' => $stats,
            'recentAttempts' => $recentAttempts,
            'chart' => $chart,
        ])
    @endif
</x-app-layout>

@push('scripts')
<script>
    (function () {
        const chartData = @json($chart ?? ['labels' => [], 'values' => []]);
        const areaCtx = document.getElementById('usageAreaChart');
        if (areaCtx && chartData.labels.length) {
            new Chart(areaCtx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: '{{ __('Respuestas completadas') }}',
                        lineTension: 0.3,
                        backgroundColor: 'rgba(78, 115, 223, 0.05)',
                        borderColor: 'rgba(78, 115, 223, 1)',
                        pointRadius: 3,
                        pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                        pointBorderColor: 'rgba(78, 115, 223, 1)',
                        pointHoverRadius: 3,
                        pointHoverBackgroundColor: 'rgba(78, 115, 223, 1)',
                        pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
                        pointHitRadius: 10,
                        pointBorderWidth: 2,
                        data: chartData.values,
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    layout: {
                        padding: { left: 10, right: 25, top: 25, bottom: 0 },
                    },
                    scales: {
                        xAxes: [{
                            gridLines: { display: false, drawBorder: false },
                            ticks: { maxTicksLimit: 7 },
                        }],
                        yAxes: [{
                            ticks: { beginAtZero: true, padding: 10, precision: 0 },
                            gridLines: {
                                color: 'rgb(234, 236, 244)',
                                zeroLineColor: 'rgb(234, 236, 244)',
                                drawBorder: false,
                                borderDash: [2],
                                zeroLineBorderDash: [2],
                            },
                        }],
                    },
                    legend: { display: false },
                },
            });
        }

        const rolePieData = @json($roleCounts ?? null);
        const pieCtx = document.getElementById('rolePieChart');
        if (pieCtx && rolePieData) {
            new Chart(pieCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Admins', 'Docentes', 'Estudiantes'],
                    datasets: [{
                        data: [
                            rolePieData['administrador'] ?? 0,
                            rolePieData['docente'] ?? 0,
                            rolePieData['estudiante'] ?? 0,
                        ],
                        backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc'],
                        hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf'],
                        hoverBorderColor: 'rgba(234, 236, 244, 1)',
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    tooltips: {
                        backgroundColor: 'rgb(255,255,255)',
                        bodyFontColor: '#858796',
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        xPadding: 15,
                        yPadding: 15,
                        displayColors: false,
                        caretPadding: 10,
                    },
                    legend: { display: false },
                    cutoutPercentage: 70,
                },
            });
        }
    })();
</script>
@if ($role === \App\Models\User::ROLE_ADMIN)
<script>
    (function () {
        const participationCtx = document.getElementById('adminParticipationSources');
        if (participationCtx) {
            new Chart(participationCtx, {
                type: 'doughnut',
                data: {
                    labels: ['{{ __('Invitaciones directas') }}', '{{ __('Docentes compartieron') }}', '{{ __('Accesos externos') }}'],
                    datasets: [{
                        data: [55, 30, 15],
                        backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc'],
                        hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf'],
                        hoverBorderColor: 'rgba(234, 236, 244, 1)',
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    cutoutPercentage: 70,
                    legend: { display: false },
                    tooltips: {
                        backgroundColor: 'rgb(255,255,255)',
                        bodyFontColor: '#858796',
                        borderColor: '#dddfeb',
                        borderWidth: 1,
                        xPadding: 15,
                        yPadding: 15,
                        displayColors: false,
                        caretPadding: 10,
                    },
                },
            });
        }
    })();
</script>
@endif
@endpush
