<?php

namespace App\Track\SinglePointChecker;

class PointChecker implements PointCheckerInterface
{
    private $maxAngle;

    public function __construct(float $maxAngle)
    {
        $this->maxAngle = $maxAngle;
    }

    public function isReal(float $distance, float $elevation): bool
    {
        $atan = atan2(abs($elevation), $distance);
        $angle = rad2deg($atan);
        if ($angle > $this->maxAngle) {
            return false;
        }

        return true;
    }
}
