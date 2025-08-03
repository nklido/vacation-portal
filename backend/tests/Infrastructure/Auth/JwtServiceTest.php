<?php

namespace Tests\Infrastructure\Auth;

use App\Domain\Role\Role;
use App\Domain\User\User;
use App\Infrastructure\Auth\JwtService;
use App\Shared\FixedClock;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use PHPUnit\Framework\TestCase;

class JwtServiceTest extends TestCase
{
    private JwtService $jwtService;
    private User $user;

    protected function setUp(): void
    {
        $_ENV['JWT_SECRET'] = 'test_secret';
        $_ENV['JWT_ISSUER'] = 'test_issuer';
        $this->jwtService = new JwtService();

        $this->user = User::createNew(
            name: 'Test User',
            email: 'test@example.com',
            plainPassword: 'password',
            code: 1234,
            role: Role::manager()
        );
        $this->user->setId(1);
    }

    public function testGenerateAndVerifyToken(): void
    {
        $token = $this->jwtService->generateToken($this->user);
        $payload = $this->jwtService->verifyToken($token);

        $this->assertSame($this->user->getId(), $payload['sub']);
        $this->assertSame($this->user->getRole()->getId(), $payload['role']);
        $this->assertSame('test_issuer', $payload['iss']);
        $this->assertGreaterThan(time(), $payload['exp']);
    }

    public function testInvalidTokenFails(): void
    {
        $this->expectException(SignatureInvalidException::class);
        $token = $this->jwtService->generateToken($this->user);

        $parts = explode('.', $token);
        $tampered = $parts[0] . '.' . $parts[1] . '.invalid';
        $this->jwtService->verifyToken($tampered);
    }

    public function testExpiredTokenThrows(): void
    {
        $jwtService = new JwtService(new FixedClock(time() - 7200));
        $token = $jwtService->generateToken($this->user);
        $this->expectException(ExpiredException::class);
        $jwtService->verifyToken($token);
    }

}
