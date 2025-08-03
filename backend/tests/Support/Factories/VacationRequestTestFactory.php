<?php namespace Tests\Support\Factories;

use App\Domain\Vacation\VacationRequest;
use App\Domain\Vacation\VacationRequestStatus;
use DateTime;

class VacationRequestTestFactory
{
    public static function make(array $overrides = []): VacationRequest
    {
        $defaults = [
            'id' => null,
            'fromDate' => new DateTime('+5 days'),
            'toDate' => new DateTime('+10 days'),
            'employee' => UserTestFactory::employee(1),
            'reason' => 'Annual leave',
            'status' => VacationRequestStatus::Pending,
        ];

        $data = array_merge($defaults, $overrides);

        return new VacationRequest(
            id: $data['id'],
            fromDate: $data['fromDate'],
            toDate: $data['toDate'],
            employee: $data['employee'],
            reason: $data['reason'],
            status: $data['status'],
        );
    }
}