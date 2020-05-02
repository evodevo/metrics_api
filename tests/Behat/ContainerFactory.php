<?php

namespace MetricsAPI\Tests\Behat;

use Dotenv\Dotenv;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

define('ROOT_PATH', dirname(dirname(__DIR__)));

/**
 * Class ContainerFactory
 * @package MetricsAPI\Tests\Behat
 */
class ContainerFactory
{
    /**
     * @return ContainerInterface
     * @throws \Exception
     */
    public static function create(): ContainerInterface
    {
        $env = getenv('ENV');
        $envFile = $env === 'test' ? '.env.test' : '.env';
        $dotenv = Dotenv::createImmutable(ROOT_PATH . '/', $envFile);
        $dotenv->load();

        $containerBuilder = new ContainerBuilder();
        $loader = new YamlFileLoader($containerBuilder, new FileLocator(ROOT_PATH));
        $loader->load('config/services.yaml');
        if ($env === 'test') {
            $loader->load('config/services_test.yaml');
        }

        $containerBuilder->compile(true);

        return $containerBuilder;
    }
}