<?php

namespace App\Domain\Auth;

readonly class AuthUser
{
    public function __construct(public int $id, public int $roleId) {

    }
}