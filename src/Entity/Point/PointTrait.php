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

    public function getLat(): ?float
    {
        return $this->lat;
    }

    public function getLng(): float
    {
        return $this->lng;
    }
}
