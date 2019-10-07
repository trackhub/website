<?php

namespace App\Entity\Point;

trait ElevationPointTrait
{
    use PointTrait;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $elevation;

    public function setElevation(float $elev = null): self
    {
        $this->elevation = $elev;

        return $this;
    }

    public function getElevation(): ?float
    {
        return $this->elevation;
    }
}
