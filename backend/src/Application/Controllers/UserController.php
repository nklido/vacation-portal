<?php namespace App\Application\Controllers;

use App\Application\User\DTO\CreateUserDTO;
use App\Application\User\DTO\UpdateUserDTO;
use App\Application\User\Handler\UserStoreHandler;
use App\Application\User\Handler\UserUpdateHandler;
use App\Domain\Exception\UnauthorizedException;
use App\Domain\User\Exception\DuplicateUserException;
use App\Domain\User\Exception\UserNotFoundException;
use App\Domain\User\UserRepository;
use App\Shared\JsonResponse;
use App\Shared\Request;
use App\Shared\Response;
use InvalidArgumentException;

class UserController
{
    private UserRepository $userRepository;
    private UserStoreHandler $userStoreHandler;
    private UserUpdateHandler $userUpdateHandler;

    public function __construct(
        UserRepository $userRepository,
        UserStoreHandler $userStoreHandler,
        UserUpdateHandler $userUpdateHandler
    ) {
        $this->userRepository = $userRepository;
        $this->userStoreHandler = $userStoreHandler;
        $this->userUpdateHandler = $userUpdateHandler;
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
            $user = $this->userStoreHandler->handle($userDto);
            return Response::json($user->toArray(), 201);
        } catch (InvalidArgumentException $exception) {
            return Response::error($exception->getMessage(), 422);
        } catch (DuplicateUserException $exception) {
            return Response::error($exception->getMessage(), 409);
        }
    }

    public function update(int $id): JsonResponse
    {
        try {
            $userDto = UpdateUserDTO::fromRequest(Request::json());
            $user = $this->userUpdateHandler->handle($id, $userDto);
            return Response::json($user->toArray());
        } catch (InvalidArgumentException $exception) {
            return Response::json(['error' => $exception->getMessage()], 422);
        } catch (DuplicateUserException $exception) {
            return Response::error($exception->getMessage(), 409);
        } catch (UserNotFoundException $exception) {
            return Response::error($exception->getMessage(), 404);
        } catch (UnauthorizedException $e) {
            return Response::error($e->getMessage(), 403);
        }
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
