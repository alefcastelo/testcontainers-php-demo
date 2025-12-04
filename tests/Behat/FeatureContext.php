<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use App\Kernel;
use Behat\Step\Then;
use Behat\Step\When;
use Jfcherng\Diff\Differ;
use Behat\Hook\AfterFeature;
use Behat\Hook\BeforeFeature;
use Jfcherng\Diff\DiffHelper;
use PHPUnit\Framework\Assert;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Jfcherng\Diff\Renderer\RendererConstant;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use PHPUnit\Framework\ExpectationFailedException;
use Symfony\Component\HttpKernel\KernelInterface;
use Testcontainers\Container\StartedGenericContainer;
use App\Tests\Configuration\TestContainerConfiguration;
use Symfony\Bundle\FrameworkBundle\Console\Application;

class FeatureContext extends Assert implements Context
{
    protected string $responseBody;
    protected string $originalResponseBody;
    protected int $responseStatusCode;
    protected array $requestHeaders                              = [];
    protected static ?StartedGenericContainer $postgresContainer = null;
    protected static ?StartedGenericContainer $natsContainer     = null;
    protected static ?KernelInterface $kernel                    = null;

    #[BeforeFeature]
    public static function before(): void
    {
        if (null === self::$kernel) {
            self::$kernel = new Kernel('test', true);
        }

        self::$kernel->boot();

        TestContainerConfiguration::startContainers();

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

    #[AfterFeature]
    public static function after(): void
    {
        TestContainerConfiguration::stopContainers();
        self::$kernel->shutdown();
    }

    #[When('I send :method request to :url with body:')]
    public function iSendRequestToWith($httpMethod, $url, PyStringNode $node)
    {
        $client = self::$kernel->getContainer()->get('test.client');
        $client->request($httpMethod, $url, content: $node->getRaw());

        $response                 = $client->getResponse();
        $this->responseBody       = $response->getContent();
        $this->responseStatusCode = $response->getStatusCode();
    }

    #[Then('I should receive a status code :statusCode and a json response equals to:')]
    public function iShouldReceiveAStatusCodeResponseWithBody(int $statusCode, PyStringNode $raw): void
    {
        $expected = json_decode($raw->getRaw(), true, 512, JSON_THROW_ON_ERROR);
        $actual   = json_decode($this->responseBody, true, 512, JSON_THROW_ON_ERROR);

        $fails = [];

        try {
            $this->assertEquals($statusCode, $this->responseStatusCode);
        } catch (ExpectationFailedException $e) {
            $fails[] = $e->getMessage();
        }

        if (empty($this->responseBody)) {
            $this->fail('Response body is empty');
        }

        try {
            $this->assertEqualsCanonicalizing(
                $expected,
                $actual,
            );
        } catch (ExpectationFailedException $e) {
            $old = json_encode($actual, JSON_PRETTY_PRINT);
            $new = json_encode($expected, JSON_PRETTY_PRINT);

            $result = DiffHelper::calculate($old, $new, 'Unified', [
                'context'                => Differ::CONTEXT_ALL,
                'ignoreCase'             => false,
                'ignoreLineEnding'       => true,
                'ignoreWhitespace'       => true,
                'lengthLimit'            => 10000,
                'fullContextIfIdentical' => false,
            ], [
                'cliColorization' => RendererConstant::CLI_COLOR_ENABLE,
            ]);

            $fails[] = sprintf('Diff: %s', $result);
            $fails[] = sprintf('Actual: %s', $new);
            $fails[] = sprintf('Expected: %s', $old);
        }

        if (!empty($fails)) {
            $this->fail(join(PHP_EOL, $fails));
        }
    }
}
