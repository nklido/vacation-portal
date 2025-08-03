<?php

namespace Domain\Role;

use App\Domain\Role\Role;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    public function testConstructorAndGetters()
    {
        $role = new Role(1, 'Manager');
        $this->assertEquals(1, $role->getId());
        $this->assertEquals('Manager', $role->getName());
    }

    public function testIsManager()
    {
        $managerRole = new Role(Role::MANAGER, 'Manager');
        $employeeRole = new Role(Role::EMPLOYEE, 'Employee');
        $this->assertTrue($managerRole->isManager());
        $this->assertFalse($employeeRole->isManager());
    }

    public function testIsEmployee()
    {
        $managerRole = new Role(Role::MANAGER, 'Manager');
        $employeeRole = new Role(Role::EMPLOYEE, 'Employee');
        $this->assertTrue($employeeRole->isEmployee());
        $this->assertFalse($managerRole->isEmployee());
    }

    public function testEmployeeFactoryMethod()
    {
        $employeeRole = Role::employee();
        $this->assertEquals(Role::EMPLOYEE, $employeeRole->getId());
        $this->assertEquals('employee', $employeeRole->getName());
    }
}
