<?php

namespace App\Track\ElevationNoiseReduction;

use App\Entity\Track\Point;

interface ElevationNoiseReducerInterface
{
    public function shouldCount(Point $pointA, Point $pointB): bool;
}
