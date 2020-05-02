<?php

declare(strict_types=1);

namespace MetricsAPI\Domain;

/**
 * Class RunningAvg
 * @package MetricsAPI\Domain
 */
class RunningAvg implements CalculatedValue
{
    /**
     * @var float|int
     */
    private $sum;

    /**
     * @var int
     */
    private $total;

    /**
     * RunningAvg constructor.
     * @param $sum
     * @param $total
     */
    public function __construct(float $sum, int $total)
    {
        $this->sum = $sum;
        $this->total = $total;
    }

    /**
     * @return float
     */
    public function getSum(): float
    {
        return $this->sum;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @param $sum
     * @param $total
     * @return $this
     */
    public function update($sum, $total): self
    {
        $this->sum += $sum;
        $this->total += $total;

        return $this;
    }

    /**
     * @return float|int
     */
    public function calculate(): float
    {
        return $this->total > 0 ? round($this->sum / $this->total, 2) : 0;
    }
}