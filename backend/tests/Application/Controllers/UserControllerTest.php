<?php

namespace Application\Controllers;

use App\Application\Controllers\UserController;
use App\Application\User\Handler\UserStoreHandler;
use App\Application\User\Handler\UserUpdateHandler;
use App\Domain\User\Exception\DuplicateUserException;
use App\Domain\User\Exception\UserNotFoundException;
use App\Domain\User\User;
use App\Domain\User\UserRepository;
use App\Shared\Request;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Tests\Support\Factories\UserTestFactory;

class UserControllerTest extends TestCase
{
    private UserRepository $userRepository;
    private UserStoreHandler $userStoreHandler;
    private UserUpdateHandler $userUpdateHandler;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->userStoreHandler = $this->createMock(UserStoreHandler::class);
        $this->userUpdateHandler = $this->createMock(UserUpdateHandler::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Request::reset();
    }

    public function testIndexReturnsEmptyList() : void
    {
        $controller = new UserController($this->userRepository, $this->userStoreHandler, $this->userUpdateHandler);
        $response = $controller->index();
        $this->assertEquals(200, $response->getStatus());
        $this->assertEmpty($response->getData());
    }


    public function testIndexReturnsUserList() : void
    {

        $john = UserTestFactory::employee(id: 1, name: 'John Doe', email: 'john@example.com');
        $jane = UserTestFactory::employee(id: 2, name: 'Jane Doe', email: 'jane@example.com');

        $this->userRepository->method('all')->willReturn([$john, $jane]);
        $controller = new UserController($this->userRepository, $this->userStoreHandler, $this->userUpdateHandler);
        $response = $controller->index();

        $this->assertEquals(200, $response->getStatus());
        $this->assertCount(2, $response->getData());

        $this->assertEquals('John Doe', $response->getData()[0]['name']);
        $this->assertEquals('Jane Doe', $response->getData()[1]['name']);
    }

    public function testGetReturnsUserIfFound(): void
    {
        $john = UserTestFactory::employee(id: 1, name: 'John Doe', email: 'john@example.com');
        $this->userRepository->method('findById')->willReturn($john);
        $controller = new UserController($this->userRepository, $this->userStoreHandler, $this->userUpdateHandler);
        $response = $controller->get(1);

        $this->assertEquals(200, $response->getStatus());
        $this->assertEquals('John Doe', $response->getData()['name']);
    }

    public function testGetReturns404IfUserNotFound(): void
    {
        $this->userRepository->method('findById')->willReturn(null);
        $controller = new UserController($this->userRepository, $this->userStoreHandler, $this->userUpdateHandler);
        $response = $controller->get(5);

        $this->assertEquals(404, $response->getStatus());
        $this->assertArrayHasKey('error', $response->getData());
    }

    public function testCreateNewUser(): void {

        $john = UserTestFactory::employee(id: 1, name: 'John Doe', email: 'john@example.com', code: '0000001');
        Request::setTestJson([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'securePass1!',
            'code' => '0000001',
        ]);

        $this->userStoreHandler->expects($this->once())
            ->method('handle')
            ->willReturn($john);

        $controller = new UserController($this->userRepository, $this->userStoreHandler, $this->userUpdateHandler);
        $response = $controller->store();

        $this->assertEquals(201, $response->getStatus());
        $this->assertEquals('John Doe', $response->getData()['name']);
        $this->assertArrayNotHasKey('password', $response->getData());
    }

    public function testCreateFailsIfUserAlreadyExists(): void {
        Request::setTestJson([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'securePass1!',
            'code' => '0000001',
        ]);

        $this->userStoreHandler->expects($this->once())
            ->method('handle')
            ->willThrowException(new DuplicateUserException('User Already Exists'));

        $controller = new UserController($this->userRepository, $this->userStoreHandler, $this->userUpdateHandler);
        $response = $controller->store();

        $this->assertEquals(409, $response->getStatus());
        $this->assertArrayHasKey('error', $response->getData());
    }

