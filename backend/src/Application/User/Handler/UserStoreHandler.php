<?php

namespace App\Application\User\Handler;

use App\Application\User\DTO\CreateUserDTO;
use App\Domain\Role\Role;
use App\Domain\User\Exception\DuplicateUserException;
use App\Domain\User\User;
use App\Domain\User\UserRepository;

class UserStoreHandler
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function handle(CreateUserDTO $dto): User
    {
        $existing = $this->userRepository->findByEmailOrEmployeeCode(
            $dto->getEmail(),
            $dto->getEmployeeCode()
        );

        if ($existing) {
            throw new DuplicateUserException('A user with this email or employee code already exists.');
        }

        $user = User::createNew(
            $dto->getName(),
            $dto->getEmail(),
            $dto->getPassword(),
            $dto->getEmployeeCode(),
            Role::employee()
        );

        return $this->userRepository->save($user);
    }
}
