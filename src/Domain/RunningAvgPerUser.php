<?php

declare(strict_types=1);

namespace MetricsAPI\Domain;

/**
 * Class RunningAvgPerUser
 * @package MetricsAPI\Domain
 */
class RunningAvgPerUser implements CalculatedValue
{
    /**
     * @var array
     */
    private $users;

    /**
     * @var RunningAvg
     */
    private $runningAvg;

    /**
     * RunningAvgPerUser constructor.
     * @param array $users
     * @param RunningAvg $runningAvg
     */
    public function __construct(array $users, RunningAvg $runningAvg)
    {
        $this->users = $users;
        $this->runningAvg = $runningAvg;
    }

    /**
     * @return array
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    /**
     * @return RunningAvg
     */
    public function getRunningAvg(): RunningAvg
    {
        return $this->runningAvg;
    }

    /**
     * @param RunningAvgPerUser $avgPostsPerUser
     * @return RunningAvgPerUser
     */
    public function update(RunningAvgPerUser $avgPostsPerUser): self
    {
        $newUsersCount = count(array_diff($avgPostsPerUser->getUsers(), $this->users));

        $this->users = array_unique(array_merge($this->users, $avgPostsPerUser->getUsers()));

        $this->runningAvg->update(
            $avgPostsPerUser->getRunningAvg()->getSum(),
            $newUsersCount
        );

        return $this;
    }

    /**
     * @return float
     */
    public function calculate(): float
    {
        return $this->runningAvg->calculate();
    }
}