    /** @dataProvider invalidUserInputProvider */
    public function testCreateFailsForInvalidInput($invalidData): void {
        Request::setTestJson($invalidData);

        $this->userStoreHandler->expects($this->never())->method('handle');
        $controller = new UserController($this->userRepository, $this->userStoreHandler, $this->userUpdateHandler);
        $response = $controller->store();

        $this->assertEquals(422, $response->getStatus());
        $this->assertArrayHasKey('error', $response->getData());
    }

    public static function invalidUserInputProvider(): array
    {
        return [
            'empty' => [[]],
            'missing name' => [[
                'email' => 'john@example.com',
                'password' => 'securePass1!',
                'employee_code' => 123456,
            ]],
            'invalid email' => [[
                'name' => 'John Doe',
                'email' => 'not-an-email',
                'password' => 'securePass1!',
                'employee_code' => 123456,
            ]],
            'short password' => [[
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => '123',
                'employee_code' => 123456,
            ]],
            'missing employee code' => [[
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'securePass1!',
            ]],
            'non-numeric employee code' => [[
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'securePass1!',
                'employee_code' => 'abc123',
            ]],
        ];
    }

    public function testUpdateUserWithValidData(): void
    {
        $userId = 1;
        $updatedUser = UserTestFactory::employee(
            id: $userId,
            name: 'Jane Doe',
            email: 'john@example.com',
            code: '0000001',
            password: 'pass123'
        );

        Request::setTestJson([
            'name' => 'Jane Doe',
            'email'=> 'jane@example.com',
            'password' => 'securePass1!',
        ]);

        $this->userUpdateHandler->expects($this->once())
            ->method('handle')
            ->willReturn($updatedUser);

        $controller = new UserController($this->userRepository, $this->userStoreHandler, $this->userUpdateHandler);
        $response = $controller->update($userId);

        $this->assertEquals(200, $response->getStatus());
        $this->assertEquals('Jane Doe', $response->getData()['name']);
    }

    public function testUpdateFailsIfUserDoesNotExist(): void
    {
        $userId = 1;
        Request::setTestJson([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'securePass1!',
        ]);

        $this->userUpdateHandler->expects($this->once())
            ->method('handle')
            ->willThrowException(new UserNotFoundException('User not found'));

        $controller = new UserController($this->userRepository, $this->userStoreHandler, $this->userUpdateHandler);
        $response = $controller->update($userId);

        $this->assertEquals(404, $response->getStatus());
        $this->assertArrayHasKey('error', $response->getData());
        $this->assertEquals('User not found', $response->getData()['error']);
    }

    public function testUpdateFailsWithInvalidData(): void
    {
        $userId = 1;
        Request::setTestJson([]);
        $this->userUpdateHandler->expects($this->never())->method('handle');
        $controller = new UserController($this->userRepository, $this->userStoreHandler, $this->userUpdateHandler);
        $response = $controller->update($userId);

        $this->assertEquals(422, $response->getStatus());
        $this->assertArrayHasKey('error', $response->getData());
    }

    public function testDeleteUser(): void
    {
        $user = UserTestFactory::employee(
            id: 1,
            name: 'John Doe',
            email: 'john@example.com',
            code: '0000001',
            password: 'pass123'
        );

        $this->userRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($user);

        $this->userRepository->expects($this->once())
            ->method('delete')
            ->with(1);

        $controller = new UserController($this->userRepository, $this->userStoreHandler, $this->userUpdateHandler);
        $response = $controller->delete(1);

        $this->assertEquals(204, $response->getStatus());
    }


    public function testDeleteFailsIfUserNotFound(): void
    {

        $this->userRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn(null);

        $this->userRepository->expects($this->never())
            ->method('delete');

        $controller = new UserController($this->userRepository, $this->userStoreHandler, $this->userUpdateHandler);
        $response = $controller->delete(1);

        $this->assertEquals(404, $response->getStatus());
        $this->assertArrayHasKey('error', $response->getData());
    }
}
