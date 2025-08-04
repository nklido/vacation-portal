<?php

namespace Tests\Application\User\Handler;

use App\Application\User\DTO\UpdateUserDTO;
use App\Application\User\Handler\UserUpdateHandler;
use App\Domain\Exception\UnauthorizedException;
use App\Domain\User\Exception\DuplicateUserException;
use App\Domain\User\Exception\UserNotFoundException;
use App\Domain\User\User;
use App\Domain\User\UserRepository;
use App\Shared\Request;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Tests\Support\Factories\UserTestFactory;

class UserUpdateHandlerTest extends TestCase
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

    /**
     * @throws UnauthorizedException
     */
    public function testHandleWithValidData(): void
    {
        $user = UserTestFactory::employee(
            id: 1,
            name: 'John Doe',
            email: 'john@example.com',
            code: '0000001',
            password: 'pass123'
        );

        $updatedUser = UserTestFactory::employee(
            id: 1,
            name: 'Jane Doe',
            email: 'john@example.com',
            code: '0000001',
            password: 'pass123'
        );

        $userId = 1;
        Request::setTestJson([
            'name' => 'Jane Doe',
            'email'=> 'jane@example.com',
            'password' => 'securePass1!',
        ]);
        $dto = UpdateUserDTO::fromRequest(Request::json());

        $this->userRepository->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($user);

        $this->userRepository->expects($this->once())
            ->method('update')
            ->with($this->callback(function (User $updatedUser) {
                return $updatedUser->getName() === 'Jane Doe' &&
                       $updatedUser->getEmail() === 'jane@example.com' &&
                       password_verify('securePass1!', $updatedUser->getPassword());
            }))
            ->willReturn($updatedUser);

        $handler = new UserUpdateHandler($this->userRepository);
        $user = $handler->handle($userId, $dto);

        $this->assertEquals('Jane Doe', $user->getName());
    }

    /**
     * @throws UnauthorizedException
     */
    public function testFailsIfUserNotFound(): void
    {
        $this->expectException(UserNotFoundException::class);
        $userId = 1;
        Request::setTestJson([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'securePass1!',
        ]);

        $dto = UpdateUserDTO::fromRequest(Request::json());

        $this->userRepository->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn(null);

        $this->userRepository->expects($this->never())
            ->method('update');

        $handler = new UserUpdateHandler($this->userRepository);
        $handler->handle(1, $dto);
    }


    public function testCannotUpdateManager(): void
    {
        $manager = UserTestFactory::manager(1);
        $this->expectException(UnauthorizedException::class);
        $userId = 1;
        Request::setTestJson([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'securePass1!',
        ]);

        $dto = UpdateUserDTO::fromRequest(Request::json());

        $this->userRepository->expects($this->once())
            ->method('findById')
            ->with($userId)
            ->willReturn($manager);

        $this->userRepository->expects($this->never())
            ->method('update');

        $handler = new UserUpdateHandler($this->userRepository);
        $handler->handle(1, $dto);
    }

    public function testCannotUpdateWhenEmailExistsOnDifferentUser(): void
    {
        $this->expectException(DuplicateUserException::class);

        $editingUser = UserTestFactory::employee(1, 'Jannet', 'jannet@example.com');
        $existingUser = UserTestFactory::employee(2, 'Jane', 'jane@example.com');

        Request::setTestJson([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'securePass1!',
        ]);
        $dto = UpdateUserDTO::fromRequest(Request::json());

        $this->userRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($editingUser);

        $this->userRepository->expects($this->once())
            ->method('findByEmail')
            ->with('jane@example.com')
            ->willReturn($existingUser);

        $this->userRepository->expects($this->never())
            ->method('update');

        $handler = new UserUpdateHandler($this->userRepository);
        $handler->handle(1, $dto);
    }

    /**
     * @throws UnauthorizedException
     */
    public function testCanUpdateWithSameEmailOnSameUser(): void
    {
        $editingUser = UserTestFactory::employee(1, 'Jannet', 'jane@example.com', '1234567', 'pass');
        $updatedUser = UserTestFactory::employee(1, 'Jane Doe', 'jane@example.com', '1234567', 'securePass1!');

        Request::setTestJson([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'securePass1!',
        ]);
        $dto = UpdateUserDTO::fromRequest(Request::json());

        $this->userRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($editingUser);

        $this->userRepository->expects($this->once())
            ->method('findByEmail')
            ->with('jane@example.com')
            ->willReturn($editingUser);

        $this->userRepository->expects($this->once())
            ->method('update')
            ->with($this->callback(function (User $updatedUser) {
                return $updatedUser->getName() === 'Jane Doe' &&
                       $updatedUser->getEmail() === 'jane@example.com' &&
                       password_verify('securePass1!', $updatedUser->getPassword());
            }))
            ->willReturn($updatedUser);

        $handler = new UserUpdateHandler($this->userRepository);
        $user = $handler->handle(1, $dto);
        $this->assertEquals('Jane Doe', $user->getName());
        $this->assertTrue(password_verify('securePass1!', $user->getPassword()));
    }
}
