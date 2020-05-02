<?php

namespace spec\MetricsAPI\Domain;

use MetricsAPI\Domain\RunningAvg;
use PhpSpec\ObjectBehavior;

/**
 * Class RunningAvgSpec
 * @package spec\MetricsAPI\Domain
 */
class RunningAvgSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(20, 4);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RunningAvg::class);
    }

    function it_calculates_average()
    {
        $this->calculate()->shouldBe((double)5);
    }

    function it_updates_average()
    {
        $this->update(10, 1);

        $this->calculate()->shouldBe((double)6);
    }
}
