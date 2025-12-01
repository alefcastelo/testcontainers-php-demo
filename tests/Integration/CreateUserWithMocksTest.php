<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\User;
use Basis\Nats\Client;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CreateUserWithMocksTest extends WebTestCase
{
    public function testCreateUserWithMockedRepository()
    {
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())
            ->method('save');

        $client = static::createClient();

        $client->getContainer()->set(UserRepository::class, $userRepository);

        $input = json_encode([
            'email'    => 'test@example.com',
            'password' => 'password',
        ]);

        $client->request('POST', '/users', content: $input);

        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_CREATED);
    }

    public function testCreateComplexUserWithMockedRepository()
    {

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())
            ->method('findByEmail')
            ->willReturn(null);

        $userRepository->expects($this->once())
            ->method('save');

        // $natsClient = $this->createMock(Client::class);
        // $natsClient->expects($this->once())
        //     ->method('publish');

        // $natsClient->expects($this->once())
        //     ->method('process');

        $client = static::createClient();

        $client->getContainer()->set(UserRepository::class, $userRepository);
        // $client->getContainer()->set(Client::class, $natsClient);

        $input = json_encode([
            'email'    => 'test@example.com',
            'password' => 'password',
        ]);

        $client->request('POST', '/users/complex', content: $input);

        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_CREATED);
    }

    public function testCreateSameComplexUserWithMockedRepository()
    {
        $user = new User()
            ->withEmail('test@example.com')
            ->withPasswordHash('password');
        $user->id = 1;

        $userRepository = $this->createMock(UserRepository::class);

        $userRepository->expects($this->once())
            ->method('findByEmail')
            ->willReturn($user);

        $client = static::createClient();

        $client->getContainer()->set(UserRepository::class, $userRepository);

        $input = json_encode([
            'email'    => 'test@example.com',
            'password' => 'password',
        ]);

        $client->request('POST', '/users/complex', content: $input);

        $this->assertResponseStatusCodeSame(JsonResponse::HTTP_BAD_REQUEST);
    }
}
