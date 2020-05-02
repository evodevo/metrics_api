<?php

namespace spec\MetricsAPI\Domain;

use MetricsAPI\Domain\AggregatedCollection;
use MetricsAPI\Domain\Post;
use MetricsAPI\Domain\PostAggregator;
use PhpSpec\ObjectBehavior;

/**
 * Class PostAggregatorSpec
 * @package spec\MetricsAPI\Domain
 */
class PostAggregatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(PostAggregator::class);
    }

    function it_calculates_avg_post_char_lengths_by_month()
    {
        $result = $this->getAvgPostCharLengths($this->givenPosts(), 'Y-M');

        $result->shouldBeAnInstanceOf(AggregatedCollection::class);
        $result->shouldHaveCount(2);
        $result->getCalculatedValues()->shouldHaveKeyWithValue('2020-Apr', 365.29);
        $result->getCalculatedValues()->shouldHaveKeyWithValue('2020-Mar', (double)383);
    }

    function it_calculates_avg_posts_per_user_by_month()
    {
        $result = $this->getAvgPostsPerUser($this->givenPosts(), 'Y-M');

        $result->shouldBeAnInstanceOf(AggregatedCollection::class);
        $result->shouldHaveCount(2);
        $result->getCalculatedValues()->shouldHaveKeyWithValue('2020-Apr', 1.17);
        $result->getCalculatedValues()->shouldHaveKeyWithValue('2020-Mar', (double)1);
    }

    function it_calculates_max_post_lengths_by_month()
    {
        $result = $this->getMaxPostLengths($this->givenPosts(), 'Y-M');

        $result->shouldBeAnInstanceOf(AggregatedCollection::class);
        $result->shouldHaveCount(2);
        $result->shouldHaveKeyWithValue('2020-Apr', 633);
        $result->shouldHaveKeyWithValue('2020-Mar', 510);
    }

    function it_calculates_total_posts_by_week()
    {
        $result = $this->getTotalPosts($this->givenPosts(), 'Y-W');

        $result->shouldBeAnInstanceOf(AggregatedCollection::class);
        $result->shouldHaveCount(2);
        $result->shouldHaveKeyWithValue('2020-17', 7);
        $result->shouldHaveKeyWithValue('2020-13', 2);
    }

    /**
     * @return array
     */
    private function givenPosts(): array
    {
        $posts = json_decode(file_get_contents(ROOT_PATH . '/tests/fixtures/posts.json'), true);

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
        return new Post(
            $postData['id'],
            $postData['from_id'],
            $postData['from_name'],
            $postData['message'],
            \DateTime::createFromFormat(DATE_RFC3339, $postData['created_time'])
        );
    }
}
