<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Tests\Config\TestContainersWebTestCase;

class CreateUserWithTestContainerTest extends TestContainersWebTestCase
{
    public function testCreateUserWithRealRepository()
    {
        $client = static::createClient();

        $input = [
            'email'    => 'test@example.com',
            'password' => 'password',
        ];

        $serializedInput = json_encode($input);

        $client->request('POST', '/users', content: $serializedInput);

        $this->assertResponseStatusCodeSame(201);

        $expected = json_encode([
            'id'    => 2,
            'email' => 'test@example.com',
        ]);

        $actual = $client->getResponse()->getContent();

        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function testCreateComplexUserWithRealRepository()
    {
        $client = static::createClient();

        $input = [
            'email'    => 'test@example.com',
            'password' => 'password',
        ];

        $serializedInput = json_encode($input);

        $client->request('POST', '/users/complex', content: $serializedInput);

        $this->assertResponseStatusCodeSame(201);

        $expected = json_encode([
            'id'    => 2,
            'email' => 'test@example.com',
        ]);

        $actual = $client->getResponse()->getContent();

        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function testCreateSameComplexUserWithRealRepository()
    {
        $client = static::createClient();

        $input = [
            'email'    => 'alef@example.com',
            'password' => 'password',
        ];

        $serializedInput = json_encode($input);

        $client->request('POST', '/users/complex', content: $serializedInput);

        $this->assertResponseStatusCodeSame(400);

        $expected = json_encode(['error' => 'Email already in use']);

        $actual = $client->getResponse()->getContent();

        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }
}
