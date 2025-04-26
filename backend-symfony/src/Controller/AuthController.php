<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use App\Service\UserService;

class AuthController extends AbstractController
{
    private $userService;
    private $jwtTokenManager;

    public function __construct(UserService $userService, JWTTokenManagerInterface $jwtTokenManager)
    {
        $this->userService = $userService;
        $this->jwtTokenManager = $jwtTokenManager;
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email']) || !isset($data['password'])) {
            return new JsonResponse(['error' => 'Invalid credentials'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user = $this->userService->getUserByEmail($data['email']); // Zakładam, że masz metodę getUserByEmail w UserService

        if (!$user || !password_verify($data['password'], $user->getPassword())) {
            return new JsonResponse(['error' => 'Invalid credentials'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $token = $this->jwtTokenManager->create($user);

        return new JsonResponse(['token' => $token], JsonResponse::HTTP_OK);
    }
}