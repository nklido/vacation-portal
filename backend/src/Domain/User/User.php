<?php namespace App\Domain\User;

use App\Domain\Role\Role;

class User
{
    private ?int $id;
    private string $name;
    private string $email;
    private string $code;
    private Role $role;
    private string $password;

    public function __construct(
        ?int $id,
        string $name,
        string $email,
        string $password,
        string $code,
        Role $role
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->code = $code;
        $this->role = $role;
    }

    public static function createNew(
        string $name,
        string $email,
        string $plainPassword,
        string $code,
        Role $role
    ): self {
        return new self(
            id: null,
            name: $name,
            email: $email,
            password: password_hash($plainPassword, PASSWORD_BCRYPT),
            code: $code,
            role: $role
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'employee_code' => $this->code,
            'role_name' => $this->role->getName(),
        ];
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function setPlainPassword(string $plainPassword): void
    {
        $this->password = password_hash($plainPassword, PASSWORD_BCRYPT);
    }

    public function isManager(): bool
    {
        return $this->role->isManager();
    }
}