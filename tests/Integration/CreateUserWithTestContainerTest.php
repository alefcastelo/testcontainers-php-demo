<?php

namespace App\Tests\Integration;

class CreateUserWithTestContainerTest extends TestContainersWebTestCase
{
    public function testCreateUserWithRealRepository()
    {
        $client = static::createClient();

        $input = [
            'email' => 'test@example.com',
            'password' => 'password',
        ];

        $serializedInput = json_encode($input);

        $client->request('POST', '/', content: $serializedInput);

        $this->assertResponseStatusCodeSame(201);

        $expected = json_encode([
            'id' => 1,
            'email' => 'test@example.com',
        ]);

        $actual = $client->getResponse()->getContent();

        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }
}
