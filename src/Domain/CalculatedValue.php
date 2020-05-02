<?php


declare(strict_types=1);

namespace MetricsAPI\Domain;

/**
 * Interface CalculatedValue
 * @package MetricsAPI\Domain
 */
interface CalculatedValue
{
    /**
     * @return mixed
     */
    public function calculate();
}