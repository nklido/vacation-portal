<?php namespace App\Application\Controllers;

use App\Application\User\CreateUserDTO;
use App\Application\User\UpdateUserDTO;
use App\Domain\Role\Role;
use App\Domain\User\User;
use App\Domain\User\UserRepository;
use App\Shared\JsonResponse;
use App\Shared\Request;
use App\Shared\Response;
use InvalidArgumentException;

// @TODO Refactor domain logic to a separate UserService|User<Action>Handler class
class UserController
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    public function index(): JsonResponse
    {
        $users = $this->userRepository->all();
        return Response::json(array_map(fn ($user) => $user->toArray(), $users));
    }

    public function get(int $id): JsonResponse
    {
        $user = $this->userRepository->findById($id);
        if (!$user) {
            return Response::error('User not found', 404);
        }
        return Response::json($user->toArray());
    }

    public function store(): JsonResponse
    {
        try {
            $userDto = CreateUserDTO::fromRequest(Request::json());
        } catch (\InvalidArgumentException $exception) {
            return Response::json(['error' => $exception->getMessage()], 422);
        }
        $user = $this->userRepository->findByEmailOrEmployeeCode(
            $userDto->getEmail(),
            $userDto->getEmployeeCode()
        );
        if ($user) {
            return Response::error("A user with this email or employee code already exists.",422);
        }
        $user = User::createNew(
            $userDto->getName(),
            $userDto->getEmail(),
            $userDto->getPassword(),
            $userDto->getEmployeeCode(),
            Role::employee()
        );
        $user = $this->userRepository->save($user);
        return Response::json($user->toArray(), 200);
    }

    public function update(int $id): JsonResponse
    {
        try {
            $userDto = UpdateUserDTO::fromRequest(Request::json());
        } catch (InvalidArgumentException $exception) {
            return Response::json(['error' => $exception->getMessage()], 422);
        }

        $user = $this->userRepository->findById($id);
        if (!$user) {
            return Response::error('User not found', 404);
        }

        if ($user->isManager()) {
            return Response::error('You cannot update a manager user.', 403);
        }

        if ($userDto->getName()) $user->setName($userDto->getName());
        if ($userDto->getEmail()) $user->setEmail($userDto->getEmail());
        if ($userDto->getPassword()) $user->setPlainPassword($userDto->getPassword());
        $user = $this->userRepository->update($user);
        return Response::json($user->toArray());
    }

    public function delete(int $id): JsonResponse
    {
        $user = $this->userRepository->findById($id);
        if (!$user) {
            return Response::error('User not found', 404);
        }

        if (Request::getAuthUser() && Request::getAuthUser()->id === $id) {
            return Response::error('You cannot delete your own user.', 400);
        }

        $this->userRepository->delete($id);
        return Response::json(null, 204);
    }
}