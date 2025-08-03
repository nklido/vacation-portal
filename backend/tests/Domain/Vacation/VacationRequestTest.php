<?php

namespace Tests\Domain\Vacation;

use App\Domain\Role\Role;
use App\Domain\User\User;
use App\Domain\Vacation\VacationRequest;
use App\Domain\Vacation\VacationRequestStatus;
use DateTime;
use PHPUnit\Framework\TestCase;

class VacationRequestTest extends TestCase
{
    private function createUser(): User
    {
        return User::createNew(
            name: 'John Doe',
            email: 'john@example.com',
            plainPassword: 'secret',
            code: 123456,
            role: Role::employee()
        );
    }

    public function testVacationRequestCreation(): void
    {
        $from = new DateTime('2025-08-01');
        $to = new DateTime('2025-08-05');
        $employee = $this->createUser();
        $reason = 'Annual leave';

        $request = new VacationRequest(
            id: 1,
            fromDate: $from,
            toDate: $to,
            employee: $employee,
            reason: $reason,
            status: VacationRequestStatus::Pending
        );

        $this->assertSame(1, $request->getId());
        $this->assertSame($from, $request->getFromDate());
        $this->assertSame($to, $request->getToDate());
        $this->assertSame($employee, $request->getEmployee());
        $this->assertSame($reason, $request->getReason());
        $this->assertEquals(VacationRequestStatus::Pending, $request->getStatus());
    }

    public function testTotalDaysCalculation(): void
    {
        $request = new VacationRequest(
            id: null,
            fromDate: new DateTime('2025-08-01'),
            toDate: new DateTime('2025-08-05'),
            employee: $this->createUser(),
            reason: 'Summer break',
            status: VacationRequestStatus::Approved
        );

        $this->assertEquals(4, $request->getTotalDays());
    }
    
    public function testToArrayStructure(): void
    {
        $from = new DateTime('2025-08-10');
        $to = new DateTime('2025-08-15');
        $employee = $this->createUser();

        $request = new VacationRequest(
            id: 5,
            fromDate: $from,
            toDate: $to,
            employee: $employee,
            reason: 'Vacation',
            status: VacationRequestStatus::Approved
        );

        $data = $request->toArray();

        $this->assertEquals([
            'id' => 5,
            'from_date' => '2025-08-10',
            'to_date' => '2025-08-15',
            'total_days' => 5,
            'reason' => 'Vacation',
            'employee' => $employee->toArray(),
            'status' => 'APPROVED'
        ], $data);
    }
}
