<?php

namespace App\Application\User;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

class UpdateUserDTO
{
    private ?string $name;
    private ?string $email;
    private ?string $password;

    private function __construct()
    {
    }

    public static function fromRequest(array $data): UpdateUserDTO
    {
        $name = $data['name'] ?? null;
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if ($name === null && $email === null && $password === null) {
            throw new InvalidArgumentException('At least one field must be provided for update.');
        }

        if ($name !== null) Assert::stringNotEmpty($name);
        if ($email !== null) Assert::email($email);
        if ($password !== null) Assert::minLength($password, 6);

        $dto = new self();
        $dto->name = $name;
        $dto->email = $email;
        $dto->password = $password;
        return $dto;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
}