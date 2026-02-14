<?php

namespace App\Charts;

use ArielMejiaDev\LarapexCharts\LineChart;

class DescriptiveLineChart extends LineChart
{
    protected ?string $xAxisTitle = null;

    protected ?string $yAxisTitle = null;

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

    public function xAxisTitle(): ?string
    {
        return $this->xAxisTitle;
    }

    public function yAxisTitle(): ?string
    {
        return $this->yAxisTitle;
    }
}
