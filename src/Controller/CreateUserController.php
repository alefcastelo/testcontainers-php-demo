<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CreateUserController extends AbstractController
{
    public function __construct(
        protected readonly UserRepository $userRepository,
    ) {
    }

    #[Route('/', name: 'create_user', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $content = json_decode($request->getContent(), true);

        ['email' => $email, 'password' => $password] = $content;

        if (empty($email)) {
            return new JsonResponse(['error' => 'Email is required'], Response::HTTP_BAD_REQUEST);
        }

        if (empty($password)) {
            return new JsonResponse(['error' => 'Password is required'], Response::HTTP_BAD_REQUEST);
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $user = new User()
            ->withEmail($email)
            ->withPasswordHash($passwordHash);

        $this->userRepository->save($user);

        return new JsonResponse($user, Response::HTTP_CREATED);
    }
}
