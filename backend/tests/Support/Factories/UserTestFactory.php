<?php namespace Tests\Support\Factories;

use App\Domain\User\User;
use App\Domain\Role\Role;

class UserTestFactory
{
    public static function employee(
        int    $id,
        string $name = 'John Doe',
        string $email = 'john.doe@example.com',
        int    $code = 123456,
        string $password = 'test1234'
    ): User
    {
        $role = new Role(2, 'employee');

        $user = User::createNew(
            name: $name,
            email: $email,
            plainPassword: $password,
            code: $code,
            role: $role
        );

        $user->setId($id);

        return $user;
    }

    public static function manager(
        int    $id,
        string $name = 'Jane Manager',
        string $email = 'manager@example.com',
        int    $code = 999999,
        string $password = 'admin1234'
    ): User
    {
        $role = new Role(1, 'manager');

        $user = User::createNew(
            name: $name,
            email: $email,
            plainPassword: $password,
            code: $code,
            role: $role
        );

        $user->setId($id);

        return $user;
    }
}
