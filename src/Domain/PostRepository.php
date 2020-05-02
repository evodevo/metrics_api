<?php

declare(strict_types=1);

namespace MetricsAPI\Domain;

/**
 * Interface PostRepository
 * @package MetricsAPI\Domain
 */
interface PostRepository
{
    /**
     * @param int $page
     * @return array
     */
    public function getPosts(int $page): array;
}