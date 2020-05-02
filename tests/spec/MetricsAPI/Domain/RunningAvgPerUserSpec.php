<?php

namespace spec\MetricsAPI\Domain;

use MetricsAPI\Domain\RunningAvg;
use MetricsAPI\Domain\RunningAvgPerUser;
use PhpSpec\ObjectBehavior;

/**
 * Class RunningAvgPerUserSpec
 * @package spec\MetricsAPI\Domain
 */
class RunningAvgPerUserSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            ['user_1', 'user_2', 'user_3', 'user_4'],
            new RunningAvg(20, 4)
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RunningAvgPerUser::class);
    }

    function it_calculates_average()
    {
        $this->calculate()->shouldBe((double)5);
    }

    function it_updates_average_with_new_user()
    {
        $this->update(new RunningAvgPerUser(['user_5'], new RunningAvg(10, 1)));

        $this->calculate()->shouldBe((double)6);
    }

    function it_updates_average_with_existing_user()
    {
        $this->update(new RunningAvgPerUser(['user_1'], new RunningAvg(10, 1)));

        $this->calculate()->shouldBe(7.5);
    }
}
