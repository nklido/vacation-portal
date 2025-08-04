<?php

namespace Tests\Application\User\Handler;

use App\Application\User\DTO\CreateUserDTO;
use App\Application\User\Handler\UserStoreHandler;
use App\Domain\User\Exception\DuplicateUserException;
use App\Domain\User\UserRepository;
use App\Shared\Request;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Tests\Support\Factories\UserTestFactory;

class UserStoreHandlerTest extends TestCase
{

    private UserRepository $userRepository;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->userRepository = $this->createMock(UserRepository::class);
    }
    public function testCreateNewUser(): void {

        $john = UserTestFactory::employee(id: 1, name: 'John Doe', email: 'john@example.com', code: '0000001');
        Request::setTestJson([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'securePass1!',
            'code' => '0000001',
        ]);

        $userDto = CreateUserDTO::fromRequest(Request::json());

        $this->userRepository->expects($this->once())
            ->method('findByEmailOrEmployeeCode')
            ->with('john@example.com')
            ->willReturn(null);

        $this->userRepository->expects($this->once())
            ->method('save')
            ->willReturn($john);

        $controller = new UserStoreHandler($this->userRepository);
        $user = $controller->handle($userDto);

        $this->assertEquals('John Doe', $user->getName());
        $this->assertEquals('john@example.com', $user->getEmail());
    }

    public function testCreateFailsIfUserAlreadyExists(): void {

        $this->expectException(DuplicateUserException::class);
        $john = UserTestFactory::employee(id: 1, name: 'John Doe', email: 'john@example.com', code: '0000001');
        Request::setTestJson([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'securePass1!',
            'code' => '0000001',
        ]);
        $dto = CreateUserDTO::fromRequest(Request::json());

        $this->userRepository->expects($this->once())
            ->method('findByEmailOrEmployeeCode')
            ->with('john@example.com', '0000001')
            ->willReturn($john);

        $this->userRepository->expects($this->never())->method('save');

        $handler = new UserStoreHandler($this->userRepository);
        $handler->handle($dto);
    }
}
