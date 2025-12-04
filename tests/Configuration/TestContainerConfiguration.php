<?php

declare(strict_types=1);

namespace App\Tests\Configuration;

use Testcontainers\Modules\PostgresContainer;
use Testcontainers\Container\GenericContainer;
use Testcontainers\Container\StartedGenericContainer;

class TestContainerConfiguration
{
    protected static ?StartedGenericContainer $postgresContainer = null;
    protected static ?StartedGenericContainer $natsContainer     = null;

    public static function startPostgresContainer(): void
    {
        self::$postgresContainer = new PostgresContainer(
            version: 'latest',
            username: 'app_user',
            password: 's3cr3t',
            database: 'app_test',
        )
            ->withExposedPorts(5432)
            ->start();

        $postgresHost = self::$postgresContainer->getHost();
        $postgresPort = self::$postgresContainer->getFirstMappedPort();

        $_ENV['DATABASE_URL'] = "postgres://app_user:s3cr3t@{$postgresHost}:{$postgresPort}/app_test?serverVersion=18&charset=utf8";
    }

    public static function startNatsContainer(): void
    {
        self::$natsContainer = new GenericContainer(image: 'nats:latest')
            ->withEntryPoint('/nats-server')
            ->withCommand(['--config', '/nats-server.conf', '--user', 'app_user', '--pass', 's3cr3t'])
            ->withExposedPorts(4222)
            ->start();

        $natsHost = self::$natsContainer->getHost();
        $natsPort = self::$natsContainer->getFirstMappedPort();

        $_ENV['NATS_HOST'] = $natsHost;
        $_ENV['NATS_PORT'] = $natsPort;
        $_ENV['NATS_USER'] = 'app_user';
        $_ENV['NATS_PASS'] = 's3cr3t';
    }

    public static function stopPostgresContainer(): void
    {
        self::$postgresContainer?->stop();
    }

    public static function stopNatsContainer(): void
    {
        self::$natsContainer?->stop();
    }

    public static function startContainers(): void
    {
        self::startPostgresContainer();
        self::startNatsContainer();
    }

    public static function stopContainers(): void
    {
        self::stopPostgresContainer();
        self::stopNatsContainer();
    }
}
