<?php

namespace App\Support;

class RequestTiming
{
    private int $queryCount = 0;

    private float $queryTimeMs = 0.0;

    public function reset(): void
    {
        $this->queryCount = 0;
        $this->queryTimeMs = 0.0;
    }

    public function recordQuery(float $timeMs): void
    {
        $this->queryCount++;
        $this->queryTimeMs += $timeMs;
    }

    public function queryCount(): int
    {
        return $this->queryCount;
    }

    public function queryTimeMs(): float
    {
        return $this->queryTimeMs;
    }
}
