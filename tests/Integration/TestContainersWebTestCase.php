<?php

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Testcontainers\Container\StartedGenericContainer;
use Testcontainers\Modules\PostgresContainer;

class TestContainersWebTestCase extends WebTestCase
{
    protected static ?StartedGenericContainer $postgresContainer = null;

    public static function createClient(array $options = [], array $server = []): KernelBrowser
    {
        $kernel = self::$kernel;
        $client = $kernel->getContainer()->get('test.client');
        $client->setServerParameters($server);

        return self::getClient($client);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->postgresContainer = new PostgresContainer(
            version: 'latest',
            username: 'app_user',
            password: 's3cr3t',
            database: 'app_test',
        )
            ->withExposedPorts(5432)
            ->start();

        $host = $this->postgresContainer->getHost();
        $port = $this->postgresContainer->getFirstMappedPort();

        $databaseUrl = "postgres://app_user:s3cr3t@{$host}:{$port}/app_test?serverVersion=18&charset=utf8";

        $_ENV['DATABASE_URL'] = $databaseUrl;
        $_SERVER['DATABASE_URL'] = $databaseUrl;

        self::bootKernel();

        $application = new Application(self::$kernel);
        $application->setAutoExit(false);
        $input = new ArrayInput([
            'command' => 'doctrine:schema:update',
            '--force' => true,
        ]);

        $application->run($input, new ConsoleOutput());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->postgresContainer?->stop();
    }
}
