<?php

namespace App\Track\TwoPointsChecker;

use App\Entity\Track\Point;

class ElevationChecker implements PointCheckerInterface
{
    private $maxAngle;

    /**
     * @param float $maxAngle max angle allowed between the two points
     */
    public function __construct(float $maxAngle)
    {
        $this->maxAngle = $maxAngle;
    }

    public function isReal(Point $pointA, Point $pointB): bool
    {
        $distance = $pointA->distance($pointB);
        $elevation = abs($pointA->getElevation() - $pointB->getElevation());

        $atan = atan2($elevation, $distance);
        $angle = rad2deg($atan);
        if ($angle > $this->maxAngle) {
            return false;
        }

        return true;
    }
}
