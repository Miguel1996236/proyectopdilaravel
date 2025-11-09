<div class="grid gap-6 lg:grid-cols-3">
    <div class="card">
        <div class="card-header">
            <h3 class="text-base font-semibold text-gray-900">{{ __('Usuarios totales') }}</h3>
        </div>
        <div class="card-body flex items-end justify-between">
            <div>
                <p class="text-3xl font-semibold text-gray-900">{{ number_format($stats['users'] ?? 0) }}</p>
                <p class="text-sm text-gray-500">{{ __('Registrados en la plataforma') }}</p>
            </div>
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 text-brand-500">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </span>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h3 class="text-base font-semibold text-gray-900">{{ __('Encuestas activas') }}</h3>
        </div>
        <div class="card-body flex items-end justify-between">
            <div>
                <p class="text-3xl font-semibold text-gray-900">{{ number_format($stats['surveys'] ?? 0) }}</p>
                <p class="text-sm text-gray-500">{{ __('Encuestas creadas por docentes') }}</p>
            </div>
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-green-50 text-green-500">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h3 class="text-base font-semibold text-gray-900">{{ __('Respuestas totales') }}</h3>
        </div>
        <div class="card-body flex items-end justify-between">
            <div>
                <p class="text-3xl font-semibold text-gray-900">{{ number_format($stats['attempts'] ?? 0) }}</p>
                <p class="text-sm text-gray-500">{{ __('Intentos registrados en todas las encuestas') }}</p>
            </div>
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-orange-50 text-orange-500">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a5 5 0 00-10 0v2M5 9h14l-1 11H6L5 9z"/>
                </svg>
            </span>
        </div>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-2 mt-6">
    <div class="card">
        <div class="card-header">
            <h3 class="text-base font-semibold text-gray-900">{{ __('Últimos usuarios registrados') }}</h3>
        </div>
        <div class="card-body">
            <div class="space-y-4">
                @forelse ($recentUsers as $recentUser)
                    <div class="flex items-center justify-between rounded-xl border border-gray-100 px-4 py-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $recentUser->name }}</p>
                            <p class="text-xs text-gray-500">{{ $recentUser->email }}</p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-500">
                            {{ __($recentUser->role) }}
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">{{ __('No hay registros recientes.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-900">{{ __('Distribución por rol') }}</h3>
            <span class="text-xs text-gray-400">{{ __('Últimos 30 días') }}</span>
        </div>
        <div class="card-body">
            <div id="admin-role-chart" class="h-60"></div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            if (typeof window.ApexCharts === 'undefined') {
                return;
            }

            const roleData = @json([
                'admin' => $roleCounts['administrador'] ?? 0,
                'teacher' => $roleCounts['docente'] ?? 0,
                'student' => $roleCounts['estudiante'] ?? 0,
            ]);

            const el = document.querySelector('#admin-role-chart');
            if (!el) {
                return;
            }

            const chart = new ApexCharts(el, {
                chart: {
                    type: 'donut',
                    height: 250,
                },
                series: Object.values(roleData),
                labels: ['Admins', 'Docentes', 'Estudiantes'],
                colors: ['#465fff', '#12b76a', '#0ba5ec'],
                dataLabels: {
                    enabled: true,
                    style: {
                        fontSize: '12px',
                    },
                },
                legend: {
                    position: 'bottom',
                },
            });

            chart.render();
        })();
    </script>
@endpush

