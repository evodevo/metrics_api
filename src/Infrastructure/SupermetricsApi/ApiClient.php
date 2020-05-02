<?php

declare(strict_types=1);

namespace MetricsAPI\Infrastructure\SupermetricsApi;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ApiClient
 * @package MetricsAPI\Infrastructure\SupermetricsApi
 */
class ApiClient
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var Authenticator
     */
    private $authenticator;

    /**
     * ApiClient constructor.
     * @param ClientInterface $client
     * @param Authenticator $authenticator
     */
    public function __construct(ClientInterface $client, Authenticator $authenticator)
    {
        $this->client = $client;
        $this->authenticator = $authenticator;
    }

    /**
     * @param int $page
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getPosts(int $page): array
    {
        $response = $this->request("GET", "assignment/posts", ['page'  => $page]);
        $response = json_decode($response->getBody()->getContents(), true);
        if (!$response || !isset($response['data']['posts'])) {
            throw new \RuntimeException('Invalid response format');
        }

        if ((int)$response['data']['page'] !== $page) {
            return [];
        }

        return $response['data']['posts'];
    }

    /**
     * @param $method
     * @param $url
     * @param array $params
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function request($method, $url, $params = []) : ResponseInterface
    {
        $options['query'] = array_merge($params, ['sl_token' => $this->authenticator->getToken()]);

        try {
            $response = $this->client->request($method, $url, $options);
        } catch (BadResponseException $exception) {
            $response = json_decode($exception->getResponse()->getBody()->getContents(), true);
            if ($response && $this->isInvalidToken($response)) {
                $options['query']['sl_token'] = $this->authenticator->refreshToken();
                $response = $this->client->request($method, $url, $options);
            } else {
                throw $exception;
            }
        }

        return $response;
    }

    /**
     * @param array $response
     * @return bool
     */
    private function isInvalidToken(array $response): bool
    {
        return isset($response['error']['message']) && $response['error']['message'] === 'Invalid SL Token';
    }
}