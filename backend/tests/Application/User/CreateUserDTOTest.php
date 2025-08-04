<?php

namespace Tests\Application\User;

use App\Application\User\DTO\CreateUserDTO;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CreateUserDTOTest extends TestCase
{
    public function testValidInputCreatesDto(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'code' => '0034567',
            'password' => 'secret123'
        ];

        $dto = CreateUserDTO::fromRequest($data);

        $this->assertEquals('John Doe', $dto->getName());
        $this->assertEquals('john@example.com', $dto->getEmail());
        $this->assertEquals('secret123', $dto->getPassword());
        $this->assertEquals('0034567', $dto->getEmployeeCode());
    }

    public function testMissingFieldThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected the key "name" to exist.');

        CreateUserDTO::fromRequest([
            'email' => 'john@example.com',
            'code' => '1234567',
            'password' => 'secret123'
        ]);
    }


    /**
     * @dataProvider invalidCodeProvider
     */
    public function testInvalidCodeThrows(string $invalidCode): void
    {
        $this->expectException(InvalidArgumentException::class);
        CreateUserDTO::fromRequest([
            'name' => 'John',
            'email' => 'john@example.com',
            'code' => $invalidCode,
            'password' => 'secret123'
        ]);
    }

    public static function invalidCodeProvider(): array
    {
        return [
            'too short' => ['12345'],
            'too long' => ['12345678'],
            'not digits' => ['12a4567'],
            'empty string' => [''],
            'spaces' => ['123 567'],
            'special characters' => ['123-567'],
            'letters only' => ['abcdefg'],
        ];
    }
}
