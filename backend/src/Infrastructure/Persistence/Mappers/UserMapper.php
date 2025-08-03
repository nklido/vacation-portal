<?php namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\Role\Role;
use App\Domain\User\User;

class UserMapper
{
    public static function fromRow($row): User
    {
        $role = new Role((int)$row['role_id'], $row['role_name']);
        return new User(
            (int)$row['id'],
            $row['name'],
            $row['email'],
            $row['password'],
            $row['employee_code'],
            $role
        );
    }
}