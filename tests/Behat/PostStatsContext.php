<?php

namespace MetricsAPI\Tests\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use MetricsAPI\Application\ReportGenerator;
use MetricsAPI\Tests\Infrastructure\SupermetricsApi\StubGuzzleClient;

/**
 * Defines application features from the specific context.
 */
class PostStatsContext implements Context
{
    /**
     * @var StubGuzzleClient
     */
    private $client;

    /**
     * @var ReportGenerator
     */
    private $reportGenerator;

    /**
     * @var
     */
    private $calculatedStats;

    /**
     * PostStatsContext constructor.
     * @param StubGuzzleClient $client
     * @param ReportGenerator $reportGenerator
     */
    public function __construct(StubGuzzleClient $client, ReportGenerator $reportGenerator)
    {
        $this->client = $client;
        $this->reportGenerator = $reportGenerator;
    }

    /**
     * @Given there are posts loaded from file :filePath
     *
     * @param $filePath
     */
    public function thereArePostsLoadedFromFile($filePath)
    {
        $this->client->loadPostFixtures($filePath);
    }

    /**
     * @When I generate average post char lengths report from :pages post pages
     *
     * @param $pages
     */
    public function iRunGenerateAvgPostCharLengthsReportCommand(int $pages)
    {
        $this->calculatedStats = json_encode($this->reportGenerator->generate($pages, ['avg_post_char_lengths']));
    }

    /**
     * @When I generate average posts per user report from :pages post pages
     *
     * @param $pages
     */
    public function iRunGenerateAvgPostsPerUserReportCommand(int $pages)
    {
        $this->calculatedStats = json_encode($this->reportGenerator->generate($pages, ['avg_posts_per_user']));
    }

    /**
     * @When I generate max post lengths report from :pages post pages
     *
     * @param $pages
     */
    public function iRunGenerateMaxPostLengthsReportCommand(int $pages)
    {
        $this->calculatedStats = json_encode($this->reportGenerator->generate($pages, ['max_post_lengths']));
    }

    /**
     * @When I generate total posts by week report from :pages post pages
     *
     * @param $pages
     */
    public function iRunGenerateTotalPostsByWeekReportCommand(int $pages)
    {
        $this->calculatedStats = json_encode($this->reportGenerator->generate($pages, ['total_posts_by_week']));
    }

    /**
     * @Then I should get the following stats calculated:
     *
     * @param PyStringNode $stats
     */
    public function iShouldGetTheFollowingStatsCalculated(PyStringNode $stats)
    {
        $expected = json_decode($stats, true);
        $actual = json_decode($this->calculatedStats, true);

        $arrayDiff = $this->compareArrays($expected,$actual);

        if(!empty($arrayDiff)){
            throw new \RuntimeException('Actual result does not match expected result');
        }
    }

    /**
     * @param $array1
     * @param $array2
     * @return array
     */
    private function compareArrays($array1, $array2): array
    {
        $outputDiff = [];

        foreach ($array1 as $key => $value) {
            if (array_key_exists($key, $array1)) {
                if (is_array($value)) {
                    $recursiveDiff = $this->compareArrays($value, $array2[$key]);

                    if (count($recursiveDiff)) {
                        $outputDiff[$key] = $recursiveDiff;
                    }
                } else if (!in_array($value, $array2)) {
                    $outputDiff[$key] = $value;
                }
            } else if (!in_array($value, $array2)) {
                $outputDiff[$key] = $value;
            }
        }

        return $outputDiff;
    }
}
