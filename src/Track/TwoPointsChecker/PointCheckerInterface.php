<?php

namespace App\Track\TwoPointsChecker;

use App\Entity\Track\Point;

interface PointCheckerInterface
{
    public function isReal(Point $pointA, Point $pointB): bool;
}
