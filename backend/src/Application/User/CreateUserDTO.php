<?php

namespace App\Application\User;

use Webmozart\Assert\Assert;

class CreateUserDTO
{

    private string $name;
    private string $email;
    private string $password;
    private string $employeeCode;

    private function __construct()
    {
    }

    public static function fromRequest(array $data): CreateUserDTO
    {
        Assert::keyExists($data, 'name');
        Assert::keyExists($data, 'email');
        Assert::keyExists($data, 'code');
        Assert::keyExists($data, 'password');

        Assert::stringNotEmpty($data['name']);
        Assert::email($data['email']);
        Assert::digits($data['code']);
        Assert::digits($data['code']);
        Assert::length($data['code'], 7);
        Assert::minLength($data['password'], 6);

        $dto = new self();
        $dto->name = $data['name'];
        $dto->email = $data['email'];
        $dto->password = $data['password'];
        $dto->employeeCode = $data['code'];
        return $dto;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getEmployeeCode(): string
    {
        return $this->employeeCode;
    }
}
