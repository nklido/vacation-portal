<?php

namespace Tests\Application\Vacation;

use App\Application\Vacation\CreateVacationRequestDTO;
use App\Domain\Clock;
use App\Shared\FixedClock;
use DateMalformedStringException;
use DateTime;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CreateVacationRequestDTOTest extends TestCase
{
    private $clock;

    protected function setUp(): void
    {
        $this->clock = new FixedClock(strtotime('2025-08-03'));
    }

    /**
     * @dataProvider validInputProvider
     * @throws DateMalformedStringException
     */
    public function testValidInputCreatesDTO(array $data, string $expectedReason, string $expectedFromDate, string $expectedToDate): void
    {
        $dto = CreateVacationRequestDTO::fromRequest($data, $this->clock);
        $this->assertEquals(new DateTime($expectedFromDate), $dto->getFromDate());
        $this->assertEquals(new DateTime($expectedToDate), $dto->getToDate());
        $this->assertEquals($expectedReason, $dto->getReason());
    }

    public static function validInputProvider(): array
    {
        return [
            'multi-day vacation request' => [
                [
                    'from_date' => '2025-08-10',
                    'to_date' => '2025-08-15',
                    'reason' => 'Family vacation',
                ],
                'Family vacation',
                '2025-08-10',
                '2025-08-15',
            ],
            'same-day vacation request' => [
                [
                    'from_date' => '2025-08-10',
                    'to_date' => '2025-08-10',
                    'reason' => 'Medical appointment',
                ],
                'Medical appointment',
                '2025-08-10',
                '2025-08-10',
            ],
            'far future date' => [
                [
                    'from_date' => '2026-01-01',
                    'to_date' => '2026-01-05',
                    'reason' => 'New Year trip',
                ],
                'New Year trip',
                '2026-01-01',
                '2026-01-05',
            ],
        ];
    }

    /**
     * @dataProvider invalidInputProvider
     */
    public function testInvalidInputThrowsException(array $data, string $expectedException, string $expectedMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);
        CreateVacationRequestDTO::fromRequest($data, $this->clock);
    }

    public static function invalidInputProvider(): array
    {
        return [
            'missing from_date' => [
                [
                    'to_date' => '2025-08-15',
                    'reason' => 'Family vacation',
                ],
                InvalidArgumentException::class,
                'Missing from_date',
            ],
            'missing to_date' => [
                [
                    'from_date' => '2025-08-10',
                    'reason' => 'Family vacation',
                ],
                InvalidArgumentException::class,
                'Missing to_date',
            ],
            'missing reason' => [
                [
                    'from_date' => '2025-08-10',
                    'to_date' => '2025-08-15',
                ],
                InvalidArgumentException::class,
                'Missing reason',
            ],
            'empty reason' => [
                [
                    'from_date' => '2025-08-10',
                    'to_date' => '2025-08-15',
                    'reason' => '',
                ],
                InvalidArgumentException::class,
                'Reason must not be empty',
            ],
            'invalid from_date format (slashes)' => [
                [
                    'from_date' => '2025/08/10',
                    'to_date' => '2025-08-15',
                    'reason' => 'Family vacation',
                ],
                InvalidArgumentException::class,
                'Invalid from_date: must be a valid date in YYYY-MM-DD format.',
            ],
            'invalid to_date format (slashes)' => [
                [
                    'from_date' => '2025-08-10',
                    'to_date' => '2025/08/15',
                    'reason' => 'Family vacation',
                ],
                InvalidArgumentException::class,
                'Invalid to_date: must be a valid date in YYYY-MM-DD format.',
            ],
            'invalid from_date (non-existent date)' => [
                [
                    'from_date' => '2025-02-50',
                    'to_date' => '2025-08-15',
                    'reason' => 'Family vacation',
                ],
                InvalidArgumentException::class,
                'Invalid from_date: must be a valid date in YYYY-MM-DD format.',
            ],
            'invalid to_date (non-existent date)' => [
                [
                    'from_date' => '2025-08-10',
                    'to_date' => '2025-04-31',
                    'reason' => 'Family vacation',
                ],
                InvalidArgumentException::class,
                'Invalid to_date: must be a valid date in YYYY-MM-DD format.',
            ],
            'from_date in past' => [
                [
                    'from_date' => '2025-08-01',
                    'to_date' => '2025-08-15',
                    'reason' => 'Family vacation',
                ],
                InvalidArgumentException::class,
                'from_date must be in the future',
            ],
            'to_date before from_date' => [
                [
                    'from_date' => '2025-08-15',
                    'to_date' => '2025-08-10',
                    'reason' => 'Family vacation',
                ],
                InvalidArgumentException::class,
                'to_date must be after or equal to from_date',
            ],
            'malformed from_date' => [
                [
                    'from_date' => 'invalid-date',
                    'to_date' => '2025-08-15',
                    'reason' => 'Family vacation',
                ],
                InvalidArgumentException::class,
                'Invalid from_date: must be a valid date in YYYY-MM-DD format.',
            ],
        ];
    }
}