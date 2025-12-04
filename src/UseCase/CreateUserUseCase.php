<?php

declare(strict_types=1);

namespace App\UseCase;

use App\Entity\User;
use Basis\Nats\Client;
use InvalidArgumentException;
use App\Repository\UserRepository;

class CreateUserUseCase
{
    public function __construct(
        protected readonly UserRepository $userRepository,
        protected readonly Client $natsClient,
    ) {
    }

    public function __invoke(string $email, string $password): User|InvalidArgumentException
    {
        if (empty($email)) {
            return new InvalidArgumentException('Email is required');
        }

        if (empty($password)) {
            return new InvalidArgumentException('Password is required');
        }

        if ($this->userRepository->findByEmail($email)) {
            return new InvalidArgumentException('Email already in use');
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $user = new User()
            ->withEmail($email)
            ->withPasswordHash($passwordHash);

        $this->userRepository->save($user);

        $this->natsClient->publish('user.created', $user->jsonSerialize());
        $this->natsClient->process();

        sleep(1);

        return $user;
    }
}
