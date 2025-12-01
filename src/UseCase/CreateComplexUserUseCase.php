<?php

declare(strict_types=1);

namespace App\UseCase;

use App\Entity\User;
use InvalidArgumentException;
use App\Repository\UserRepository;
use App\Provider\EmailSenderProvider;

class CreateComplexUserUseCase
{
    public function __construct(
        protected readonly UserRepository $userRepository,
        protected readonly EmailSenderProvider $emailSenderProvider,
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
        $this->emailSenderProvider->sendEmail(
            $email,
            'Welcome to our app',
            'Welcome to our app',
        );

        return $user;
    }
}
