<?php namespace App\Infrastructure\Persistence;

use App\Domain\User\User;
use App\Domain\User\UserRepository;
use App\Infrastructure\Persistence\Mappers\UserMapper;
use App\Shared\Database;
use PDO;

class MySQLUserRepository implements UserRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function findById(int $userId): ?User
    {
        $stmt = $this->pdo->prepare('
            SELECT u.*, r.name AS role_name
            FROM users u
            JOIN roles r ON u.role_id = r.id
            where u.id = :id
        ');
        $stmt->execute(['id' => $userId]);
        $data = $stmt->fetch();
        return $data ? UserMapper::fromRow($data) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->pdo->prepare('
            SELECT u.*, r.name AS role_name
            FROM users u
            JOIN roles r ON u.role_id = r.id
            where u.email = :email
        ');
        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch();
        return $data ? UserMapper::fromRow($data) : null;
    }

    public function findByEmailOrEmployeeCode(string $email, string $employeeCode): ?User
    {
        $stmt = $this->pdo->prepare('
            SELECT u.*, r.name AS role_name
            FROM users u
            JOIN roles r ON u.role_id = r.id
            where u.email = :email or u.employee_code = :employee_code
        ');
        $stmt->execute([
            'email' => $email,
            'employee_code' => $employeeCode
        ]);
        $data = $stmt->fetch();
        return $data ? UserMapper::fromRow($data) : null;
    }

    public function save(User $user): User
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO users (name, email, employee_code, password, role_id) 
            VALUES (?, ?, ?, ?, ?)
        ');
        $stmt->execute([
            $user->getName(),
            $user->getEmail(),
            $user->getCode(),
            $user->getPassword(),
            $user->getRole()->getId(),
        ]);
        $user->setId((int) $this->pdo->lastInsertId());
        return $user;
    }

    public function all(): array
    {
        $stmt = $this->pdo->query('
            SELECT u.*, r.name AS role_name
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE r.id = 2
        ');
        $rows = $stmt->fetchAll();
        return array_map([UserMapper::class, 'fromRow'], $rows);
    }

    public function delete(int $userId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);
    }

    public function update(User $user): User
    {
        $stmt = $this->pdo->prepare('
            UPDATE users
            SET name = :name,
                email = :email,
                password = :password
            WHERE id = :id
            ');
        $stmt->execute([
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'id' => $user->getId(),
        ]);
        return $user;
    }
}