<?php namespace App\Domain\Vacation;

use App\Domain\Vacation\VacationRequest;

interface VacationRequestRepository
{

    public function findById(int $id): ?VacationRequest;
    public function getPending(): array;

    public function getByUserId(int $userId): array;

    public function save(VacationRequest $request): void;

    public function updateStatus(VacationRequest $request, VacationRequestStatus $status): void;

    public function delete(VacationRequest $request): void;
}