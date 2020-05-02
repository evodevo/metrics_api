<?php

declare(strict_types=1);

namespace MetricsAPI\Domain;

/**
 * Class PostAggregator
 * @package MetricsAPI\Domain
 */
class PostAggregator
{
    /**
     * @param array $posts
     * @param string $interval
     * @return AggregatedCollection
     */
    public function getAvgPostCharLengths(array $posts, $interval = 'Y-M'): AggregatedCollection
    {
        $collection = new AggregatedCollection($posts);

        $collection->groupBy(function (Post $post) use ($interval) {
            return $post->getCreatedAt()->format($interval);
        });

        $collection->map(function (AggregatedCollection $postsPerMonth) {
            return $postsPerMonth->map(function (Post $post) {
                return $post->getLength();
            })->runningAvg();
        });

        return $collection;
    }

    /**
     * @param array $posts
     * @param string $interval
     * @return AggregatedCollection
     */
    public function getAvgPostsPerUser(array $posts, $interval = 'Y-M'): AggregatedCollection
    {
        $collection = new AggregatedCollection($posts);

        $collection->groupBy(function (Post $post) use ($interval) {
            return $post->getCreatedAt()->format($interval);
        });

        $collection->map(function (AggregatedCollection $postsPerMonth) {
            $totalPosts = $postsPerMonth->count();
            $uniqueUsers = $postsPerMonth->map(function (Post $post) {
                return $post->getFromId();
            })->unique();
            $uniqueUsersCount = $uniqueUsers->count();

            return new RunningAvgPerUser($uniqueUsers->getValues(), new RunningAvg($totalPosts, $uniqueUsersCount));
        });

        return $collection;
    }

    /**
     * @param array $posts
     * @param string $interval
     * @return AggregatedCollection
     */
    public function getMaxPostLengths(array $posts, $interval = 'Y-M'): AggregatedCollection
    {
        $collection = new AggregatedCollection($posts);

        $collection->groupBy(function (Post $post) use ($interval) {
            return $post->getCreatedAt()->format($interval);
        });

        $collection->map(function (AggregatedCollection $postsPerMonth) {
            return $postsPerMonth->map(function (Post $post) {
                return $post->getLength();
            })->max();
        });

        return $collection;
    }

    /**
     * @param array $posts
     * @param string $interval
     * @return AggregatedCollection
     */
    public function getTotalPosts(array $posts, $interval = 'Y-W'): AggregatedCollection
    {
        $collection = new AggregatedCollection($posts);

        $collection->groupBy(function (Post $post) use ($interval) {
            return $post->getCreatedAt()->format($interval);
        });

        $collection->map(function (AggregatedCollection $postsPerWeek) {
            return $postsPerWeek->count();
        });

        return $collection;
    }
}