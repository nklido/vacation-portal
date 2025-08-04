<?php

namespace App\Domain\Auth;

use App\Domain\Role\Role;

readonly class AuthUser
{
    public function __construct(public int $id, public int $roleId)
    {

    }

    public function isManager(): bool
    {
        return $this->roleId === Role::MANAGER;
    }

    public function isEmployee(): bool
    {
        return $this->roleId === Role::EMPLOYEE;
    }
}
