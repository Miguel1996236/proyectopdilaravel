@section('title', 'Dashboard')

<x-app-layout>
    <x-slot name="header">
        {{ __('Panel principal') }}
    </x-slot>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Usuarios</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['users'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Encuestas activas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['surveys'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-poll-h fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Respuestas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['attempts'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Invitaciones activas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['invites'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-key fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $roleDistribution = [
            'admins' => $roleCounts[\App\Models\User::ROLE_ADMIN] ?? 0,
            'teachers' => $roleCounts[\App\Models\User::ROLE_TEACHER] ?? 0,
            'students' => $roleCounts[\App\Models\User::ROLE_STUDENT] ?? 0,
        ];
    @endphp

    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Actividad en encuestas') }}</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="usageAreaChart"></canvas>
                    </div>
                    <hr>
                    <p class="text-muted small mb-0">{{ __('Visualiza la evolución semanal de las respuestas registradas en la plataforma.') }}</p>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Roles de usuarios') }}</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="rolePieChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="mr-2">
                            <i class="fas fa-circle text-primary"></i> {{ __('Administradores') }}
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-success"></i> {{ __('Docentes') }}
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-info"></i> {{ __('Estudiantes') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Estado del sistema') }}</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="mr-3">
                            <i class="fas fa-database fa-2x text-gray-300"></i>
                        </div>
                        <div>
                            <div class="small text-muted">{{ __('Base de datos') }}</div>
                            <div class="font-weight-bold text-success">{{ __('Operativa') }}</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="mr-3">
                            <i class="fas fa-robot fa-2x text-gray-300"></i>
                        </div>
                        <div>
                            <div class="small text-muted">{{ __('Servicios de IA') }}</div>
                            <div class="font-weight-bold text-success">{{ __('OpenAI configurado') }}</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-server fa-2x text-gray-300"></i>
                        </div>
                        <div>
                            <div class="small text-muted">{{ __('Colas y caché') }}</div>
                            <div class="font-weight-bold text-success">{{ __('Preparado') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Notas rápidas') }}</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        {{ __('Atajos para gestionar encuestas y revisar resultados de satisfacción o diagnóstico.') }}
                    </p>
                    <a href="#" class="btn btn-primary btn-icon-split btn-sm mr-2 mb-2">
                        <span class="icon text-white-50">
                            <i class="fas fa-plus"></i>
                        </span>
                        <span class="text">{{ __('Nueva encuesta') }}</span>
                    </a>
                    <a href="#" class="btn btn-success btn-icon-split btn-sm mr-2 mb-2">
                        <span class="icon text-white-50">
                            <i class="fas fa-key"></i>
                        </span>
                        <span class="text">{{ __('Generar invitaciones') }}</span>
                    </a>
                    <a href="#" class="btn btn-info btn-icon-split btn-sm mb-2">
                        <span class="icon text-white-50">
                            <i class="fas fa-file-alt"></i>
                        </span>
                        <span class="text">{{ __('Ver reportes') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
<script>
    (function () {
        const areaCtx = document.getElementById('usageAreaChart');
        if (areaCtx) {
            new Chart(areaCtx, {
                type: 'line',
                data: {
                    labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                    datasets: [{
                        label: 'Intentos completados',
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
                        data: [10, 20, 35, 25, 40, 45, 30],
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            left: 10,
                            right: 25,
                            top: 25,
                            bottom: 0
                        }
                    },
                    scales: {
                        xAxes: [{
                            time: {
                                unit: 'day'
                            },
                            gridLines: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                maxTicksLimit: 7
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                maxTicksLimit: 5,
                                padding: 10,
                                beginAtZero: true
                            },
                            gridLines: {
                                color: 'rgb(234, 236, 244)',
                                zeroLineColor: 'rgb(234, 236, 244)',
                                drawBorder: false,
                                borderDash: [2],
                                zeroLineBorderDash: [2]
                            }
                        }],
                    },
                    legend: {
                        display: false
                    }
                }
            });
        }

        const pieCtx = document.getElementById('rolePieChart');
        if (pieCtx) {
            new Chart(pieCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Admins', 'Docentes', 'Estudiantes'],
                    datasets: [{
                        data: [
                            {{ $roleDistribution['admins'] }},
                            {{ $roleDistribution['teachers'] }},
                            {{ $roleDistribution['students'] }}
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
                    legend: {
                        display: false
                    },
                    cutoutPercentage: 70,
                },
            });
        }
    })();
</script>
@endpush
