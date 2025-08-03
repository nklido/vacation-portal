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

    public function __construct(
        ?int $id,
        DateTime $fromDate,
        DateTime $toDate,
        User $employee,
        string $reason,
        VacationRequestStatus $status
    ) {
        $this->id = $id;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->employee = $employee;
        $this->reason = $reason;
        $this->status = $status;
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

    /** @TODO Count only work days excluding weekends, holidays etc. */
    public function getTotalDays(): int
    {
        $interval = $this->fromDate->diff($this->toDate);
        return $interval->days;
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
            'status' => $this->status->value
        ];
    }
}