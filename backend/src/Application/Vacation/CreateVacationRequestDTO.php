<?php

namespace App\Application\Vacation;

use App\Domain\Clock;
use App\Shared\SystemClock;
use DateTime;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

class CreateVacationRequestDTO
{
    private DateTime $fromDate;
    private DateTime $toDate;
    private string $reason;

    private function __construct()
    {
    }

    public static function fromRequest(array $data, Clock $clock = new SystemClock()): CreateVacationRequestDTO
    {
        Assert::keyExists($data, 'fromDate', 'Missing fromDate');
        Assert::keyExists($data, 'toDate', 'Missing toDate');
        Assert::keyExists($data, 'reason', 'Missing reason');
        Assert::stringNotEmpty($data['reason'], 'Reason must not be empty');

        $fromDate = self::parseDate($data['fromDate'], 'fromDate');
        $toDate = self::parseDate($data['toDate'], 'toDate');
        $now = (new DateTime())->setTimestamp($clock->now())->setTime(0, 0);

        Assert::greaterThan($fromDate, $now, 'fromDate must be in the future');
        Assert::greaterThanEq($toDate, $fromDate, 'toDate must be after or equal to fromDate');

        $dto = new self();
        $dto->fromDate = $fromDate;
        $dto->toDate = $toDate;
        $dto->reason = $data['reason'];

        return $dto;
    }

    private static function parseDate(string $dateStr, string $field): DateTime
    {
        $date = DateTime::createFromFormat('Y-m-d', $dateStr);
        $errors = DateTime::getLastErrors();
        if (!$date || ($errors && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            throw new InvalidArgumentException("Invalid $field: must be a valid date in YYYY-MM-DD format.");
        }
        $date->setTime(0, 0);
        return $date;
    }

    public function getFromDate(): DateTime
    {
        return $this->fromDate;
    }

    public function getToDate(): DateTime
    {
        return $this->toDate;
    }

    public function getReason(): string
    {
        return $this->reason;
    }
}