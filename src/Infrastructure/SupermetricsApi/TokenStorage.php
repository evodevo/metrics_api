<?php

declare(strict_types=1);

namespace MetricsAPI\Infrastructure\SupermetricsApi;

/**
 * Interface TokenStorage
 * @package MetricsAPI\Infrastructure\SupermetricsApi
 */
interface TokenStorage
{
    /**
     * @param $token
     * @return mixed
     */
    public function storeToken(string $token);

    /**
     * @return string|null
     */
    public function loadToken(): ?string;
}