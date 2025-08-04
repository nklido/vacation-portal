<?php

namespace App\Domain;

interface Clock
{
    public function now(): int;
}
