<?php

namespace App\Entity\Point;

trait PointTrait
{
    /**
     * @ORM\Column(type="float")
     */
    private $lat;

    /**
     * @ORM\Column(type="float")
     */
    private $lng;

    public function getLat(): float
    {
        return $this->lat;
    }

    public function getLng(): float
    {
        return $this->lng;
    }

    public function distance(PointInterface $point): float
    {
        $r = 6371000;

        $dLan = deg2rad($point->getLat() - $this->getLat());
        $dLng = deg2rad($point->getLng() - $this->getLng());

        $lat1 = deg2rad($this->getLat());
        $lat2 = deg2rad($point->getLat());

        $a = (sin($dLan / 2) ** 2) + (sin($dLng / 2) ** 2) * cos($lat1) * cos($lat2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $c * $r;
    }
}
