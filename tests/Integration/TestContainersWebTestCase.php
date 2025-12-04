<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Testcontainers\Container\StartedGenericContainer;
use App\Tests\Configuration\TestContainerConfiguration;
use Symfony\Bundle\FrameworkBundle\Console\Application;

class TestContainersWebTestCase extends WebTestCase
{
    protected static ?StartedGenericContainer $postgresContainer = null;
    protected static ?StartedGenericContainer $natsContainer     = null;

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

        TestContainerConfiguration::startContainers();

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

        TestContainerConfiguration::stopContainers();
    }
}
