<?php

declare(strict_types=1);

namespace App\Tests\Config;

use Testcontainers\Modules\PostgresContainer;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Testcontainers\Container\StartedGenericContainer;
use Symfony\Bundle\FrameworkBundle\Console\Application;

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

        $_ENV['DATABASE_URL']    = $databaseUrl;
        $_SERVER['DATABASE_URL'] = $databaseUrl;

        self::bootKernel();

        $application = new Application(self::$kernel);
        $application->setAutoExit(false);

        $runMigrationsInput = new ArrayInput([
            'command'          => 'doctrine:migrations:migrate',
            '--no-interaction' => true,
            '--all-or-nothing' => true,
        ]);

        $application->run($runMigrationsInput, new NullOutput());

        $runFixturesInput = new ArrayInput([
            'command'               => 'doctrine:fixtures:load',
            '--no-interaction'      => true,
            '--purge-with-truncate' => true,
        ]);

        $application->run($runFixturesInput, new NullOutput());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->postgresContainer?->stop();
    }
}
