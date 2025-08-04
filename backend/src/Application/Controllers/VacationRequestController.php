<?php

namespace App\Application\Controllers;

use App\Application\Vacation\CreateVacationRequestDTO;
use App\Domain\Role\Role;
use App\Domain\User\UserRepository;
use App\Domain\Vacation\VacationRequest;
use App\Domain\Vacation\VacationRequestRepository;
use App\Domain\Vacation\VacationRequestStatus;
use App\Infrastructure\Auth\AuthContext;
use App\Shared\JsonResponse;
use App\Shared\Request;
use App\Shared\Response;
use InvalidArgumentException;

// @TODO Refactor domain logic to a separate VacationRequestService|VacationRequest<Action>Handler class
class VacationRequestController
{
    private VacationRequestRepository $vacationRequestRepository;

    private UserRepository $userRepository;

    public function __construct(
        VacationRequestRepository $vacationRequestRepository,
        UserRepository            $userRepository
    ) {
        $this->vacationRequestRepository = $vacationRequestRepository;
        $this->userRepository = $userRepository;
    }

    public function index(): JsonResponse
    {
        $vacationRequests = match (AuthContext::roleId()) {
            Role::MANAGER => $this->vacationRequestRepository->getPending(),
            Role::EMPLOYEE => $this->vacationRequestRepository->getByUserId(AuthContext::id()),
        };
        return Response::json(array_map(fn ($vacationRequest) => $vacationRequest->toArray(), $vacationRequests));
    }

    public function store(): JsonResponse
    {

        if (AuthContext::roleId() !== Role::EMPLOYEE) {
            return Response::error('Only employees can create vacation requests.', 403);
        }
        try {
            $vacationRequestDto = CreateVacationRequestDTO::fromRequest(Request::json());
        } catch (InvalidArgumentException $exception) {
            return Response::error($exception->getMessage(), 422);
        }

        $user = $this->userRepository->findById(AuthContext::id());
        if (!$user) {
            return Response::error('Invalid user', 404);
        }

        $vacationRequest = VacationRequest::createNew(
            $vacationRequestDto->getFromDate(),
            $vacationRequestDto->getToDate(),
            $vacationRequestDto->getReason(),
            $user,
        );

        if ($this->vacationRequestRepository->existsOverlappingRequest($vacationRequest)) {
            return Response::error('Vacation request overlaps with an existing one', 409);
        }

        $vacationRequest = $this->vacationRequestRepository->save($vacationRequest);
        return Response::json($vacationRequest->toArray(), 200);
    }

    public function updateStatus(int $vacationRequestId): JsonResponse
    {
        $data = Request::json();
        $status = VacationRequestStatus::tryFrom($data['status'] ?? null);
        if (!in_array($status, [VacationRequestStatus::Approved, VacationRequestStatus::Rejected])) {
            return Response::json(['error' => 'Invalid status'], 400);
        }

        $vacationRequest = $this->vacationRequestRepository->findById($vacationRequestId);
        if (!$vacationRequest) {
            return Response::json(['error' => 'Vacation Request not found'], 404);
        }

        if (!$vacationRequest->isPending()) {
            return Response::json(['error' => 'Vacation Request is not in a pending state'], 400);
        }

        $vacationRequest = $this->vacationRequestRepository->updateStatus($vacationRequest, $status);

        return Response::json($vacationRequest->toArray(), 200);
    }

    public function delete(int $id): JsonResponse
    {
        $vacationRequest = $this->vacationRequestRepository->findById($id);
        if (!$vacationRequest || $vacationRequest->getEmployee()->getId() !== AuthContext::id()) {
            return Response::error('VacationRequest not found', 404);
        }

        if (!$vacationRequest->isPending()) {
            return Response::error('Only pending vacation requests can be deleted', 422);
        }

        $this->vacationRequestRepository->delete($vacationRequest);
        return Response::json(null, 204);
    }
}
