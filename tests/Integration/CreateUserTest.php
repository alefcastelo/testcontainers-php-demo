<?php

namespace App\Tests\Integration;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class CreateUserTest extends WebTestCase
{
    public function testCreateUserWithMockedRepository()
    {
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())
            ->method('save');

        $client = static::createClient();

        $client->getContainer()
            ->set(UserRepository::class, $userRepository);

        $input = json_encode([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $client->request('POST', '/', content: $input);

        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_CREATED);
    }
}
