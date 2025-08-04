<?php namespace App\Domain\Vacation;


use App\Domain\User\User;
use DateTime;

class VacationRequest
{
    private ?int $id;

    private DateTime $fromDate;

    private DateTime $toDate;
    private User $employee;
    private string $reason;
    private VacationRequestStatus $status;
    private ?DateTime $createdAt;

    public function __construct(
        ?int $id,
        DateTime $fromDate,
        DateTime $toDate,
        User $employee,
        string $reason,
        VacationRequestStatus $status,
        ?DateTime $createdAt = null
    ) {
        $this->id = $id;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->employee = $employee;
        $this->reason = $reason;
        $this->status = $status;
        $this->createdAt = $createdAt;
    }

    public static function createNew(
        DateTime $fromDate,
        DateTime $toDate,
        string $reason,
        User $employee,
    ): self {
        return new self(
            id: null,
            fromDate: $fromDate,
            toDate: $toDate,
            employee: $employee,
            reason: $reason,
            status: VacationRequestStatus::Pending,
            createdAt: null
        );
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFromDate(): DateTime
    {
        return $this->fromDate;
    }

    public function getToDate(): DateTime
    {
        return $this->toDate;
    }

    public function getEmployee(): User
    {
        return $this->employee;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getStatus(): VacationRequestStatus
    {
        return $this->status;
    }

    public function getCreatedAt(): DateTime{
        return $this->createdAt;
    }

    /** @TODO Count only work days excluding weekends, holidays etc. */
    public function getTotalDays(): int
    {
        $interval = $this->fromDate->diff($this->toDate);
        return $interval->days + 1;
    }

    public function isPending(): bool
    {
        return $this->getStatus() === VacationRequestStatus::Pending;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'from_date' => $this->fromDate->format('Y-m-d'),
            'to_date' => $this->toDate->format('Y-m-d'),
            'total_days' => $this->getTotalDays(),
            'reason' => $this->reason,
            'employee' => $this->employee->toArray(),
            'status' => $this->status->value,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
        ];
    }
}