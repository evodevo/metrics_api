<?php

declare(strict_types=1);

namespace MetricsAPI\Infrastructure\SupermetricsApi;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;

/**
 * Class Authenticator
 * @package MetricsAPI\Infrastructure\SupermetricsApi
 */
class Authenticator
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var TokenRequest
     */
    private $tokenRequest;

    /**
     * Authenticator constructor.
     * @param ClientInterface $client
     * @param TokenStorage $tokenStorage
     * @param TokenRequest $tokenRequest
     */
    public function __construct(ClientInterface $client, TokenStorage $tokenStorage, TokenRequest $tokenRequest)
    {
        $this->client = $client;
        $this->tokenStorage = $tokenStorage;
        $this->tokenRequest = $tokenRequest;
    }

    /**
     * @return mixed|string|null
     */
    public function getToken(): ?string
    {
        $token = $this->tokenStorage->loadToken();
        if (!$token) {
            return $this->refreshToken();
        }

        return $token;
    }

    /**
     * @return string
     */
    public function refreshToken(): string
    {
        $token = $this->requestToken();
        if (!$token) {
            throw new \RuntimeException('API returned empty token');
        }

        $this->tokenStorage->storeToken($token);

        return $token;
    }

    /**
     * @return mixed
     */
    private function requestToken(): string
    {
        $response = $this->client->post('assignment/register', [
            RequestOptions::JSON => [
                'client_id' => $this->tokenRequest->getClientId(),
                'name' => $this->tokenRequest->getName(),
                'email' => $this->tokenRequest->getEmail(),
            ]
        ]);
        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('Failed to get api token');
        }

        $responseData = json_decode($response->getBody()->getContents(), true);
        if (!$responseData || !isset($responseData['data']['sl_token'])) {
            throw new \RuntimeException('Invalid response format');
        }

        return $responseData['data']['sl_token'];
    }
}