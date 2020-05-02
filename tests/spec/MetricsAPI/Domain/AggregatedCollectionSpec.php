<?php

namespace spec\MetricsAPI\Domain;

use MetricsAPI\Domain\AggregatedCollection;
use MetricsAPI\Domain\Post;
use MetricsAPI\Domain\RunningAvg;
use PhpSpec\ObjectBehavior;

/**
 * Class AggregatedCollectionSpec
 * @package spec\MetricsAPI\Domain
 */
class AggregatedCollectionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith($this->givenPosts());
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AggregatedCollection::class);
    }

    function it_groups_collection_entries_by_callback()
    {
        $this->groupBy(function (Post $post) {
            return $post->getCreatedAt()->format('Y-M');
        });

        $this->shouldHaveKey('2020-Apr');
        $this->shouldHaveKey('2020-Mar');
        $this->shouldHaveCount(2);
    }

    function it_returns_running_avg_from_collection_entries()
    {
        $this->beConstructedWith([5, 1, 4, 7, 3]);
        $this->runningAvg()->shouldBeLike(new RunningAvg(20, 5));
    }

    function it_maps_collection_entries_with_callback_result()
    {
        $this->map(function (Post $post) {
            return $post->getLength();
        });

        $this->shouldHaveCount(9);
        $this->getValues()->shouldIterateAs([302, 299, 172, 510, 463, 306, 382, 633, 256]);
    }

    function it_updates_running_avg_for_collection_entries()
    {
        $this->beConstructedWith([
            new RunningAvg(10, 2),
            new RunningAvg(12, 3)
        ]);
        $this->updateAvg(new AggregatedCollection([
            new RunningAvg(8, 2),
            new RunningAvg(6, 3)
        ]));
        $this->shouldHaveCount(2);
        $this->getValues()->shouldIterateLike([
            new RunningAvg(18, 4),
            new RunningAvg(18, 6)
        ]);
    }

    function it_fails_to_update_avg_for_non_running_avg_entries()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->duringUpdateAvg(new AggregatedCollection([
                new RunningAvg(8, 2),
                new RunningAvg(6, 3)
            ]));
    }

    function it_updates_max_value_for_collection_entries()
    {
        $this->beConstructedWith([17, 2]);
        $this->updateMax(new AggregatedCollection([5, 8]));
        $this->shouldHaveCount(2);
        $this->getValues()->shouldIterateAs([17, 8]);
    }

    function it_updates_total_values_for_collection_entries()
    {
        $this->beConstructedWith([1, 2]);
        $this->updateTotal(new AggregatedCollection([5, 6]));
        $this->shouldHaveCount(2);
        $this->getValues()->shouldIterateAs([6, 8]);
    }

    function it_merges_collection_entries_by_callback_result()
    {
        $this->beConstructedWith([2, 3]);
        $this->merge(
            new AggregatedCollection([3, 4]),
            function ($currentValue, $newValue) {
                return $currentValue * $newValue;
            }
        );
        $this->shouldHaveCount(2);
        $this->getValues()->shouldIterateAs([6, 12]);
    }

    function it_returns_calculated_values()
    {
        $this->beConstructedWith([
            new RunningAvg(10, 2),
            new RunningAvg(12, 3)
        ]);
        $this->getCalculatedValues()->shouldHaveCount(2);
        $this->getCalculatedValues()->shouldIterateAs([(double)5, (double)4]);
    }

    function it_returns_collection_entries_count()
    {
        $this->count()->shouldBe(9);
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
