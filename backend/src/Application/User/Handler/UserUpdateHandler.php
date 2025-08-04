<?php

namespace App\Application\User\Handler;

use App\Application\User\DTO\UpdateUserDTO;
use App\Domain\Exception\UnauthorizedException;
use App\Domain\User\Exception\DuplicateUserException;
use App\Domain\User\Exception\UserNotFoundException;
use App\Domain\User\User;
use App\Domain\User\UserRepository;

class UserUpdateHandler
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws UnauthorizedException
     */
    public function handle(int $id, UpdateUserDTO $dto): User
    {
        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw new UserNotFoundException('User not found');
        }

        if ($dto->getEmail()) {
            $existingUser = $this->userRepository->findByEmail($dto->getEmail());
            if ($existingUser && $existingUser->getId() !== $id) {
                throw new DuplicateUserException('A user with this email already exists.');
            }
        }

        if ($user->isManager()) {
            throw new UnauthorizedException('You cannot update a manager user.');
        }

        if ($dto->getName()) {
            $user->setName($dto->getName());
        }
        if ($dto->getEmail()) {
            $user->setEmail($dto->getEmail());
        }
        if ($dto->getPassword()) {
            $user->setPlainPassword($dto->getPassword());
        }
        return $this->userRepository->update($user);
    }
}
