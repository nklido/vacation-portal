<?php

namespace Tests\Application\Controllers;

use App\Application\Controllers\VacationRequestController;
use App\Domain\Auth\AuthUser;
use App\Domain\Role\Role;
use App\Domain\User\UserRepository;
use App\Domain\Vacation\VacationRequestRepository;
use App\Domain\Vacation\VacationRequestStatus;
use App\Shared\Request;
use DateTime;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Tests\Domain\Vacation\VacationRequestTest;
use Tests\Support\Factories\UserTestFactory;
use Tests\Support\Factories\VacationRequestTestFactory;

class VacationRequestControllerTest extends TestCase
{
    private UserRepository $userRepository;

    private VacationRequestRepository $vacationRequestRepository;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->vacationRequestRepository = $this->createMock(VacationRequestRepository::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Request::reset();
    }

    public function testIndexReturnsEmployeeOwnRequestsForEmployee(): void {

        $user = UserTestFactory::employee(id: 1, name: 'John Doe', email: 'john@example.com');
        $request = VacationRequestTestFactory::make([
            'employee' => $user
        ]);
        Request::setAuthUser(new AuthUser(1, Role::EMPLOYEE));

        $controller = new VacationRequestController($this->vacationRequestRepository, $this->userRepository);

        $this->vacationRequestRepository->expects($this->once())
            ->method('getByUserId')
            ->with(1)
            ->willReturn([$request]);

        $response = $controller->index();

        $this->assertEquals(200, $response->getStatus());
        $this->assertCount(1, $response->getData());
    }

    public function testIndexReturnsAllEmployeeRequestsForManager(): void {

        $requests = [];
        $user = UserTestFactory::employee(id: 2, name: 'John Doe', email: 'john@example.com');
        $requests[] = VacationRequestTestFactory::make([
            'employee' => $user
        ]);
        $user = UserTestFactory::employee(id: 2, name: 'John Doe', email: 'john@example.com');
        $requests[] = VacationRequestTestFactory::make([
            'employee' => $user
        ]);

        Request::setAuthUser(new AuthUser(1, Role::MANAGER));


        $this->vacationRequestRepository->expects($this->once())
            ->method('getPending')
            ->willReturn($requests);

        $controller = new VacationRequestController($this->vacationRequestRepository, $this->userRepository);
        $response = $controller->index();

        $this->assertEquals(200, $response->getStatus());
        $this->assertCount(2, $response->getData());
    }


    public function testCreatesANewVacationRequest(): void {
        $user = UserTestFactory::employee(
            id: 1,
            name: 'John Doe',
            email: 'john@example.com',
            code: '0000001',
            password: 'pass123'
        );
        Request::setAuthUser(new AuthUser(1, Role::EMPLOYEE));

        $vacationRequest = VacationRequestTestFactory::make([
            'employee' => $user
        ]);

        Request::setTestJson([
            'from_date' => (new DateTime('+5 days'))->format('Y-m-d'),
            'to_date' => (new DateTime('+10 days'))->format('Y-m-d'),
            'reason' => 'Annual leave',
        ]);

        $this->userRepository->expects($this->once())
            ->method('findById')
            ->with($user->getId())
            ->willReturn($user);

        $this->vacationRequestRepository->expects($this->once())
            ->method('save')
            ->willReturn($vacationRequest);

        $controller = new VacationRequestController($this->vacationRequestRepository, $this->userRepository);
        $response = $controller->store();

        $this->assertEquals(200, $response->getStatus());
        $this->assertEquals('Annual leave', $response->getData()['reason']);
    }

    public function testCreateVacationRequestFailsIfInvalidData(): void {
        Request::setTestJson([]);

        $this->userRepository->expects($this->never())->method('findById');
        $this->vacationRequestRepository->expects($this->never())->method('save');

        $controller = new VacationRequestController($this->vacationRequestRepository, $this->userRepository);
        $response = $controller->store();

        $this->assertEquals(422, $response->getStatus());
    }

    public function testCreateVacationRequestFailsIfUserNotFound(): void {
        Request::setAuthUser(new AuthUser(1, Role::EMPLOYEE));
        Request::setTestJson([
            'from_date' => (new DateTime('+5 days'))->format('Y-m-d'),
            'to_date' => (new DateTime('+10 days'))->format('Y-m-d'),
            'reason' => 'Annual leave',
        ]);

        $this->userRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn(null);

        $this->vacationRequestRepository->expects($this->never())->method('save');

        $controller = new VacationRequestController($this->vacationRequestRepository, $this->userRepository);
        $response = $controller->store();

        $this->assertEquals(404, $response->getStatus());
    }

