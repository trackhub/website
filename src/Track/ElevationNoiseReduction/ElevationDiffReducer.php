<?php

namespace App\Track\ElevationNoiseReduction;

use App\Entity\Track\Point;

/**
 * See https://www.gpsvisualizer.com/tutorials/elevation_gain.html
 */
class ElevationDiffReducer implements ElevationNoiseReducerInterface
{
    /**
     * Minimum elevation to consider point "real"
     */
    private float $elevationChange;

    public function __construct(float $minimumElevChange)
    {
        $this->elevationChange = $minimumElevChange;
    }

    public function shouldCount(Point $pointA, Point $pointB): bool
    {
        $elevA = $pointA->getElevation();
        $elevB = $pointB->getElevation();
        $diff = $elevA - $elevB;
        if (abs($diff) >= $this->elevationChange) {
            return true;
        }

        return false;
    }
}
