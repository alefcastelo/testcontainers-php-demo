<?php

declare(strict_types=1);

namespace App\Controller;

use InvalidArgumentException;
use App\UseCase\CreateComplexUserUseCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CreateComplexUserController extends AbstractController
{
    public function __construct(
        protected readonly CreateComplexUserUseCase $createComplexUserUseCase,
    ) {
    }

    #[Route('/users/complex', name: 'create_complex_user', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $content = json_decode($request->getContent(), true);

        ['email' => $email, 'password' => $password] = $content;

        $userOrException = ($this->createComplexUserUseCase)($email, $password);

        if ($userOrException instanceof InvalidArgumentException) {
            return new JsonResponse(['error' => $userOrException->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($userOrException, Response::HTTP_CREATED);
    }
}
