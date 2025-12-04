<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Symfony\Component\HttpFoundation\JsonResponse;

class CreateUserTest extends TestContainersWebTestCase
{
    public function testCreateUserWithoutOnboardWithMockedRepository()
    {
        $client = static::createClient();

        $input = json_encode([
            'email'    => 'test@example.com',
            'password' => 'password',
        ]);

        $client->request('POST', '/users/without-onboard', content: $input);

        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_CREATED);

        $expected = json_encode([
            'id'    => 2,
            'email' => 'test@example.com',
        ]);

        $actual = $client->getResponse()->getContent();

        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function testCreateUserWithOnboardWithMockedRepository()
    {
        $client = static::createClient();

        $input = json_encode([
            'email'    => 'test@example.com',
            'password' => 'password',
        ]);

        $client->request('POST', '/users', content: $input);

        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_CREATED);

        $expected = json_encode([
            'id'    => 2,
            'email' => 'test@example.com',
        ]);

        $actual = $client->getResponse()->getContent();

        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }
}
