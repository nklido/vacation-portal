<?php namespace App\Domain\Vacation;

interface VacationRequestRepository
{

    public function findById(int $id): ?VacationRequest;
    public function getPending(): array;

    public function getByUserId(int $userId): array;

    public function save(VacationRequest $request): VacationRequest;

    public function updateStatus(VacationRequest $request, VacationRequestStatus $status): VacationRequest;

    public function delete(VacationRequest $request): void;

    public function existsOverlappingRequest(VacationRequest $request): bool;
}
