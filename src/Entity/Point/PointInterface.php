<?php

namespace App\Entity\Point;

interface PointInterface
{
    public function getLat(): float;
    public function getLng(): float;
}
