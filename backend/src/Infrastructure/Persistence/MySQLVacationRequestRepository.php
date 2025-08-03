<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Vacation\VacationRequest;
use App\Domain\Vacation\VacationRequestRepository;
use App\Domain\Vacation\VacationRequestStatus;
use App\Infrastructure\Persistence\Mappers\VacationRequestMapper;
use App\Shared\Database;
use PDO;

class MySQLVacationRequestRepository implements VacationRequestRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function findById($id): ?VacationRequest
    {
        $sql = $this->selectVacationRequestSQL();
        $sql .= ' WHERE vr.id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch();
        return $data ? VacationRequestMapper::fromRow($data) : null;
    }
    public function getPending(): array
    {
        $sql = $this->selectVacationRequestSQL();
        $sql .= ' WHERE vr.status = :status';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['status' => VacationRequestStatus::Pending->value]);
        $rows = $stmt->fetchAll();
        return array_map([VacationRequestMapper::class, 'fromRow'], $rows);
    }

    public function getByUserId(int $userId): array
    {
        $sql = $this->selectVacationRequestSQL();
        $sql .= ' WHERE vr.user_id = :user_id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $rows = $stmt->fetchAll();
        return array_map([VacationRequestMapper::class, 'fromRow'], $rows);
    }

    public function save(VacationRequest $request): VacationRequest
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO vacation_requests (from_date, to_date, reason, user_id, status) 
            VALUES (?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $request->getFromDate()->format('Y-m-d'),
            $request->getToDate()->format('Y-m-d'),
            $request->getReason(),
            $request->getEmployee()->getId(),
            $request->getStatus()->value
        ]);

        $request->setId((int) $this->pdo->lastInsertId());
        return $request;
    }

    public function updateStatus(VacationRequest $request, VacationRequestStatus $status): VacationRequest
    {
        $stmt = $this->pdo->prepare('
            UPDATE vacation_requests
            SET status = :status
            WHERE id = :id
            ');
        $stmt->execute([
            'status' => $status->value,
            'id' => $request->getId()
        ]);
        return $request;
    }

    public function delete(VacationRequest $request): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM vacation_requests WHERE id = :id');
        $stmt->execute(['id' => $request->getId()]);
    }

    private function selectVacationRequestSQL(): string
    {
        return '
            SELECT vr.*,
              JSON_OBJECT(
                \'id\', u.id,
                \'name\', u.name,
                \'email\', u.email,
                \'password\', u.password,
                \'role\', JSON_OBJECT(
                          \'id\', r.id,
                          \'name\', r.name
                ),
                \'code\', u.employee_code
              ) AS employee
            FROM vacation_requests vr
            JOIN users u ON u.id = vr.user_id
            JOIN roles r ON r.id = u.role_id
        ';
    }

    public function existsOverlappingRequest(VacationRequest $request): bool
    {
        $stmt = $this->pdo->prepare('
            SELECT COUNT(*) FROM vacation_requests
            WHERE user_id = :user_id
              AND status IN ("PENDING", "APPROVED")
              AND from_date <= :to
              AND to_date >= :from
        ');
        $stmt->execute([
            'user_id' => $request->getEmployee()->getId(),
            'from'    => $request->getFromDate()->format('Y-m-d'),
            'to'      => $request->getToDate()->format('Y-m-d'),
        ]);

        return $stmt->fetchColumn() > 0;
    }
}