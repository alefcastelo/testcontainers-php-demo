<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use Behat\Config\Suite;
use Behat\Config\Config;
use Behat\Config\Profile;
use App\Tests\Behat\FeatureContext;
use Behat\Config\Formatter\ProgressFormatter;

$defaultSuite = new Suite('default');
$defaultSuite->withContexts(FeatureContext::class);

return new Config()
    ->withProfile(
        new Profile('default', [
            'testers' => [
                'stop_on_failure' => true,
                'strict'          => true,
            ],
        ])
        ->withFormatter(new ProgressFormatter())
        ->withSuite($defaultSuite)
    );
