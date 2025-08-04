<?php

namespace App\Application\Controllers;

use App\Domain\User\UserRepository;
use App\Infrastructure\Auth\tokenService;
use App\Shared\JsonResponse;
use App\Shared\Request;
use App\Shared\Response;

class LoginController
{
    private TokenService $tokenService;
    private UserRepository $userRepository;

    public function __construct(TokenService $tokenService, UserRepository $userRepository)
    {
        $this->tokenService = $tokenService;
        $this->userRepository = $userRepository;
    }

    public function login(): JsonResponse
    {
        $input = Request::json();
        $email = $input['email'] ?? null;
        $password = $input['password'] ?? null;

        if (!$email || !$password) {
            return Response::json(['error' => 'Missing credentials'], 400);
        }

        $user = $this->userRepository->findByEmail($email);

        if (!$user || !password_verify($password, $user->getPassword())) {
            return Response::json(['error' => 'Invalid credentials'], 401);
        }

        $token = $this->tokenService->generateToken($user);
        return Response::json([
            'token' => $token,
            'user' => $user->toArray()
        ]);
    }
}