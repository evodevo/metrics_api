<?php

declare(strict_types=1);

namespace MetricsAPI\Infrastructure\SupermetricsApi\TokenStorage;

use MetricsAPI\Infrastructure\SupermetricsApi\TokenStorage;

/**
 * Class FileStorage
 * @package MetricsAPI\Infrastructure\SupermetricsApi\TokenStorage
 */
class FileStorage implements TokenStorage
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * FileStorage constructor.
     * @param $filePath
     */
    public function __construct(string $filePath)
    {
        $this->filePath = ROOT_PATH . $filePath;
    }

    /**
     * @param string $token
     */
    public function storeToken(string $token)
    {
        file_put_contents($this->filePath, $token);
    }

    /**
     * @return false|string|null
     */
    public function loadToken(): ?string
    {
        if (!is_file($this->filePath)) {
            return null;
        }

        return file_get_contents($this->filePath);
    }
}