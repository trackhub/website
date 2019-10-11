<?php

namespace App\Track\SinglePointChecker;

interface PointCheckerInterface
{
    public function isReal(float $distance, float $elevation): bool;
}
