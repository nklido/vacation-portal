<?php

namespace App\Shared;

use App\Domain\Clock;

class FixedClock implements Clock
{
    private int $fixed;

    public function __construct(int $fixed)
    {
        $this->fixed = $fixed;
    }

    public function now(): int
    {
        return $this->fixed;
    }
}