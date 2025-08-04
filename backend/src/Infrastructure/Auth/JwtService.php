<?php

namespace App\Infrastructure\Auth;

use App\Domain\Clock;
use App\Domain\User\User;
use App\Shared\SystemClock;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService implements TokenService
{
    private string $secret;

    private string $issuer;

    private const string ALGORITHM = 'HS256';
    private Clock $clock;

    private const int EXPIRE_SECONDS = 3600;

    public function __construct(Clock $clock = new SystemClock())
    {
        $this->secret = $_ENV['JWT_SECRET'];
        $this->issuer = $_ENV['JWT_ISSUER'] ?? 'vacation-portal';
        $this->clock = $clock;
    }

    public function generateToken(User $user): string
    {
        $payload = [
            'iss' => $this->issuer,
            'sub' => $user->getId(),
            'role' => $user->getRole()->getId(),
            'exp' => $this->clock->now() + self::EXPIRE_SECONDS
        ];

        return JWT::encode($payload, $this->secret, self::ALGORITHM);
    }

    public function verifyToken(string $token): array
    {
        return (array) JWT::decode($token, new Key($this->secret, self::ALGORITHM));
    }
}
