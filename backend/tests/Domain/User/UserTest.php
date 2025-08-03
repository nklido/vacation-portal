<?php

namespace Tests\Domain\User;

use App\Domain\Role\Role;
use App\Domain\User\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{


    public function testCreateNewHashesPassword(): void
    {
        $user = User::createNew(
            name: 'John Doe',
            email: 'john@example.com',
            plainPassword: 'secret',
            code: 123456,
            role: Role::employee()
        );

        $this->assertNotEmpty($user->getPassword());
        $this->assertNotEquals('secret', $user->getPassword());
        $this->assertTrue(password_verify('secret', $user->getPassword()));
    }

    public function testConstructorAndGetters()
    {
        $role = new Role(Role::EMPLOYEE, 'Employee');
        $user = new User(1, 'John Doe', 'john@example.com', 'password', 123456, $role);

        $this->assertEquals(1, $user->getId());
        $this->assertEquals('John Doe', $user->getName());
        $this->assertEquals('john@example.com', $user->getEmail());
        $this->assertEquals('password', $user->getPassword());
        $this->assertEquals(123456, $user->getCode());
        $this->assertEquals($role, $user->getRole());
    }

    public function testSetters()
    {
        $role = new Role(Role::EMPLOYEE, 'Employee');
        $user = new User(1, 'John Doe', 'john@example.com', 'secret', 123456, $role);

        $user->setName('Jane Doe');
        $user->setEmail('jane@example.com');
        $user->setPassword('secret');

        $this->assertEquals('Jane Doe', $user->getName());
        $this->assertEquals('jane@example.com', $user->getEmail());
        $this->assertEquals('secret', $user->getPassword());
    }

    
    public function testToArray()
    {
        $role = new Role(Role::EMPLOYEE, 'Employee');
        $user = new User(1, 'John Doe', 'john@example.com', 'password', 123456, $role);

        $expectedArray = [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'employee_code' => 123456,
            'role_name' => 'Employee',
        ];

        $this->assertEquals($expectedArray, $user->toArray());
    }
}
