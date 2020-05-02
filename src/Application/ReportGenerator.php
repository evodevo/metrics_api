<?php

declare(strict_types=1);

namespace MetricsAPI\Application;

use MetricsAPI\Domain\AggregatedCollection;
use MetricsAPI\Domain\RunningAvgPerUser;
use MetricsAPI\Domain\PostAggregator;
use MetricsAPI\Domain\PostRepository;

/**
 * Class ReportService
 * @package MetricsAPI\Application
 */
class ReportGenerator
{
    const MAX_PAGES = 10;

    const GROUP_BY_MONTH = 'Y-M';
    const GROUP_BY_WEEK = 'Y-W';

    /**
     * @var PostRepository
     */
    private $postRepository;

    /**
     * @var PostAggregator
     */
    private $postAggregator;

    /**
     * ReportService constructor.
     * @param PostRepository $postRepository
     * @param PostAggregator $postAggregator
     */
    public function __construct(PostRepository $postRepository, PostAggregator $postAggregator)
    {
        $this->postRepository = $postRepository;
        $this->postAggregator = $postAggregator;
    }

    /**
     * @param int $pages
     * @param array $statsToCalculate
     * @return array
     */
    public function generate(int $pages = self::MAX_PAGES, array $statsToCalculate = []): array
    {
        $currentPage = 1;

        $posts = $this->postRepository->getPosts($currentPage);

        // Calculates statistics for a single posts page.
        $aggregatedStats = $this->calculateBatchStats($posts, $statsToCalculate);

        // Calculates post statistics incrementally, page by page,
        // without loading potentially very large dataset into memory.
        while (!empty($posts) && ++$currentPage <= $pages) {
            $posts = $this->postRepository->getPosts($currentPage);

            $newStatsBatch = $this->calculateBatchStats($posts, $statsToCalculate);

            // Updates calculated statistics with values from the new posts batch.
            $aggregatedStats = $this->updateStats($aggregatedStats, $newStatsBatch);
        }

        return array_map(function (AggregatedCollection $stat) {
            return $stat->getCalculatedValues();
        }, $aggregatedStats);
    }

    /**
     * @param array $posts
     * @param array $statsToCalculate
     * @return array
     */
    private function calculateBatchStats(array $posts, array $statsToCalculate = []): array
    {
        $statGenerators = $this->getStatsGenerators();
        if (empty($statsToCalculate)) {
            $statsToCalculate = array_keys($statGenerators);
        }

        $statsToCalculate = array_combine(array_values($statsToCalculate), $statsToCalculate);

        return array_map(function ($stat) use ($statGenerators, $posts) {
            if (!isset($statGenerators[$stat])) {
                throw new \RuntimeException('Stats calculator not available for ' . $stat);
            }
            return $statGenerators[$stat]($posts);
        }, $statsToCalculate);
    }

    /**
     * @param array $aggregatedStats
     * @param array $newStatsBatch
     * @return array
     */
    private function updateStats(array $aggregatedStats, array $newStatsBatch): array
    {
        $statUpdaters = $this->getStatsUpdaters();

        $statsToUpdate = array_keys($newStatsBatch);
        $statsToUpdate = array_combine($statsToUpdate, $statsToUpdate);

        return array_map(function ($statToUpdate) use ($statUpdaters, $aggregatedStats, $newStatsBatch) {
            if (!isset($statUpdaters[$statToUpdate])) {
                throw new \RuntimeException('Stats updater not available for ' . $statToUpdate);
            }
            return $statUpdaters[$statToUpdate]($aggregatedStats[$statToUpdate], $newStatsBatch[$statToUpdate]);
        }, $statsToUpdate);
    }

    /**
     * @return array
     */
    private function getStatsGenerators(): array
    {
        return [
            'avg_post_char_lengths' => function (array $posts) {
                return $this->postAggregator->getAvgPostCharLengths($posts, self::GROUP_BY_MONTH);
            },
            'avg_posts_per_user' => function (array $posts) {
                return $this->postAggregator->getAvgPostsPerUser($posts, self::GROUP_BY_MONTH);
            },
            'max_post_lengths' => function (array $posts) {
                return $this->postAggregator->getMaxPostLengths($posts, self::GROUP_BY_MONTH);
            },
            'total_posts_by_week' => function (array $posts) {
                return $this->postAggregator->getTotalPosts($posts, self::GROUP_BY_WEEK);
            },
        ];
    }

    /**
     * @return array
     */
    private function getStatsUpdaters(): array
    {
        $avgPostsUpdater = function (AggregatedCollection $stats, AggregatedCollection $newStats) {
            return $newStats->merge(
                $stats,
                function ($currentValue, $valueToUpdate) {
                    if (!$currentValue instanceof RunningAvgPerUser || !$valueToUpdate instanceof RunningAvgPerUser) {
                        throw new \InvalidArgumentException('Trying to update a non-RunningAvgPerUser value');
                    }
                    return $currentValue->update($valueToUpdate);
                }
            );
        };

        return [
            'avg_post_char_lengths' => function (AggregatedCollection $stats, AggregatedCollection $newStats) {
                return $newStats->updateAvg($stats);
            },
            'avg_posts_per_user' => $avgPostsUpdater,
            'max_post_lengths' => function (AggregatedCollection $stats, AggregatedCollection $newStats) {
                return $newStats->updateMax($stats);
            },
            'total_posts_by_week' => function (AggregatedCollection $stats, AggregatedCollection $newStats) {
                return $newStats->updateTotal($stats);
            },
        ];
    }
}