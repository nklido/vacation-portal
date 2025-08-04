<?php

namespace App\Shared;

use App\Domain\Clock;

class SystemClock implements Clock
{
    public function now(): int
    {
        return time();
    }
}
