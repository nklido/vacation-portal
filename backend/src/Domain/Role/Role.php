<?php

namespace App\Domain\Role;

class Role
{
    public final const int MANAGER = 1;
    public final const int EMPLOYEE = 2;

    private string $name;
    private int $id;

    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isManager(): bool
    {
        return $this->id === self::MANAGER;
    }

    public function isEmployee(): bool
    {
        return $this->id === self::EMPLOYEE;
    }

    public static function employee(): self
    {
        return new self(self::EMPLOYEE, 'employee');
    }

    public static function manager(): self
    {
        return new self(self::MANAGER, 'manager');
    }
}
