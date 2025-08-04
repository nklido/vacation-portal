<?php

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\Role\Role;
use App\Domain\User\User;
use App\Domain\Vacation\VacationRequest;
use App\Domain\Vacation\VacationRequestStatus;
use DateTime;

class VacationRequestMapper
{
    public static function fromRow(array $row): VacationRequest
    {
        $employeeData = json_decode($row['employee'], true);

        $role = new Role(
            $employeeData['role']['id'],
            $employeeData['role']['name'],
        );

        $employee = new User(
            id: $employeeData['id'],
            name: $employeeData['name'],
            email: $employeeData['email'],
            password: $employeeData['password'],
            code: $employeeData['code'],
            role: $role,
        );

        return new VacationRequest(
            id: $row['id'],
            fromDate: DateTime::createFromFormat('Y-m-d', $row['from_date']),
            toDate: DateTime::createFromFormat('Y-m-d', $row['to_date']),
            employee: $employee,
            reason: $row['reason'],
            status: VacationRequestStatus::from($row['status']),
            createdAt: DateTime::createFromFormat('Y-m-d H:i:s', $row['created_at'])
        );
    }
}
