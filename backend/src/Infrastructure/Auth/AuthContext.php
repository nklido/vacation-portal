<?php namespace App\Infrastructure\Auth;

use App\Domain\Auth\AuthUser;
use App\Domain\Exception\UnauthorizedException;
use App\Shared\Request;
use Exception;

class AuthContext
{
    private static ?self $instance = null;
    private array $decoded = [];

    private function __construct(private readonly TokenService $tokenService) {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self(new JwtService());
        }
        return self::$instance;
    }

    public static function setInstance(AuthContext $context): void
    {
        self::$instance = $context;
    }

    /**
     * @throws UnauthorizedException
     */
    public function requireAuth(): void
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!str_starts_with($authHeader, 'Bearer ')) {
            throw new UnauthorizedException('Missing or malformed Authorization header');
        }

        $token = substr($authHeader, 7);

        try {
            $this->decoded = $this->tokenService->verifyToken($token);
            Request::setAuthUser(new AuthUser(
                $this->decoded['sub'],
                $this->decoded['role']
            ));
        } catch (Exception $e) {
            throw new UnauthorizedException('Invalid or expired token');
        }
    }

    /**
     * @throws Exception
     */
    public function requireRole(int $role): void
    {
        if ($this->userRole() !== $role) {
            throw new UnauthorizedException('Action was forbidden');
        }
    }

    public static function id(): ?int
    {
        return Request::getAuthUser()?->id;
    }

    public static function roleId(): ?int
    {
        return Request::getAuthUser()?->roleId;
    }

    public function userRole(): ?int
    {
        return $this->decoded['role'] ?? null;
    }
}