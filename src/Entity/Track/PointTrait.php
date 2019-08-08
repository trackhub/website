<?php

namespace App\Entity\Track;

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

    /**
     * @ORM\Column(type="float")
     */
    private $elevation = 0;

    public function getLat(): float
    {
        return $this->lat;
    }

    public function getLng(): float
    {
        return $this->lng;
    }

    public function setElevation(float $elev)
    {
        $this->elevation = $elev;
    }

    public function getElevation(): ?float
    {
        return $this->elevation;
    }
}
