<?php

declare(strict_types=1);

namespace MetricsAPI\Infrastructure\Repository;

use MetricsAPI\Domain\Post;
use MetricsAPI\Domain\PostRepository;
use MetricsAPI\Infrastructure\SupermetricsApi\ApiClient;

/**
 * Class SupermetricsPostRepository
 * @package MetricsAPI\Infrastructure\Repository
 */
class SupermetricsPostRepository implements PostRepository
{
    /**
     * @var ApiClient
     */
    private $apiClient;

    /**
     * SupermetricsPostRepository constructor.
     * @param ApiClient $apiClient
     */
    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * @param int $page
     * @return array
     */
    public function getPosts(int $page = 1): array
    {
        $posts = $this->apiClient->getPosts($page);

        return array_map(function (array $postData) {
            return $this->createPost($postData);
        }, $posts);
    }

    /**
     * @param array $postData
     * @return Post
     * @throws \Exception
     */
    private function createPost(array $postData): Post
    {
        if (!$this->isValidPostData($postData)) {
            throw new \RuntimeException(
                'Data is invalid for post with id ' . isset($postData['id']) ? $postData['id'] : 'null'
            );
        }

        return new Post(
            $postData['id'],
            $postData['from_id'],
            $postData['from_name'],
            $postData['message'],
            \DateTime::createFromFormat(DATE_RFC3339, $postData['created_time'])
        );
    }

    /**
     * @param array $postData
     * @return bool
     */
    private function isValidPostData(array $postData): bool
    {
        if (!isset($postData['id'])
            || !isset($postData['from_id'])
            || !isset($postData['from_name'])
            || !isset($postData['message'])
            || !isset($postData['created_time'])) {
            return false;
        }

        return true;
    }
}