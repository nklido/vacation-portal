<?php namespace App\Domain\User;

interface UserRepository
{

    public function findById(int $userId): ?User;

    public function findByEmail(string $email): ?User;

    public function findByEmailOrEmployeeCode(string $email, string $employeeCode): ?User;

    public function save(User $user): User;

    public function all(): array;

    public function delete(int $userId): void;

    public function update(User $user): User;
}