    public function testCreateVacationRequestFailsIfExistingPendingRequestsOverlap(): void {
        $user = UserTestFactory::employee(id: 1);
        Request::setAuthUser(new AuthUser(1, Role::EMPLOYEE));

        Request::setTestJson([
            'from_date' => (new DateTime('+5 days'))->format('Y-m-d'),
            'to_date' => (new DateTime('+10 days'))->format('Y-m-d'),
            'reason' => 'Annual leave',
        ]);

        $this->userRepository->expects($this->once())
            ->method('findById')
            ->with($user->getId())
            ->willReturn($user);

        $this->vacationRequestRepository->expects($this->once())
            ->method('existsOverlappingRequest')
            ->willReturn(true);

        $this->vacationRequestRepository->expects($this->never())->method('save');

        $controller = new VacationRequestController($this->vacationRequestRepository, $this->userRepository);
        $response = $controller->store();

        $this->assertEquals(409, $response->getStatus());
    }

    public function testUpdateStatusOfAVacationRequest(): void {
        $user = UserTestFactory::employee(id: 2);
        $vacationRequest = VacationRequestTestFactory::make([
            'id' => 105,
            'employee' => $user
        ]);

        $updatedVacationRequest = VacationRequestTestFactory::make([
            'id' => 105,
            'employee' => $user,
            'status' => VacationRequestStatus::Approved
        ]);

        Request::setAuthUser(new AuthUser(1, Role::MANAGER));


        Request::setTestJson([
            'status' => VacationRequestStatus::Approved->value,
        ]);

        $this->vacationRequestRepository->expects($this->once())
            ->method('findById')
            ->with(105)
            ->willReturn($vacationRequest);

        $this->vacationRequestRepository->expects($this->once())
            ->method('updateStatus')
            ->with($vacationRequest, VacationRequestStatus::Approved)
            ->willReturn($updatedVacationRequest);

        $controller = new VacationRequestController($this->vacationRequestRepository, $this->userRepository);
        $response = $controller->updateStatus(105);

        $this->assertEquals(200, $response->getStatus());
        $this->assertEquals(VacationRequestStatus::Approved->value, $response->getData()['status']);
    }

    public function testDeleteAVacationRequest(): void {
        $user = UserTestFactory::employee(id: 1);
        Request::setAuthUser(new AuthUser(1, Role::EMPLOYEE));
        $vacationRequest = VacationRequestTestFactory::make([
            'id' => 105,
            'employee' => $user
        ]);

        $this->vacationRequestRepository->expects($this->once())
            ->method('findById')
            ->with(105)
            ->willReturn($vacationRequest);

        $this->vacationRequestRepository->expects($this->once())
            ->method('delete')
            ->with($vacationRequest);

        $controller = new VacationRequestController($this->vacationRequestRepository, $this->userRepository);
        $response = $controller->delete(105);

        $this->assertEquals(204, $response->getStatus());
    }


    public function testDeleteAVacationRequestFailsIfRequestNotFound(): void {
        UserTestFactory::employee(id: 1);
        Request::setAuthUser(new AuthUser(1, Role::EMPLOYEE));

        $this->vacationRequestRepository->expects($this->once())
            ->method('findById')
            ->with(105)
            ->willReturn(null);

        $this->vacationRequestRepository->expects($this->never())->method('delete');

        $controller = new VacationRequestController($this->vacationRequestRepository, $this->userRepository);
        $response = $controller->delete(105);

        $this->assertEquals(404, $response->getStatus());
    }

    public function testDeleteAVacationRequestFailsIfRequestNotOwnedByEmployee(): void {
        $user = UserTestFactory::employee(id: 1);
        $vacationRequest = VacationRequestTestFactory::make([
            'id' => 105,
            'employee' => $user
        ]);
        UserTestFactory::employee(id: 1);
        Request::setAuthUser(new AuthUser(2, Role::EMPLOYEE));

        $this->vacationRequestRepository->expects($this->once())
            ->method('findById')
            ->with(105)
            ->willReturn($vacationRequest);

        $this->vacationRequestRepository->expects($this->never())->method('delete');

        $controller = new VacationRequestController($this->vacationRequestRepository, $this->userRepository);
        $response = $controller->delete(105);
        $this->assertEquals(404, $response->getStatus());
    }


    public function testDeleteAVacationRequestFailsIfRequestIsNotPending(): void {
        $user = UserTestFactory::employee(id: 1);
        $vacationRequest = VacationRequestTestFactory::make([
            'id' => 105,
            'employee' => $user,
            'status' => VacationRequestStatus::Approved
        ]);
        UserTestFactory::employee(id: 1);
        Request::setAuthUser(new AuthUser(1, Role::EMPLOYEE));

        $this->vacationRequestRepository->expects($this->once())
            ->method('findById')
            ->with(105)
            ->willReturn($vacationRequest);

        $this->vacationRequestRepository->expects($this->never())->method('delete');

        $controller = new VacationRequestController($this->vacationRequestRepository, $this->userRepository);
        $response = $controller->delete(105);
        $this->assertEquals(422, $response->getStatus());
    }

}
