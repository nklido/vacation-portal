<?php

namespace App\Infrastructure\Auth;

use App\Domain\User\User;

interface TokenService
{
    public function generateToken(User $user): string;

    public function verifyToken(String $token): array;
}