<?php

declare(strict_types=1);

namespace MetricsAPI\Infrastructure\SupermetricsApi;

/**
 * Class TokenRequest
 * @package MetricsAPI\Infrastructure\SupermetricsApi
 */
class TokenRequest
{
    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $email;

    /**
     * TokenRequest constructor.
     * @param string $clientId
     * @param string $name
     * @param string $email
     */
    public function __construct(string $clientId, string $name, string $email)
    {
        $this->clientId = $clientId;
        $this->name = $name;
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }
}