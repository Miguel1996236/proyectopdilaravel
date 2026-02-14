<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PdfChartService
{
    protected const QUICKCHART_URL = 'https://quickchart.io/chart';

    protected const CHART_WIDTH = 500;

    protected const CHART_HEIGHT = 300;

    protected const CHART_COLORS = [
        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
        '#858796', '#5a5c69', '#2e59d9', '#17a673', '#2c9faf',
    ];

    /**
     * Genera imágenes de gráficos como base64 a partir de los insights cuantitativos.
     *
     * @param  array<int, array<string, mixed>>  $quantitativeInsights
     * @return array<int|string, string>  Mapa question_id → data URI base64
     */
    public function generateChartImages(array $quantitativeInsights): array
    {
        $images = [];

        foreach ($quantitativeInsights as $insight) {
            $questionId = $insight['question_id'] ?? null;

            if (! $questionId) {
                continue;
            }

            $chartConfig = $this->buildChartConfig($insight);

            if (! $chartConfig) {
                continue;
            }

            $base64 = $this->fetchChartAsBase64($chartConfig);

            if ($base64) {
                $images[$questionId] = $base64;
            }
        }

        return $images;
    }

    /**
     * Construye la configuración Chart.js para QuickChart según el tipo de pregunta.
     */
    protected function buildChartConfig(array $insight): ?array
    {
        $type = $insight['type'] ?? null;

        return match ($type) {
            'multiple_choice', 'true_false' => $this->buildDoughnutConfig($insight),
            'multi_select' => $this->buildHorizontalBarConfig($insight),
            'scale', 'numeric' => $this->buildVerticalBarConfig($insight),
            default => null,
        };
    }

    protected function buildDoughnutConfig(array $insight): array
    {
        $labels = collect($insight['options'] ?? [])->pluck('label')->all();
        $data = collect($insight['options'] ?? [])->pluck('count')->all();
        $colors = array_slice(self::CHART_COLORS, 0, count($labels));

        return [
            'type' => 'doughnut',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderWidth' => 2,
                    'borderColor' => '#ffffff',
                ]],
            ],
            'options' => [
                'plugins' => [
                    'legend' => ['position' => 'right', 'labels' => ['fontSize' => 11]],
                    'datalabels' => [
                        'display' => true,
                        'color' => '#fff',
                        'font' => ['weight' => 'bold', 'size' => 12],
                        'formatter' => '__PERCENT_FORMATTER__',
                    ],
                ],
            ],
        ];
    }

    protected function buildHorizontalBarConfig(array $insight): array
    {
        $labels = collect($insight['options'] ?? [])->pluck('label')->all();
        $data = collect($insight['options'] ?? [])->pluck('count')->all();

        return [
            'type' => 'horizontalBar',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => __('Respuestas'),
                    'data' => $data,
                    'backgroundColor' => '#4e73df',
                    'borderRadius' => 4,
                ]],
            ],
            'options' => [
                'plugins' => [
                    'legend' => ['display' => false],
                    'datalabels' => [
                        'display' => true,
                        'anchor' => 'end',
                        'align' => 'end',
                        'color' => '#374151',
                        'font' => ['weight' => 'bold', 'size' => 11],
                    ],
                ],
                'scales' => [
                    'xAxes' => [['ticks' => ['beginAtZero' => true, 'precision' => 0]]],
                ],
            ],
        ];
    }

    protected function buildVerticalBarConfig(array $insight): array
    {
        $distribution = $insight['distribution'] ?? [];
        $labels = collect($distribution)->map(fn ($d) => (string) ($d['value'] ?? ''))->all();
        $data = collect($distribution)->pluck('count')->all();

        return [
            'type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => __('Respuestas'),
                    'data' => $data,
                    'backgroundColor' => '#4e73df',
                    'borderRadius' => 4,
                ]],
            ],
            'options' => [
                'plugins' => [
                    'legend' => ['display' => false],
                    'datalabels' => [
                        'display' => true,
                        'anchor' => 'end',
                        'align' => 'top',
                        'color' => '#374151',
                        'font' => ['weight' => 'bold', 'size' => 11],
                    ],
                ],
                'scales' => [
                    'yAxes' => [['ticks' => ['beginAtZero' => true, 'precision' => 0]]],
                ],
            ],
        ];
    }

    /**
     * Llama a QuickChart.io y devuelve la imagen como data URI base64.
     */
    protected function fetchChartAsBase64(array $chartConfig): ?string
    {
        try {
            $json = json_encode($chartConfig, JSON_UNESCAPED_UNICODE);

            // Reemplazar el placeholder del formatter por una función JS real
            $json = str_replace(
                '"__PERCENT_FORMATTER__"',
                "(value, ctx) => { let sum = ctx.dataset.data.reduce((a, b) => a + b, 0); let pct = sum > 0 ? Math.round(value / sum * 100) : 0; return pct > 5 ? pct + '%' : ''; }",
                $json
            );

            $response = Http::timeout(10)->get(self::QUICKCHART_URL, [
                'c' => $json,
                'w' => self::CHART_WIDTH,
                'h' => self::CHART_HEIGHT,
                'bkg' => '#ffffff',
                'f' => 'png',
            ]);

            if ($response->successful()) {
                $base64 = base64_encode($response->body());

                return 'data:image/png;base64,' . $base64;
            }

            Log::warning('PdfChartService: QuickChart returned HTTP ' . $response->status());
        } catch (\Throwable $e) {
            Log::warning('PdfChartService: Error fetching chart - ' . $e->getMessage());
        }

        return null;
    }
}
