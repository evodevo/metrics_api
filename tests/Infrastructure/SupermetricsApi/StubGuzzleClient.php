<?php

namespace MetricsAPI\Tests\Infrastructure\SupermetricsApi;

use Alekseytupichenkov\GuzzleStub\Handler\StubHandler;
use Alekseytupichenkov\GuzzleStub\Model\Fixture;
use Alekseytupichenkov\GuzzleStub\Traits\GuzzleClientTrait;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;

/**
 * Class StubGuzzleClient
 * @package MetricsAPI\Tests\Infrastructure\SupermetricsApi
 */
class StubGuzzleClient extends \GuzzleHttp\Client
{
    use GuzzleClientTrait;

    const TEST_API_URL = 'https://test.api/';
    const TEST_API_TOKEN = 'smslt_12345678901234567_aaabbbccc';

    /**
     * @var string
     */
    private $fixturesFilePath;

    /**
     * StubGuzzleClient constructor.
     * @param string $fixturesFilePath
     * @param array $config
     */
    public function __construct(string $fixturesFilePath, array $config = [])
    {
        $this->fixturesFilePath = ROOT_PATH . $fixturesFilePath;

        parent::__construct($config);

        $this->handlerStack = $this->getConfig('handler');

        $this->stubHandler = new StubHandler();
        $this->handlerStack->setHandler($this->stubHandler);

        $this->loadFixtures();
    }

    /**
     * Loads API response fixtures.
     */
    protected function loadFixtures()
    {
        $registerRequest = '{"client_id":"test123","name":"John","email":"test@email.address"}';

        $registerResponse = '{
            "meta": {
                "request_id": "test_request123"
            },
            "data": {
                "client_id": "test123",
                "email": "test@email.address",
                "sl_token": "smslt_12345678901234567_aaabbbccc"
            }
        }';

        $this->append(new Fixture(
            new Request('POST', self::TEST_API_URL . 'assignment/register', [], $registerRequest),
            new Response(200, [], $registerResponse)
        ));
    }

    /**
     * @param string $fixturesFilePath
     */
    public function loadPostFixtures(string $fixturesFilePath)
    {
        $fixtures = $this->loadPostsFromFile(ROOT_PATH . $fixturesFilePath);
        $page1 = array_slice($fixtures, 0, 3);
        $page2 = array_slice($fixtures, 3, 3);
        $page3 = array_slice($fixtures, 6, 3);

        $this->append(new Fixture(
            new Request('GET', $this->createPostsPageQuery(1)),
            new Response(200, [], $this->createPostsResponse($page1, 1))
        ));

        $this->append(new Fixture(
            new Request('GET', $this->createPostsPageQuery(2)),
            new Response(200, [], $this->createPostsResponse($page2, 2))
        ));

        $this->append(new Fixture(
            new Request('GET', $this->createPostsPageQuery(3)),
            new Response(200, [], $this->createPostsResponse($page3, 3))
        ));
    }

    /**
     * @param string $filePath
     * @return array
     */
    private function loadPostsFromFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException('Fixtures file does not exist at path: ' . $filePath);
        }

        $fixtures = file_get_contents($filePath);
        if (!$fixtures) {
            throw new \RuntimeException('Fixtures file is empty');
        }

        $fixtures = json_decode($fixtures, true);
        if ($fixtures === null) {
            throw new \RuntimeException('Fixtures file does not contain a valid json');
        }

        return $fixtures;
    }

    /**
     * @param array $posts
     * @param int $page
     * @return string
     */
    private function createPostsResponse(array $posts, int $page): string
    {
        return '{
            "meta": {
                "request_id": "test_request123"
            },
            "data": {
                "page": '.$page.',
                "posts": '.json_encode($posts).'
            }
        }';
    }

    /**
     * @param int $page
     * @return Uri
     */
    private function createPostsPageQuery(int $page): Uri
    {
        $postsUri = new Uri(self::TEST_API_URL . 'assignment/posts');

        return $postsUri->withQuery(http_build_query([
            'page' => $page,
            'sl_token' => self::TEST_API_TOKEN,
        ]));
    }
}