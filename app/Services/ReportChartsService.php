<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use ArielMejiaDev\LarapexCharts\LarapexChart;

class ReportChartsService
{
    /**
     * Construir serie de datos para actividad semanal.
     *
     * @param  Builder<\App\Models\QuizAttempt>  $query
     */
    public function buildAttemptSeries(Builder $query): array
    {
        $period = CarbonPeriod::create(
            Carbon::now()->subDays(6)->startOfDay(),
            Carbon::now()->endOfDay()
        );

        $labels = [];
        $values = [];

        foreach ($period as $date) {
            $labels[] = $date->translatedFormat('D d');
            $values[] = (clone $query)
                ->whereBetween('created_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
                ->where('status', 'completed')
                ->count();
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * Construir serie de datos para participación mensual.
     *
     * @param  Builder<\App\Models\QuizAttempt>  $query
     */
    public function buildMonthlySeries(Builder $query): array
    {
        $period = CarbonPeriod::create(
            Carbon::now()->subMonths(5)->startOfMonth(),
            Carbon::now()->endOfMonth(),
            '1 month'
        );

        $labels = [];
        $values = [];

        foreach ($period as $date) {
            $labels[] = $date->translatedFormat('M Y');
            $values[] = (clone $query)
                ->whereBetween('created_at', [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()])
                ->where('status', 'completed')
                ->count();
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * Construir serie de datos para tendencias mensuales de encuestas.
     *
     * @param  Builder<\App\Models\Quiz>  $query
     */
    public function buildMonthlyTrends(Builder $query): array
    {
        $period = CarbonPeriod::create(
            Carbon::now()->subMonths(5)->startOfMonth(),
            Carbon::now()->endOfMonth(),
            '1 month'
        );

        $labels = [];
        $values = [];

        foreach ($period as $date) {
            $labels[] = $date->translatedFormat('M Y');
            $values[] = (clone $query)
                ->whereBetween('created_at', [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()])
                ->count();
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * @param  array{labels: array<string>, values: array<int>}  $series
     */
    public function buildLineChart(array $series, string $title, string $datasetLabel): ?LarapexChart
    {
        if (empty($series['labels']) || array_sum($series['values']) === 0) {
            return null;
        }

        $chart = (new LarapexChart())->lineChart();
        $chart
            ->setHeight(300)
            ->setColors(['#4e73df'])
            ->setMarkers(['#2e59d9'], 7, 10)
            ->setXAxis($series['labels'])
            ->addData($datasetLabel, $series['values']);

        return $chart;
    }

    /**
     * @param  array<int>  $values
     */
    public function buildDonutChart(array $labels, array $values, string $title): ?LarapexChart
    {
        if (array_sum($values) === 0) {
            return null;
        }

        $chart = (new LarapexChart())->donutChart();
        $chart
            ->setHeight(300)
            ->setLabels($labels)
            ->addData($values)
            ->setColors(['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796']);

        return $chart;
    }

}
