<?php

namespace Tests\Application\User;

use App\Application\User\DTO\UpdateUserDTO;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class UpdateUserDTOTest extends TestCase
{
    public function testValidInputCreatesDto(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret123'
        ];

        $dto = UpdateUserDTO::fromRequest($data);

        $this->assertEquals('John Doe', $dto->getName());
        $this->assertEquals('john@example.com', $dto->getEmail());
        $this->assertEquals('secret123', $dto->getPassword());
    }


    public function testValidPartialUpdateCreatesDto(): void
    {
        $data = [
            'name' => 'John Doe',
        ];

        $dto = UpdateUserDTO::fromRequest($data);

        $this->assertEquals('John Doe', $dto->getName());
        $this->assertEquals(null, $dto->getEmail());
        $this->assertEquals(null, $dto->getPassword());
    }

    public function testEmptyUpdateIsInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one field must be provided for update.');
        $dto = UpdateUserDTO::fromRequest([]);
    }
}
