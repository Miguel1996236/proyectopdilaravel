<?php

namespace App\Charts;

use ArielMejiaDev\LarapexCharts\BarChart;

class DescriptiveBarChart extends BarChart
{

    protected ?string $xAxisTitle = null;

    protected ?string $yAxisTitle = null;

    protected ?float $yAxisMin = null;

    public function setXAxisTitle(string $title): self
    {
        $this->xAxisTitle = $title;

        return $this;
    }

    public function setYAxisTitle(string $title): self
    {
        $this->yAxisTitle = $title;

        return $this;
    }

    public function setYAxisMin(float $min): self
    {
        $this->yAxisMin = $min;

        return $this;
    }

    public function xAxisTitle(): ?string
    {
        return $this->xAxisTitle;
    }

    public function yAxisTitle(): ?string
    {
        return $this->yAxisTitle;
    }

    public function yAxisMin(): ?float
    {
        return $this->yAxisMin;
    }
}
